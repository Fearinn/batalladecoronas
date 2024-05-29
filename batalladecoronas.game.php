<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * BatallaDeCoronas implementation : © Matheus Gomes matheusgomesforwork@gmail.com
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * batalladecoronas.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */

use function PHPSTORM_META\type;

require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');


class BatallaDeCoronas extends Table
{
    function __construct()
    {
        parent::__construct();

        $this->initGameStateLabels(array(
            "die_1" => 10,
            "die_2" => 11,
            "active_chair" => 12,
            "active_counselor" => 13,
            "token_state" => 14,
            "active_equipment" => 15,
            "attacker" => 16,
            "initial_loser" => 17,
            "damaged_shields" => 18,

            "highest_gems" => 80,
            "first_turn" => 81
        ));

        $this->tokens = $this->getNew("module.common.deck");
        $this->tokens->init("token");

        $this->council = $this->getNew("module.common.deck");
        $this->council->init("counselor");
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "batalladecoronas";
    }

    protected function setupNewGame($players, $options = array())
    {
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
        }
        $sql .= implode(',', $values);
        $this->DbQuery($sql);
        $this->reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        $this->reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        $this->setGameStateInitialValue('die_1', 0);
        $this->setGameStateInitialValue('die_2', 0);
        $this->setGameStateInitialValue("active_chair", 0);
        $this->setGameStateInitialValue("active_counselor", 0);
        $this->setGameStateInitialValue("token_state", 0);
        $this->setGameStateInitialValue("active_equipment", 0);
        $this->setGameStateInitialValue("attacker", 0);
        $this->setGameStateInitialValue("initial_loser", 0);
        $this->setGameStateInitialValue("damaged_shields", 0);

        $this->setGameStateInitialValue("highest_gems", 0);
        $this->setGameStateInitialValue("first_turn", 1);

        foreach ($players as $player_id => $player) {
            $counselors = array();
            foreach ($this->counselors_info as $counselor_id => $counselor) {
                $card = array(
                    "type" => "counselor",
                    "type_arg" => $counselor_id,
                    "nbr" => 1
                );

                $counselors[] = $card;
            }
            $this->council->createCards($counselors, "box");
            $this->council->moveAllCardsInLocation("box", "inactive", null, $player_id);
        }

        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = $this->getCurrentPlayerId();

        $sql = "SELECT player_id id, player_score score FROM player ";

        $result["counselorsInfo"] = $this->counselors_info;
        $result["churchSquares"] = $this->church_squares;

        $result["players"] = $this->getCollectionFromDb($sql);
        $result["dice"] = $this->getDice();
        $result["supply"] = $this->getSupply();
        $result["claimedSupply"] = $this->getClaimedSupply();
        $result["inactiveCouncil"] = $this->getInactiveCouncil();
        $result["council"] = $this->getCouncil();
        $result["gems"] = $this->getGemsByLocation();
        $result["attack"] = $this->getAttack();
        $result["defense"] = $this->getDefense();
        $result["church"] = $this->getChurch();
        $result["treasure"] = $this->getTreasure();
        $result["dragon"] = $this->getDragon();

