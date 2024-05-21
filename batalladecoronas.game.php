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
            "highest_gems" => 80
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
        $this->setGameStateInitialValue("highest_gems", 0);

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

    function getPlayerSupply(string $supply, $player_id): int
    {
        return !!$this->getUniqueValueFromDB("SELECT $supply from player WHERE player_id='$player_id'");
    }

    function setPlayerSupply(string $supply, int $value, $player_id): void
    {
        $this->DbQuery("UPDATE player SET $supply=$value WHERE player_id='$player_id'");
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

        foreach ($this->tokens_info as $token_info) {
            $label = $token_info["label"];
            $supply[$label] = true;

            foreach ($players as $player_id => $player) {
                $owned = $this->getPlayerSupply($label, $player_id);

                if ($owned) {
                    $supply[$label] = false;
                }
            }
        }

        return $supply;
    }

    function getClaimedSupply()
    {
        $supply = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            foreach ($this->tokens_info as $token_info) {
                $label = $token_info["label"];
                $supply[$player_id][$label] = false;

                $owned = $this->getUniqueValueFromDB("SELECT $label from player WHERE player_id='$player_id'");

                if ($owned) {
                    $supply[$player_id][$label] = true;
                }
            }
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

    function negativateGold($player_id): void
    {
        $prev_gold = $this->getTreasure()[$player_id];

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

        $prev_gold = $this->getTreasure()[$player_id];

        if ($value > $prev_gold) {
            throw new BgaUserException($this->_("You don't have the gold required by this action"));
        }

        $total_gold = $prev_gold - $value;

        $this->setPlayerGold($total_gold, $player_id);

        $this->notifyAllPlayers(
            "generateGold",
            $message ? clienttranslate('${player_name} spends ${spentGold} of gold. The total is ${totalGold}') : "",
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

        $prev_gold = $this->getTreasure()[$player_id];

        $max_gold = $this->getPlayerMaxGold($player_id);

        if ($prev_gold == $max_gold) {
            throw new BgaUserException($this->_("You can't generate more gold"));
        }

        $total_gold = $prev_gold + $value;

        if ($total_gold > $max_gold) {
            $total_gold = $max_gold;
        }

        $this->setPlayerGold($total_gold, $player_id);

        $this->notifyAllPlayers(
            "generateGold",
            clienttranslate('${player_name} generates ${generatedGold} of gold. The total is ${totalGold}'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevGold" => $prev_gold,
                "generatedGold" => $value,
                "totalGold" => $total_gold
            )
        );

        return $total_gold;
    }

    function increaseAttack(int $value, $player_id): int
    {
        $prev_swords = $this->getAttack()[$player_id];

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
            clienttranslate('${player_name} gets ${newSwords} sword(s). The total is ${totalSwords}'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevSwords" => $prev_swords,
                "newSwords" => $value,
                "totalSwords" => $total_swords,
                "attack" => $this->getAttack()
            )
        );

        return $total_swords;
    }

    function decreaseAttack(int $value, $player_id, $message = false)
    {
        $prev_swords = $this->getAttack()[$player_id];

        $total_swords = $prev_swords - $value;

        if ($value > $prev_swords) {
            $total_swords = 0;
        }

        $this->setPlayerAttack($total_swords, $player_id);

        $this->notifyAllPlayers(
            "increaseAttack",
            $message ? clienttranslate('${newSwords} swords of ${player_name} are destroyed') : "",
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevSwords" => $prev_swords,
                "newSwords" => $value,
                "totalSwords" => $total_swords,
                "attack" => $this->getAttack()
            )
        );

        return $total_swords;
    }

    function increaseDefense(int $value, $player_id): int
    {
        $prev_shields = $this->getDefense()[$player_id];

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
            clienttranslate('${player_name} gets ${newShields} shield(s). The total is ${totalShields}'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevShields" => $prev_shields,
                "newShields" => $value,
                "totalShields" => $total_shields,
                "attack" => $this->getDefense()
            )
        );

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
        $prev_square = $this->getChurch()[$player_id];

        if ($prev_square == $square_id) {
            throw new BgaUserException($this->_("You must move the clergy to other square"));
        }

        $this->setPlayerClergy($square_id, $player_id);

        $square = $this->church_squares[$square_id];

        $this->notifyAllPlayers(
            "moveClergy",
            clienttranslate('${player_name} moves the Clergy to the ${new_square_tr} square and activates its effect'),
            array(
                "i18n" => array("square_tr"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "new_square_tr" => $square["label_tr"],
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
        $prev_level = $this->getDragon()[$player_id];

        if ($prev_level == 6) {
            throw new BgaUserException($this->_("The level of the dragon can't be further increased"));
        }

        $total_level = $prev_level + $value;

        if ($total_level > 6) {
            $total_level = 6;
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

    function levelDownDragon(int $value, $player_id): int
    {
        $prev_level = $this->getDragon()[$player_id];

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
        if ($this->getPlayerSupply("crown", $player_id)) {
            throw new BgaVisibleSystemException("The crown is already in your castle");
        }

        $other_player_id = $this->getPlayerAfter($player_id);

        $is_owned = $this->getPlayerSupply("crown", $other_player_id);

        $this->setPlayerSupply("crown", 1, $player_id);
        $this->setPlayerSupply("crown", 0, $other_player_id);

        $this->notifyAllPlayers(
            "claimCrown",
            clienttranslate('${player_name} obtains the ${label_tr} token'),
            array(
                "i18n" => array("label_tr"),
                "label_tr" => $this->tokens_info[1]["label_tr"],
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "other_player_id" => $other_player_id,
                "isOwned" => $is_owned,
                "supply" => $this->getSupply()
            )
        );
    }

    function claimCross($player_id): void
    {
        if ($this->getPlayerSupply("sacredcross", $player_id)) {
            throw new BgaVisibleSystemException("The Cross is already in your castle");
        }

        $other_player_id = $this->getPlayerAfter($player_id);

        $is_owned = $this->getPlayerSupply("sacredcross", $other_player_id);

        $this->setPlayerSupply("sacredcross", 1, $player_id);
        $this->setPlayerSupply("sacredcross", 0, $other_player_id);

        $this->notifyAllPlayers(
            "claimCross",
            clienttranslate('${player_name} obtains the ${token_label} token'),
            array(
                "i18n" => array("token_label"),
                "token_label" => $this->tokens_info[2]["label_tr"],
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "other_player_id" => $other_player_id,
                "isOwned" => $is_owned,
                "supply" => $this->getSupply()
            )
        );
    }

    function claimSmith($player_id): void
    {
        if ($this->getPlayerSupply("smith", $player_id)) {
            throw new BgaVisibleSystemException("The Smith is already in your castle");
        }

        $other_player_id = $this->getPlayerAfter($player_id);

        $is_owned = $this->getPlayerSupply("smith", $other_player_id);

        $this->setPlayerSupply("smith", 1, $player_id);
        $this->setPlayerSupply("smith", 0, $other_player_id);

        $this->notifyAllPlayers(
            "claimSmith",
            clienttranslate('${player_name} obtains the ${token_label} token'),
            array(
                "i18n" => array("token_label"),
                "token_label" => $this->tokens_info[3]["label_tr"],
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "other_player_id" => $other_player_id,
                "isOwned" => $is_owned,
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
            clienttranslate('${player_name} gets a gem. The total is ${totalGems}'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "other_player_id" => $other_player_id,
                "totalGems" => $total_gems,
                "gemsByLocations" => $this->getGemsByLocation(),
            )
        );

        $gold = $this->getPlayerGold($player_id);
        $max_gold = $this->getPlayerMaxGold($player_id);

        if ($gold >= $max_gold) {
            $this->spendGold(1, $player_id);
        }

        return $total_gems;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Counselor actions
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

    function rollDice()
    {
        $this->checkAction("rollDice");

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
                clienttranslate('The dice have the same result. Decision phase skipped'),
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

        $this->gamestate->nextState("buyingPhase");
    }

    function activateNoble($card_id)
    {
        $this->checkAction("activateNoble");

        $player_id = $this->getActivePlayerId();

        $counselor = $this->council->getCard($card_id);

        $active_counselor = $counselor["type_arg"];
        $chair = $counselor["location_arg"];

        if ($active_counselor == 4) {
            throw new BgaUserException($this->_("You can't activate the Noble with its own effect"));
        }

        $this->notifyAllPlayers(
            "activateNoble",
            clienttranslate('${player_name} activates the Noble. The effect of other counselor is activated'),
            array("player_name" => $this->getPlayerNameById($player_id))
        );

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

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

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