        return $result;
    }

    function getGameProgression()
    {
        $progression = (100 / 3) * $this->getGameStateValue("highest_gems");
        return round($progression);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    //////////// 

    function getStateName(): string
    {
        return $this->gamestate->state()["name"];
    }

    function dragonRage($player_id): int
    {
        $target_id = $this->getPlayerAfter($player_id);

        if ($this->getPlayerDefense($target_id) > 0) {
            $this->decreaseDefense(4, $target_id, true);

            $this->notifyAllPlayers(
                "dragonRage",
                clienttranslate('${target_name} is attacked by the dragon of ${player_name}'),
                array(
                    "player_id" => $player_id,
                    "target_id" => $target_id,
                    "player_name" => $this->getPlayerNameById($player_id),
                    "target_name" => $this->getPlayerNameById($target_id),
                )
            );

            $this->setPlayerDragon(0, $player_id);
        } else {
            $this->levelDownDragon(5, $player_id);
        }

        return $this->getPlayerDefense($target_id);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// DB manipulation
    ////////////

    function getPlayerCrown($player_id): int
    {
        return !!$this->getUniqueValueFromDB("SELECT crown from player WHERE player_id='$player_id'");
    }

    function setPlayerCrown(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET crown=$value WHERE player_id='$player_id'");
    }

    function getPlayerCross($player_id): int
    {
        return !!$this->getUniqueValueFromDB("SELECT sacredcross from player WHERE player_id='$player_id'");
    }

    function setPlayerCross(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET sacredcross=$value WHERE player_id='$player_id'");
    }

    function getPlayerSmith($player_id): int
    {
        return !!$this->getUniqueValueFromDB("SELECT smith from player WHERE player_id='$player_id'");
    }

    function setPlayerSmith(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET smith=$value WHERE player_id='$player_id'");
    }

    function getPlayerAttack($player_id): int
    {
        return $this->getUniqueValueFromDB("SELECT attack from player WHERE player_id='$player_id'");
    }

    function setPlayerAttack(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET attack=$value WHERE player_id='$player_id'");
    }

    function getPlayerDefense($player_id): int
    {
        return $this->getUniqueValueFromDB("SELECT defense from player WHERE player_id='$player_id'");
    }

    function setPlayerDefense(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET defense=$value WHERE player_id='$player_id'");
    }

    function getPlayerClergy($player_id): int
    {
        return $this->getUniqueValueFromDB("SELECT clergy from player WHERE player_id='$player_id'");
    }

    function setPlayerClergy(int $value, $player_id): void
    {
        if ($value < 1 || $value > 3) {
            throw new BgaVisibleSystemException("Invalid value for Clergy");
        }

        $this->DbQuery("UPDATE player SET clergy=$value WHERE player_id='$player_id'");
    }

    function getPlayerDragon($player_id): int
    {
        return $this->getUniqueValueFromDB("SELECT dragon from player WHERE player_id='$player_id'");
    }

    function setPlayerDragon(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET dragon=$value WHERE player_id='$player_id'");
    }

    function getPlayerPower($player_id): int
    {
        return $this->getUniqueValueFromDB("SELECT gem_power from player WHERE player_id='$player_id'");
    }

    function setPlayerPower(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET gem_power=$value WHERE player_id='$player_id'");
    }

    function getPlayerGems($player_id): int
    {
        return $this->getUniqueValueFromDB("SELECT gem_treasure from player WHERE player_id='$player_id'");
    }

    function setPlayerGems(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET gem_treasure=$value WHERE player_id='$player_id'");
    }

    function getPlayerGold($player_id): int
    {
        return $this->getUniqueValueFromDB("SELECT gold from player WHERE player_id='$player_id'");
    }

    function setPlayerGold(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET gold=$value WHERE player_id='$player_id'");
    }

    function getPlayerMaxGold($player_id): int
    {
        return $this->getUniqueValueFromDB("SELECT max_gold from player WHERE player_id='$player_id'");
    }

    function setPlayerMaxGold(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET max_gold=$value WHERE player_id='$player_id'");
    }

    function getPlayerReroll($player_id): int
    {
        return $this->getUniqueValueFromDB("SELECT reroll from player WHERE player_id='$player_id'");
    }

    function setPlayerReroll(int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET reroll=$value WHERE player_id='$player_id'");
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Getters
    ////////////

    function getDice()
    {
        return array(
            1 => $this->getGameStateValue("die_1"),
            2 => $this->getGameStateValue("die_2")
        );
    }

    function getSupply()
    {
        $supply = array();

        $players = $this->loadPlayersBasicInfos();

        $crown_unclaimed = true;
        $cross_unclaimed = true;
        $smith_unclaimed = true;

        foreach ($players as $player_id => $player) {
            if ($this->getPlayerCrown($player_id)) {
                $crown_unclaimed = false;
            };

            if ($this->getPlayerCross($player_id)) {
                $cross_unclaimed = false;
            };

            if ($this->getPlayerSmith($player_id)) {
                $smith_unclaimed = false;
            };
        }

        $supply["crown"] = $crown_unclaimed;
        $supply["cross"] = $cross_unclaimed;
        $supply["smith"] = $smith_unclaimed;

        return $supply;
    }

    function getClaimedSupply()
    {
        $supply = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $supply[$player_id]["crown"] = $this->getPlayerCrown($player_id);
            $supply[$player_id]["cross"] = $this->getPlayerCross($player_id);
            $supply[$player_id]["smith"] = $this->getPlayerSmith($player_id);
        }

        return $supply;
    }

    function getInactiveCouncil()
    {
        $players = $this->loadPlayersBasicInfos();

        $council = array();

        foreach ($players as $player_id => $player) {
            $council[$player_id] =
                $this->council->getCardsInLocation("inactive", $player_id);
        }

        return $council;
    }

    function getCouncil()
    {
        $players = $this->loadPlayersBasicInfos();

        $council = array();

        foreach ($players as $player_id => $player) {
            for ($chair = 1; $chair <= 6; $chair++) {
                $location_cards = $this->council->getCardsInLocation("vested:" . $player_id, $chair);
                $counselor = array_shift($location_cards);
                $council[$player_id][$chair] = $counselor;
            }
        }

        return $council;
    }

    function getGemsByLocation()
    {
        $gems = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $power = $this->getPlayerPower($player_id);
            $treasure = $this->getPlayerGems($player_id);

            $gems[$player_id]["power"] = $power;
            $gems[$player_id]["treasure"] = $treasure;
        }

        return $gems;
    }

    function getAttack()
    {
        $attack = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $attack[$player_id] = $this->getPlayerAttack($player_id);
        }

        return $attack;
    }

    function getDefense()
    {
        $defense = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $defense[$player_id] = $this->getPlayerDefense($player_id);
        }

        return $defense;
    }

    function getChurch()
    {
        $church = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $church[$player_id] = $this->getPlayerClergy($player_id);
        }

        return $church;
    }

    function getTreasure()
    {
        $treasure = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $treasure[$player_id] = $this->getPlayerGold($player_id);
        }

        return $treasure;
    }

    function getDragon()
    {
        $dragon = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $dragon[$player_id] = $this->getPlayerDragon($player_id);
        }

        return $dragon;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Operations
    ////////////

    function negativateGold($player_id): void
    {
        $prev_gold = $this->getPlayerGold($player_id);

        $this->setPlayerGold(-1, $player_id);

        $this->notifyAllPlayers(
            "generateGold",
            "",
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevGold" => $prev_gold,
                "totalGold" => -1,
                "treasure" => $this->getTreasure(),
            )
        );
    }

    function spendGold(int $value, $player_id, bool $message = false): int
    {
        if ($value <= 0) {
            throw new BgaVisibleSystemException("The gold value must be positive");
        }

        $prev_gold = $this->getPlayerGold($player_id);

        if ($value > $prev_gold) {
            throw new BgaUserException($this->_("You don't have the gold required by this action"));
        }

        $total_gold = $prev_gold - $value;

        $this->setPlayerGold($total_gold, $player_id);

        $this->notifyAllPlayers(
            "generateGold",
            $message ? clienttranslate('${player_name} spends ${spentGold} of gold') : "",
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevGold" => $prev_gold,
                "spentGold" => $value,
                "totalGold" => $total_gold,
                "treasure" => $this->getTreasure(),
            )
        );

        return $total_gold;
    }

    function generateGold(int $value, $player_id): int
    {
        if ($value <= 0) {
            throw new BgaVisibleSystemException("The gold value must be positive");
        }

        $prev_gold = $this->getPlayerGold($player_id);

        $max_gold = $this->getPlayerMaxGold($player_id);

        if ($prev_gold == $max_gold) {
            return $max_gold;
        }

        $total_gold = $prev_gold + $value;

        if ($total_gold > $max_gold) {
            $total_gold = $max_gold;
        }

        $this->setPlayerGold($total_gold, $player_id);

        $this->notifyAllPlayers(
            "generateGold",
            clienttranslate('${player_name} generates ${generatedGold} of gold'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevGold" => $prev_gold,
                "generatedGold" => $total_gold - $prev_gold,
                "totalGold" => $total_gold
            )
        );

        return $total_gold;
    }

    function increaseAttack(int $value, $player_id): int
    {
        $prev_swords = $this->getPlayerAttack($player_id);

        if ($prev_swords == 5) {
            throw new BgaUserException($this->_("The attack can't be further improved"));
        }

        $total_swords = $prev_swords + $value;

        if ($total_swords > 5) {
            $total_swords = 5;
        }

        $this->setPlayerAttack($total_swords, $player_id);

        $this->notifyAllPlayers(
            "increaseAttack",
            clienttranslate('${player_name} obtains ${newSwords} sword(s)'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevSwords" => $prev_swords,
                "newSwords" => abs($total_swords - $prev_swords),
                "totalSwords" => $total_swords,
                "attack" => $this->getAttack()
            )
        );

        return $total_swords;
    }

    function decreaseAttack(int $value, $player_id, $message = false)
    {
        $prev_swords = $this->getPlayerAttack($player_id);

        $total_swords = $prev_swords - $value;

        if ($value > $prev_swords) {
            $total_swords = 0;
        }

        $this->setPlayerAttack($total_swords, $player_id);

        $this->notifyAllPlayers(
            "increaseAttack",
            $message ? clienttranslate('${newSwords} sword(s) of ${player_name} are destroyed') : "",
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevSwords" => $prev_swords,
                "newSwords" => abs($total_swords - $prev_swords),
                "totalSwords" => $total_swords,
            )
        );

        return $total_swords;
    }

    function increaseDefense(int $value, $player_id, $message = true): int
    {
        $prev_shields = $this->getPlayerDefense($player_id);

        if ($prev_shields == 5) {
            throw new BgaUserException($this->_("The defense can't be further improved"));
        }

        $total_shields = $prev_shields + $value;

        if ($total_shields > 5) {
            $total_shields = 5;
        }

        $this->setPlayerDefense($total_shields, $player_id);

        $this->notifyAllPlayers(
            "increaseDefense",
            $message ? clienttranslate('${player_name} obtains ${newShields} shield(s)') : "",
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevShields" => $prev_shields,
                "newShields" => abs($total_shields - $prev_shields),
                "totalShields" => $total_shields,
            )
        );

        return $total_shields;
    }

    function decreaseDefense(int $value, $player_id, $message = false)
    {
        $prev_shields = $this->getPlayerDefense($player_id);

        $total_shields = $prev_shields - $value;

        if ($value > $prev_shields) {
            $total_shields = 0;
        }

        $this->setPlayerDefense($total_shields, $player_id);

        if ($prev_shields > 0) {
            $this->notifyAllPlayers(
                "increaseDefense",
                $message ? clienttranslate('${newShields} shield(s) of ${player_name} are destroyed') : "",
                array(
                    "player_id" => $player_id,
                    "player_name" => $this->getPlayerNameById($player_id),
                    "prevShields" => $prev_shields,
                    "newShields" => abs($total_shields - $prev_shields),
                    "totalShields" => $total_shields,
                )
            );
        }

        return $total_shields;
    }

    function activateGoldenSquare($player_id): void
    {
        $other_player_id = $this->getPlayerAfter($player_id);

        $this->negativateGold($other_player_id);
    }

    function activateBlueSquare($player_id): void
    {
        $other_player_id = $this->getPlayerAfter($player_id);
        $this->decreaseAttack(1, $other_player_id);
    }

    function activateRedSquare($player_id): void
    {
        $other_player_id = $this->getPlayerAfter($player_id);
        $this->levelDownDragon(1, $other_player_id);
    }

    function moveClergy(int $square_id, $player_id): void
    {
        $prev_square = $this->getPlayerClergy($player_id);

        if ($prev_square == $square_id) {
            throw new BgaUserException($this->_("You must move the clergy to other square"));
        }

        $this->setPlayerClergy($square_id, $player_id);

        $square = $this->church_squares[$square_id];

        $this->notifyAllPlayers(
            "moveClergy",
            clienttranslate('${player_name} moves the Clergy to the ${square_label} square and activates its effect'),
            array(
                "i18n" => array("square_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "square_label" => $square["label_tr"],
                "newSquare" => $square_id,
                "prevSquare" => $prev_square,
                "church" => $this->getChurch()
            )
        );

        if ($square_id == 1) {
            $this->activateGoldenSquare($player_id);
        }

        if ($square_id == 2) {
            $this->activateBlueSquare($player_id);
        }

        if ($square_id == 3) {
            $this->activateRedSquare($player_id);
        }
    }

    function levelUpDragon(int $value, $player_id): int
    {
        $prev_level = $this->getPlayerDragon($player_id);

        if ($prev_level == 5) {
            throw new BgaUserException($this->_("The level of the dragon can't be further increased"));
        }

        $total_level = $prev_level + $value;

        if ($total_level > 5) {
            $total_level = 5;
        }

        $this->setPlayerDragon($total_level, $player_id);

        $this->notifyAllPlayers(
            "levelUpDragon",
            clienttranslate('${player_name} levels up the dragon'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevLevel" => $prev_level,
                "totalLevel" => $total_level,
                "dragon" => $this->getDragon()
            ),
        );

        if ($total_level == 5) {
            $this->dragonRage($player_id);
        }

        return $total_level;
    }

    function levelDownDragon(int $value, $player_id): int
    {
        $prev_level = $this->getPlayerDragon($player_id);

        $total_level = $prev_level - $value;

        if ($value > $prev_level) {
            $total_level = 0;
        }

        $this->setPlayerDragon($total_level, $player_id);

        $this->notifyAllPlayers(
            "levelUpDragon",
            "",
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevLevel" => $prev_level,
                "totalLevel" => $total_level,
                "dragon" => $this->getDragon()
            ),
        );

        return $total_level;
    }

    function claimCrown($player_id): void
    {
        $other_player_id = $this->getPlayerAfter($player_id);

        $owned = $this->getPlayerCrown($other_player_id);

        $this->setPlayerCrown(1, $player_id);
        $this->setPlayerCrown(0, $other_player_id);

        $this->notifyAllPlayers(
            "claimCrown",
            clienttranslate('${player_name} obtains the ${token_label} token'),
            array(
                "i18n" => array("token_label"),
                "token_label" => $this->tokens_info[1]["label_tr"],
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "other_player_id" => $other_player_id,
                "isOwned" => $owned,
                "supply" => $this->getSupply()
            )
        );
    }

    function claimCross($player_id): void
    {
        $other_player_id = $this->getPlayerAfter($player_id);

        $owned = $this->getPlayerCross($other_player_id);

        $this->setPlayerCross(1, $player_id);
        $this->setPlayerCross(0, $other_player_id);

        $this->notifyAllPlayers(
            "claimCross",
            clienttranslate('${player_name} obtains the ${token_label} token'),
            array(
                "i18n" => array("token_label"),
                "token_label" => $this->tokens_info[2]["label_tr"],
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "other_player_id" => $other_player_id,
                "isOwned" => $owned,
                "supply" => $this->getSupply()
            )
        );
    }

    function claimSmith($player_id): void
    {
        $other_player_id = $this->getPlayerAfter($player_id);

        $owned = $this->getPlayerSmith($other_player_id);

        $this->setPlayerSmith(1, $player_id);
        $this->setPlayerSmith(0, $other_player_id);

        $this->notifyAllPlayers(
            "claimSmith",
            clienttranslate('${player_name} obtains the ${token_label} token'),
            array(
                "i18n" => array("token_label"),
                "token_label" => $this->tokens_info[3]["label_tr"],
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "other_player_id" => $other_player_id,
                "isOwned" => $owned,
                "supply" => $this->getSupply()
            )
        );
    }

    function claimGem($player_id): int
    {
        $other_player_id = $this->getPlayerAfter($player_id);

        $total_gems = $this->getPlayerGems($player_id) + 1;

        $this->setPlayerMaxGold(7 - $total_gems, $player_id);
        $this->setPlayerGems($total_gems, $player_id);

        $prev_power = $this->getPlayerPower($other_player_id);
        $this->setPlayerPower($prev_power - 1, $other_player_id);

        $prev_highest_gems = $this->getGameStateValue("highest_gems");

        if ($total_gems > $prev_highest_gems) {
            $this->setGameStateValue("highest_gems", $total_gems);
        }

        $this->notifyAllPlayers(
            "claimGem",
            clienttranslate('${player_name} obtains a gem'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "other_player_id" => $other_player_id,
                "totalGems" => $total_gems,
                "gemsByLocations" => $this->getGemsByLocation(),
            )
        );

        $this->claimCrown($player_id);
        $this->claimCross($other_player_id);

        $gold = $this->getPlayerGold($player_id);
        $max_gold = $this->getPlayerMaxGold($player_id);

        if ($gold >= $max_gold) {
            $this->spendGold(1, $player_id);
        }

        return $total_gems;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Checks and possible picks
    ////////////

    function canActivate(int $counselor_id, $player_id): bool
    {
        $can_activate = true;

        if ($counselor_id == 1) {
            $can_activate = $this->getPlayerAttack($player_id) + $this->getPlayerDefense($player_id) < 10;
        }

        if ($counselor_id == 2) {
            $can_activate = $this->getPlayerGold($player_id) < $this->getPlayerMaxGold($player_id);
        }

        if ($counselor_id == 3) {
            $can_activate = $this->getPlayerDragon($player_id) < 5;
        }

        if ($counselor_id == 4) {
            $can_activate = !!$this->getNoblePicks($player_id);
        }

        return $can_activate;
    }

    function getNoblePicks($player_id): array
    {
        $noble_picks = array();

        $council = $this->council->getCardsInLocation("vested:" . $player_id);

        foreach ($council as $card_id => $card) {
            $counselor_id = $card["type_arg"];

            if ($counselor_id != 4) {
                if ($this->canActivate($counselor_id, $player_id)) {
                    $noble_picks[$card_id] = $counselor_id;
                }
            }
        }
        return $noble_picks;
    }

    function getCommanderPicks($player_id): array
    {
        $commander_picks = array();

        if ($this->getPlayerAttack($player_id) < 5) {
            $commander_picks["ATTACK"] = "ATTACK";
        }

        if ($this->getPlayerDefense($player_id) < 5) {
            $commander_picks["DEFENSE"] = "DEFENSE";
        }

        return $commander_picks;
    }

    function canPayDragon($player_id): bool
    {
        $level = $this->getPlayerDragon($player_id) + 1;

        $price = $this->dragon_prices[$level];

        return $this->getPlayerGold($player_id) >= $price;
    }

    function getBuyableAreas($player_id): array
    {
        $buyable_areas = array();

        $commander_picks = $this->getCommanderPicks($player_id);

        if ($this->getPlayerGold($player_id) >= 3) {
            if (in_array("ATTACK", $commander_picks)) {
                $buyable_areas["ATTACK"] = "ATTACK";
            }

            if (in_array("DEFENSE", $commander_picks)) {
                $buyable_areas["DEFENSE"] = "DEFENSE";
            }
        }

        if ($this->getPlayerDragon($player_id) < 5 && $this->canPayDragon($player_id)) {
            $buyable_areas["DRAGON"] = "DRAGON";
        }

        return $buyable_areas;
    }

    function getTokenPicks($player_id): array
    {
        $token_picks = array();

        if ($this->getPlayerCrown($player_id) && $this->canActivate(2, $player_id)) {
            $token_picks["CROWN"] = "CROWN";
        }

        if ($this->getPlayerCross($player_id)) {
            $token_picks["CROSS"] = "CROSS";
        }

        return $token_picks;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Counselor effects
    ////////////

    function commanderAttack($player_id): int
    {
        $this->notifyAllPlayers(
            "activateCommander",
            clienttranslate('${player_name} activates the ${counselor_name}'),
            array(
                "i18n" => array("counselor_name"),
                "player_name" => $this->getPlayerNameById($player_id),
                "counselor_name" => clienttranslate("Militia Commander")
            )
        );
        return $this->increaseAttack(1, $player_id);
    }
    function commanderDefense($player_id): int
    {
        $this->notifyAllPlayers(
            "activateCommander",
            clienttranslate('${player_name} activates the ${counselor_name}'),
            array(
                "i18n" => array("counselor_name"),
                "player_name" => $this->getPlayerNameById($player_id),
                "counselor_name" => clienttranslate("Militia Commander")
            )
        );

        return $this->increaseDefense(1, $player_id);
    }

    function masterOfCoin($player_id): int
    {
        $this->notifyAllPlayers(
            "activateMaster",
            clienttranslate('${player_name} activates the ${counselor_name}'),
            array(
                "i18n" => array("counselor_name"),
                "player_name" => $this->getPlayerNameById($player_id),
                "counselor_name" => clienttranslate("Master of Coin")
            )
        );

        return $this->generateGold(3, $player_id);
    }

    function sorcerer($player_id): int
    {
        $this->notifyAllPlayers(
            "activateSorcerer",
            clienttranslate('${player_name} activates the ${counselor_name}'),
            array(
                "i18n" => array("counselor_name"),
                "player_name" => $this->getPlayerNameById($player_id),
                "counselor_name" => clienttranslate("Sorcerer")
            )
        );

        return $this->levelUpDragon(1, $player_id);
    }

    function smith($player_id): void
    {
        $this->notifyAllPlayers(
            "activateSmith",
            clienttranslate('${player_name} activates the ${counselor_name}'),
            array(
                "i18n" => array("counselor_name"),
                "player_name" => $this->getPlayerNameById($player_id),
                "counselor_name" => clienttranslate("Smith")
            )
        );

        $this->claimSmith($player_id);
    }

    function priestGolden($player_id): void
    {
        $this->notifyAllPlayers(
            "activatePriest",
            clienttranslate('${player_name} activates the ${counselor_name}'),
            array(
                "i18n" => array("counselor_name"),
                "player_name" => $this->getPlayerNameById($player_id),
                "counselor_name" => clienttranslate("Priest"),
            )
        );

        $this->moveClergy(1, $player_id);
    }
    function priestBlue($player_id): void
    {
        $this->notifyAllPlayers(
            "activatePriest",
            clienttranslate('${player_name} activates the ${counselor_name}'),
            array(
                "i18n" => array("counselor_name"),
                "player_name" => $this->getPlayerNameById($player_id),
                "counselor_name" => clienttranslate("Priest")
            )
        );

        $this->moveClergy(2, $player_id);
    }
    function priestRed($player_id): void
    {
        $this->notifyAllPlayers(
            "activatePriest",
            clienttranslate('${player_name} activates the ${counselor_name}'),
            array(
                "i18n" => array("counselor_name"),
                "player_name" => $this->getPlayerNameById($player_id),
                "counselor_name" => clienttranslate("Priest")
            )
        );

        $this->moveClergy(3, $player_id);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    ////////////

    function rollDice($auto = false)
    {
        if (!$auto) {
            $this->checkAction("rollDice");
        }

        $player_id = $this->getActivePlayerId();

        $die_1 = bga_rand(1, 6);
        $die_2 = bga_rand(1, 6);

        $this->setGameStateValue("die_1", $die_1);
        $this->setGameStateValue("die_2", $die_2);

        $player_name = $this->getActivePlayerName();

        $this->notifyAllPlayers(
            "dieRoll",
            clienttranslate('${player_name} rolls the first die. The result is ${result}'),
            array(
                "player_name" => $player_name,
                "die" => 1,
                "result" => $die_1
            )
        );

        $this->notifyAllPlayers(
            "dieRoll",
            clienttranslate('${player_name} rolls the second die. The result is ${result}'),
            array(
                "player_name" => $player_name,
                "die" => 2,
                "result" => $die_2,
            )
        );

        if ($die_1 == $die_2) {
            $this->notifyAllPlayers(
                "skipDecision",
                clienttranslate('The dice have the same result. Decision phase automatically skipped'),
                array()
            );

            $this->decideDice(1, true);
            return;
        }

        $this->gamestate->nextState("decisionPhase");
    }

    function decideDice($die, $auto = false)
    {
        if (!$auto) {
            $this->checkAction("decideDice");
        }

        $player_id = $this->getActivePlayerId();

        $chair_die = 0;
        $gold_die = 0;

        if ($die == 1) {
            $chair_die = $this->getGameStateValue("die_1");
            $gold_die = $this->getGameStateValue("die_2");
        } else {
            $chair_die = $this->getGameStateValue("die_2");
            $gold_die = $this->getGameStateValue("die_1");
        }

        $this->notifyAllPlayers("decideDice", clienttranslate('${player_name} activates the chair ${chair_die}'), array(
            "player_name" => $this->getActivePlayerName(),
            "chair_die" => $chair_die,
        ));

        $this->generateGold($gold_die, $player_id);

        $location_counselors = $this->council->getCardsInLocation("vested:" . $player_id, $chair_die);
        $counselor = array_shift($location_counselors);

        if ($counselor === null) {
            $this->setGameStateValue("active_chair", $chair_die);
            $this->gamestate->nextState("counselorVesting");
            return;
        }

        $counselor_id = $counselor["type_arg"];

        $this->setGameStateValue("active_counselor", $counselor_id);

        if (!$this->canActivate($counselor_id, $player_id)) {
            if (!$this->getBuyableAreas($player_id)) {
                $this->gamestate->nextState("preBattle");
                return;
            }

            $this->gamestate->nextState("buyingPhase");
            return;
        }

        $this->gamestate->nextState("counselorActivation");
    }

    function vestCounselor($card_id)
    {
        $this->checkAction("vestCounselor");

        $player_id = $this->getActivePlayerId();

        $active_chair = $this->getGameStateValue("active_chair");

        if ($this->council->getCard($card_id)["location"] !== "inactive") {
            throw new BgaVisibleSystemException("This counselor is already on a chair");
        }

        $this->council->moveCard($card_id, "vested:" . $player_id, $active_chair);

        $card = $this->council->getCard($card_id);
        $counselor_id = $card["type_arg"];
        $counselor = $this->counselors_info[$counselor_id];

        $this->notifyPlayer(
            $player_id,
            "vestCounselorPrivately",
            "",
            array(
                "player_id" => $this->getActivePlayerId(),
                "counselorId" => $counselor_id,
                "cardId" => $card_id,
                "chair" => $active_chair
            )
        );

        $this->notifyAllPlayers(
            "vestCounselor",
            clienttranslate('${player_name} picks the ${counselorName} to occupy the chair ${chair}'),
            array(
                "i18n" => array("counselorName"),
                "player_id" => $this->getActivePlayerId(),
                "player_name" => $this->getActivePlayerName(),
                "counselorName" => $counselor["name"],
                "counselorId" => $counselor_id,
                "cardId" => $card_id,
                "chair" => $active_chair
            )
        );

        $this->setGameStateValue("active_counselor", $counselor_id);

        if (!$this->canActivate($counselor_id, $player_id)) {
            if (!$this->getBuyableAreas($player_id)) {
                $this->gamestate->nextState("preBattle");
                return;
            }

            $this->gamestate->nextState("buyingPhase");
            return;
        }

        $this->gamestate->nextState("counselorActivation");
    }

    function activateCounselor()
    {
        $this->checkAction("activateCounselor");

        $player_id = $this->getActivePlayerId();

        $active_counselor = $this->getGameStateValue("active_counselor");

        if ($active_counselor == 2) {
            $this->masterOfCoin($player_id);
        }

        if ($active_counselor == 3) {
            $this->sorcerer($player_id);
        }

        if ($active_counselor == 5) {
            $this->smith($player_id);
        }

        if ($active_counselor == 1) {
            $this->gamestate->nextState("commanderActivation");
            return;
        }

        if ($active_counselor == 4) {
            $this->gamestate->nextState("nobleActivation");
            return;
        }

        if ($active_counselor == 6) {
            $this->gamestate->nextState("priestActivation");
            return;
        }

        if (!$this->getBuyableAreas($player_id)) {
            $this->gamestate->nextState("preBattle");
            return;
        }


        $this->gamestate->nextState("buyingPhase");
    }

    function activateNoble($card_id)
    {
        $this->checkAction("activateNoble");

        $player_id = $this->getActivePlayerId();

        $counselor = $this->council->getCard($card_id);

        $active_counselor = $counselor["type_arg"];

        if ($counselor["location"] !== ("vested:" . $player_id)) {
            throw new BgaVisibleSystemException("The Noble can only activate counselor on chairs");
        }

        if ($active_counselor == 4) {
            throw new BgaUserException($this->_("You can't activate the Noble with its own effect"));
        }

        $this->notifyAllPlayers(
            "activateNoble",
            clienttranslate('${player_name} activates the Noble. The effect of other counselor is activated'),
            array("player_name" => $this->getPlayerNameById($player_id))
        );

        if (!in_array($active_counselor, $this->getNoblePicks($player_id))) {
            throw new BgaUserException($this->_("You can't activate this counselor now"));
        }

        if ($active_counselor == 2) {
            $this->masterOfCoin($player_id);
        }

        if ($active_counselor == 3) {
            $this->sorcerer($player_id);
        }

        if ($active_counselor == 5) {
            $this->smith($player_id);
        }

        if ($active_counselor == 1) {
            $this->gamestate->nextState("commanderActivation");
            return;
        }

        if ($active_counselor == 6) {
            $this->gamestate->nextState("priestActivation");
            return;
        }

        if (!$this->getBuyableAreas($player_id)) {
            $this->gamestate->nextState("preBattle");
            return;
        }

        $this->gamestate->nextState("buyingPhase");
    }

    function activateCommander($militia)
    {
        $this->checkAction("activateCommander");

        $player_id = $this->getActivePlayerId();

        if ($militia === "ATTACK") {
            $this->commanderAttack($player_id);
        }

        if ($militia === "DEFENSE") {
            $this->commanderDefense($player_id);
        }

        if (!$this->getBuyableAreas($player_id)) {
            $this->gamestate->nextState("preBattle");
            return;
        }

        $this->gamestate->nextState("buyingPhase");
    }

    function activatePriest($square)
    {
        $this->checkAction("activatePriest");

        $player_id = $this->getActivePlayerId();

        if ($square == 1) {
            $this->priestGolden($player_id);
        }

        if ($square == 2) {
            $this->priestBlue($player_id);
        }

        if ($square == 3) {
            $this->priestRed($player_id);
        }

        if (!$this->getBuyableAreas($player_id)) {
            $this->gamestate->nextState("preBattle");
            return;
        }

        $this->gamestate->nextState("buyingPhase");
    }

    function cancelActivation()
    {
        $this->checkAction("cancelActivation");

        $this->notifyAllPlayers(
            "cancelActivation",
            "",
            array()
        );

        $this->gamestate->nextState("cancel");
    }

    function skipActivation()
    {
        $this->checkAction("skipActivation");

        $player_id = $this->getActivePlayerId();

        $counselor_id = $this->getGameStateValue("active_counselor");

        $this->notifyAllPlayers(
            "skipActivation",
            clienttranslate('${player_name} skips the activation of the ${counselor_name}'),
            array(
                "i18n" => array("counselor_name"),
                "player_name" => $this->getPlayerNameById($player_id),
                "counselor_name" => $this->counselors_info[$counselor_id]["name"]
            )
        );

        $this->setGameStateValue("active_counselor", 0);
        $this->setGameStateValue("active_chair", 0);

        $this->gamestate->nextState("skip");
    }

    function buyArea($area)
    {
        $this->checkAction("buyArea");

        $player_id = $this->getActivePlayerId();

        if (!in_array($area, $this->getBuyableAreas($player_id))) {
            throw new BgaUserException($this->_("You can't spend gold with this area now"));
        }

        if ($area === "ATTACK") {
            $this->spendGold(3, $player_id, true);
            $this->increaseAttack(1, $player_id);

            if ($this->getPlayerSmith($player_id)) {
                $this->setGameStateValue("active_equipment", 1);
                $this->gamestate->nextState("smithTokenActivation");
                return;
            }
        }

        if ($area === "DEFENSE") {
            $this->spendGold(3, $player_id, true);
            $this->increaseDefense(1, $player_id);

            if ($this->getPlayerSmith($player_id)) {
                $this->setGameStateValue("active_equipment", 2);
                $this->gamestate->nextState("smithTokenActivation");
                return;
            }
        }

        if ($area === "DRAGON") {
            $level = $this->getPlayerDragon($player_id) + 1;
            $price = $this->dragon_prices[$level];

            $this->spendGold($price, $player_id, true);
            $this->levelUpDragon(1, $player_id);
        }

        if ($this->getBuyableAreas($player_id)) {
            $this->gamestate->nextState("buyAgain");
            return;
        }

        $this->gamestate->nextState("preBattle");
    }

    function skipBuying()
    {
        $this->checkAction("skipBuying");

        $player_id = $this->getActivePlayerId();

        $this->notifyAllPlayers(
            "skipBuying",
            clienttranslate('${player_name} skips the buying phase'),
            array(
                "player_name" => $this->getPlayerNameById($player_id)
            )
        );

        $this->gamestate->nextState("skip");
    }

    function activateToken($token)
    {
        $this->checkAction("activateToken");

        $player_id = $this->getActivePlayerId();

        /**
         * @disregard P1009 Undefined type
         */
        $state_id = $this->gamestate->state_id();
        $state_name = $this->getStateName();

        $this->setGameStateValue("token_state",  $state_id);

        if (!in_array($token, $this->getTokenPicks($player_id))) {
            throw new BgaUserException($this->_("You can't use this token now"));
        }

        if ($token === "CROWN") {
            $this->notifyAllPlayers(
                "activateCrownToken",
                clienttranslate('${player_name} activates the ${token_label} token'),
                array(
                    "i18n" => array("token_label"),
                    "token_label" => $this->tokens_info[1]["label_tr"],
                    "player_id" => $player_id,
                    "player_name" => $this->getPlayerNameById($player_id),
                )
            );

            $this->generateGold(3, $player_id);

            $this->setPlayerCrown(0, $player_id);

            if ($state_name === "preBattleToken") {
                $this->game->nextState("afterToken");
                return;
            }

            $this->gamestate->jumpToState($state_id);
            return;
        }

        if ($token === "CROSS") {
            $this->gamestate->jumpToState(52);
            return;
        }
    }

    function activateCrossToken($square_id)
    {
        $this->checkAction("activateCrossToken");

        $player_id = $this->getActivePlayerId();

        $this->notifyAllPlayers(
            "activateCrossToken",
            clienttranslate('${player_name} activates the ${token_label} token'),
            array(
                "i18n" => array("token_label"),
                "token_label" => $this->tokens_info[2]["label_tr"],
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
            )
        );

        $this->moveClergy($square_id, $player_id);

        $this->setPlayerCross(0, $player_id);

        $prev_state = $this->getGameStateValue("token_state");

        if ($prev_state == 51 && !$this->getNoblePicks($player_id)) {
            $this->gamestate->nextState("afterToken");
            return;
        }

        $this->gamestate->jumpToState($prev_state);
    }

    function activateSmithToken()
    {
        $this->checkAction("activateSmithToken");

        $player_id = $this->getActivePlayerId();

        if ($this->getBuyableAreas($player_id)) {
            $this->gamestate->nextState("buyAgain");
        }

        $this->notifyAllPlayers(
            "activateSmithToken",
            clienttranslate('${player_name} activates the ${token_label} token'),
            array(
                "i18n" => array("token_label"),
                "token_label" => $this->tokens_info[3]["label_tr"],
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
            )
        );

        $equipment = $this->getGameStateValue("active_equipment");

        if ($equipment == 1) {
            $this->increaseAttack(1, $player_id);
        }

        if ($equipment == 2) {
            $this->increaseDefense(1, $player_id);
        }

        $this->setPlayerSmith(0, $player_id);

        $this->gamestate->nextState("preBattle");
    }

    function cancelToken()
    {
        $this->checkAction("cancelToken");

        $prev_state = $this->getGameStateValue("token_state");

        $this->gamestate->jumpToState($prev_state);
    }

    function skipToken()
    {
        $this->checkAction("skipToken");

        $player_id = $this->getActivePlayerId();

        $this->notifyAllPlayers(
            "skipToken",
            clienttranslate('${player_name} decides to not use any token'),
            array("player_name" => $this->getPlayerNameById($player_id))
        );

        $this->gamestate->nextState("skip");
    }

    function startBattle()
    {
        $this->checkAction("startBattle");

        $player_id = $this->getActivePlayerId();

        $this->setGameStateValue("attacker", $player_id);

        $other_player_id = $this->getPlayerAfter($player_id);

        $die_1 = bga_rand(1, 6);
        $die_2 = bga_rand(1, 6);

        $this->setGameStateValue("die_1", $die_1);
        $this->setGameStateValue("die_2", $die_2);

        $this->notifyAllPlayers(
            "startBattle",
            clienttranslate('${player_name} declares an attack and starts a battle'),
            array(
                "player_name" => $this->getPlayerNameById($player_id),
            )
        );

        $this->notifyAllPlayers(
            "dieRoll",
            clienttranslate('${player_name} rolls the first die. The result is ${result}'),
            array(
                "player_name" => $this->getPlayerNameById($player_id),
                "die" => 1,
                "result" => $die_1
            )
        );

        $this->notifyAllPlayers(
            "dieRoll",
            clienttranslate('${player_name} rolls the second die. The result is ${result}'),
            array(
                "player_name" => $this->getPlayerNameById($other_player_id),
                "die" => 2,
                "result" => $die_2
            )
        );

        $this->gamestate->nextState("battle");
    }

    function skipBattle()
    {
        $this->checkAction("skipBattle");

        $player_id = $this->getActivePlayerId();

        $this->notifyAllPlayers(
            "skipBattle",
            clienttranslate('${player_name} decides to not start a battle'),
            array("player_name" => $this->getPlayerNameById($player_id))
        );


        $this->gamestate->nextState("skip");
    }

    function disputeResult()
    {
        $this->checkAction("disputeResult");

        $player_id = $this->getActivePlayerId();

        $attacker_id = $this->getGameStateValue("attacker");

        $is_attacker = $attacker_id == $player_id;

        $reroll = $this->getPlayerReroll($player_id);
        $this->setPlayerReroll($reroll + 1, $player_id);

        $this->spendGold($reroll, $player_id, true);

        $die = bga_rand(1, 6);

        $this->notifyAllPlayers(
            "dieRoll",
            clienttranslate('${player_name} rerolls his die. The result is ${result}'),
            array(
                "player_name" => $this->getPlayerNameById($player_id),
                "die" => $is_attacker ? 1 : 2,
                "result" => $die
            )
        );

        if ($is_attacker) {
            $this->setGameStateValue("die_1", $die);
        } else {
            $this->setGameStateValue("die_2", $die);
        }

        if ($this->getPlayerGold($player_id) < $reroll) {
            $this->setPlayerReroll(0, $player_id);
        }

        $this->gamestate->nextState("betweenDisputes");
    }

    function skipDispute()
    {
        $this->checkAction("skipDispute");

        $player_id = $this->getActivePlayerId();

        $this->setPlayerReroll(0, $player_id);

        $player_id = $this->getActivePlayerId();

        $this->notifyAllPlayers(
            "skipDispute",
            clienttranslate('${player_name} accepts the result of his die'),
            array("player_name" => $this->getPlayerNameById($player_id))
        );

        $this->gamestate->nextState("skip");
    }

    function destroyShields($value)
    {
        $this->checkAction("destroyShields");

        if ($value > $this->getGameStateValue("damaged_shields")) {
            throw new BgaVisibleSystemException("You can't destroy more shields than what you've damaged");
        }

        $player_id = $this->getActivePlayerId();

        $other_player_id = $this->getPlayerAfter($player_id);

        $final_shields = $this->decreaseDefense($value, $other_player_id, true);

        if ($final_shields == 0) {
            $this->claimGem($player_id);
        }

        $this->gamestate->nextState("betweenTurns");
    }

    function skipDestruction()
    {
        $this->checkAction("skipDestruction");

        $player_id = $this->getActivePlayerId();

        $this->notifyAllPlayers(
            "skipDestruction",
            clienttranslate('${player_name} skips the destruction of shields'),
            array("player_name" => $this->getPlayerNameById($player_id))
        );

        $this->gamestate->nextState("skip");
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    function argCounselorVesting()
    {
        return array(
            "chair" => $this->getGameStateValue("active_chair")
        );
    }

    function argCounselorActivation()
    {
        $counselor_id = $this->getGameStateValue("active_counselor");

        return array(
            "counselor_name" => $this->counselors_info[$counselor_id]["name"]
        );
    }

    function argBuyingPhase()
    {
        $player_id = $this->getActivePlayerId();

        return array("buyableAreas" => $this->getBuyableAreas($player_id));
    }

    function argShieldDestruction()
    {
        $player_id = $this->getActivePlayerId();

        $other_player_id = $this->getPlayerAfter($player_id);
        return array(
            "player_name" => $this->getPlayerNameById($other_player_id),
            "damagedShields" => $this->getGameStateValue("damaged_shields")
        );
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    function stPreBattle()
    {
        if ($this->getGameStateValue("first_turn")) {
            $this->gamestate->nextState("betweenTurns");
            return;
        }

        $player_id = $this->getActivePlayerId();
        $other_player_id = $this->getPlayerAfter($player_id);

        if ($this->getTokenPicks($other_player_id)) {
            $this->activeNextPlayer();

            $this->gamestate->nextState("preBattleToken");
            return;
        }

        $this->gamestate->nextState("battlePhase");
    }

    function stAfterToken()
    {
        $this->activeNextPlayer();

        $this->gamestate->nextState("battlePhase");
    }

    function stBattle()
    {
        $attacker_id = $this->getActivePlayerId();
        $defender_id = $this->getPlayerAfter($attacker_id);

        $attacking_die = $this->getGameStateValue("die_1");
        $defending_die = $this->getGameStateValue("die_2");

        $attack_wins = $attacking_die > $defending_die;

        $loser_id = $attack_wins ? $defender_id : $attacker_id;
        $winner_id = $this->getPlayerAfter($loser_id);

        $initial_loser = $this->getGameStateValue("initial_loser");

        if (!$initial_loser) {
            $initial_loser = $loser_id;
            $this->setGameStateValue("initial_loser", $loser_id);
        }

        $this->dump("initial_loser", $loser_id);

        $initial_winner = $this->getPlayerAfter($initial_loser);

        $winner_reroll = $this->getPlayerReroll($initial_winner);
        $loser_reroll = $this->getPlayerReroll($initial_loser);

        $this->dump("winner_reroll", $winner_reroll);
        $this->dump("loser_reroll", $loser_reroll);

        if (
            $loser_reroll &&
            $this->getPlayerGold($initial_loser) >= $loser_reroll
        ) {
            $this->gamestate->changeActivePlayer($initial_loser);

            $this->gamestate->nextState("resultDispute");
            return;
        }

        if (
            $winner_reroll &&
            $this->getPlayerGold($initial_winner) >= $winner_reroll
        ) {
            $this->gamestate->changeActivePlayer($initial_winner);

            $this->gamestate->nextState("resultDispute");
            return;
        }

        $margin = abs($attacking_die - $defending_die);

        if ($margin > 0) {
            $this->notifyAllPlayers(
                "battleResult",
                clienttranslate('${player_name} wins the battle'),
                array(
                    "player_id" => $winner_id,
                    "player_name" => $this->getPlayerNameById($winner_id)
                )
            );

            if ($attack_wins) {
                $swords = $this->getPlayerAttack($attacker_id);

                if ($margin > $this->getPlayerAttack($attacker_id)) {
                    $margin = $swords;
                }

                $this->setGameStateValue("damaged_shields", $margin);

                $this->gamestate->changeActivePlayer($attacker_id);

                $this->gamestate->nextState("shieldDestruction");
                return;
            }

            $this->decreaseAttack($margin, $loser_id, true);
        }

        if ($margin == 0) {
            $this->notifyAllPlayers(
                "battleTie",
                clienttranslate("It's a tie. No shields or swords are destroyed"),
                array()
            );
        }

        $this->gamestate->nextState("betweenTurns");
    }

    function stBetweenDisputes()
    {
        $this->stBattle();
    }

    function stBetweenTurns()
    {
        $player_id = $this->getActivePlayerId();

        $other_player_id = $this->getPlayerAfter($player_id);

        $this->setPlayerReroll(1, $player_id);
        $this->setPlayerReroll(1, $other_player_id);

        $this->setGameStateValue("attacker", 0);
        $this->setGameStateValue("active_equipment", 0);

        $this->notifyAllPlayers(
            "nextTurn",
            clienttranslate('${player_name} finishes his turn and passes'),
            array("player_name" => $this->getPlayerNameById($player_id))
        );

        $this->giveExtraTime($other_player_id);
        $this->activeNextPlayer();

        if ($this->getGameStateValue("first_turn")) {
            $this->setGameStateValue("first_turn", 0);
        }

        $this->gamestate->nextState("nextTurn");
    }

    function stAutoDiceRoll()
    {
        $this->rollDice(true);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    function zombieTurn($state, $active_player)
    {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    function upgradeTableDb($from_version)
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
        //        if( $from_version <= 1404301345 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //        }
        //        if( $from_version <= 1405061421 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //        }
        //        // Please add your future database scheme changes here
        //
        //


    }
}
