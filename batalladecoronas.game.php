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
        ));

        $this->crown = $this->getNew("module.common.deck");
        $this->crown->init("crown");

        $this->cross = $this->getNew("module.common.deck");
        $this->cross->init("sacredcross");

        $this->smith = $this->getNew("module.common.deck");
        $this->smith->init("smith");

        $this->council = $this->getNew("module.common.deck");
        $this->council->init("counselor");

        $this->gems = $this->getNew("module.common.deck");
        $this->gems->init("gem");

        $this->attack = $this->getNew("module.common.deck");
        $this->attack->init("attack");

        $this->defense = $this->getNew("module.common.deck");
        $this->defense->init("defense");

        $this->clergy = $this->getNew("module.common.deck");
        $this->clergy->init("clergy");

        $this->gold = $this->getNew("module.common.deck");
        $this->gold->init("gold");

        $this->dragon = $this->getNew("module.common.deck");
        $this->dragon->init("dragon");
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

        $this->crown->createCards(array(
            array("type" => "crown", "type_arg" => 0, "nbr" => 1)
        ), "supply");

        $this->cross->createCards(array(
            array("type" => "cross", "type_arg" => 0, "nbr" => 1)
        ), "supply");

        $this->smith->createCards(array(
            array("type" => "smith", "type_arg" => 0, "nbr" => 1)
        ), "supply");

        $this->gems->createCards(array(array("type" => "gem", "type_arg" => 0, "nbr" => 6)), "box");

        $this->gold->createCards(array(array("type" => "gold", "type_arg" => 0, "nbr" => 14)), "box");

        $this->attack->createCards(array(array("type" => "attack", "type_arg" => 0, "nbr" => 10)), "box");
        $this->defense->createCards(array(array("type" => "defense", "type_arg" => 0, "nbr" => 10)), "box");

        $this->clergy->createCards(array(array("type" => "clergy", "type_arg" => 0, "nbr" => 2)), "box");

        $this->dragon->createCards(array(array("type" => "dragon", "type_arg" => 0, "nbr" => 10)), "box");

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

            $this->moveCardsToLocation($this->gems, 3, "box", "power", null, $player_id);

            $this->moveCardsToLocation($this->gold, 7, "box", "unclaimed", null, $player_id);

            $this->moveCardsToLocation($this->attack, 5, "box", "unclaimed", null, $player_id);
            $this->moveCardsToLocation($this->attack, 2, "unclaimed", "attack", $player_id, $player_id);

            $this->moveCardsToLocation($this->defense, 5, "box", "unclaimed", null, $player_id);
            $this->moveCardsToLocation($this->defense, 2, "unclaimed", "defense", $player_id, $player_id);

            $this->moveCardsToLocation($this->clergy, 1, "box", 0, null, $player_id);

            $this->moveCardsToLocation($this->dragon, 5, "box", "unclaimed", null, $player_id);
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
        $result["churchHouses"] = $this->church_houses;

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
        // TODO: compute and return the game progression

        return 0;
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    //////////// 

    function moveCardsToLocation(
        $deck,
        $moved_nbr,
        $from_location,
        $to_location,
        $from_location_arg = null,
        $to_location_arg = null
    ) {
        $location_cards = $deck->getCardsInLocation($from_location, $from_location_arg);

        $moved_cards = array_slice($location_cards, 0, $moved_nbr, true);
        $moved_ids = array_keys($moved_cards);

        $deck->moveCards($moved_ids, $to_location, $to_location_arg);

        return $moved_cards;
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
        return array(
            "crown" => $this->crown->countCardInLocation("supply") > 0,
            "cross" => $this->cross->countCardInLocation("supply") > 0,
            "smith" => $this->smith->countCardInLocation("supply") > 0
        );
    }

    function getClaimedSupply()
    {
        $claimed_supply = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $claimed_supply[$player_id] = array(
                "crown" => $this->crown->countCardInLocation("crown", $player_id) > 0,
                "cross" => $this->cross->countCardInLocation("cross", $player_id) > 0,
                "smith" => $this->smith->countCardInLocation("smith", $player_id) > 0
            );
        }

        return $claimed_supply;
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
        $gem_nbr = array();
        $players = $this->loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            $gem_nbr[$player_id]["power"] = $this->gems->countCardInLocation("power", $player_id);
            $gem_nbr[$player_id]["treasure"] = $this->gems->countCardInLocation("treasure", $player_id);
        }

        return $gem_nbr;
    }

    function getAttack()
    {
        $attack = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $attack[$player_id] = $this->attack->countCardInLocation("attack", $player_id);
        }

        return $attack;
    }

    function getDefense()
    {
        $defense = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $defense[$player_id] = $this->defense->countCardInLocation("defense", $player_id);
        }

        return $defense;
    }

    function getChurch()
    {
        $church = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            foreach ($this->church_houses as $house_id => $house) {
                $house_cards = $this->clergy->getCardsInLocation($house_id, $player_id);
                $card = array_shift($house_cards);

                if ($card !== null) {
                    $church[$player_id] = $house_id;
                    break;
                }
            }
        }

        return $church;
    }

    function getTreasure()
    {
        $treasure = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $treasure[$player_id] = $this->gold->countCardInLocation("treasure", $player_id);
        }

        return $treasure;
    }

    function getDragon()
    {
        $dragon = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $dragon[$player_id] = $this->dragon->countCardInLocation("dragon", $player_id);
        }

        return $dragon;
    }

    function spendGold(int $gold_nbr, $player_id, bool $to_box = false): int
    {
        if ($gold_nbr <= 0) {
            return $this->getTreasure()[$player_id];
        }

        $prev_gold_nbr = $this->getTreasure()[$player_id];

        $spent_gold  = array();

        if ($to_box) {
            $spent_gold = $this->moveCardsToLocation($this->gold, $gold_nbr, "treasure", "box", $player_id, $player_id);
        } else {
            $spent_gold = $this->moveCardsToLocation($this->gold, $gold_nbr, "treasure", "unclaimed", $player_id, $player_id);
        }

        $this->notifyAllPlayers(
            "generateGold",
            clienttranslate('${player_name} spends ${spentGold} of gold. The total is ${totalGold}'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevGold" => $prev_gold_nbr,
                "spentGold" => count($spent_gold),
                "totalGold" => $prev_gold_nbr - count($spent_gold)
            )
        );

        return $this->getTreasure()[$player_id];
    }

    function generateGold(int $gold_nbr, $player_id): int
    {
        if ($gold_nbr <= 0) {
            return $this->getTreasure()[$player_id];
        }

        $prev_gold_nbr = $this->getTreasure()[$player_id];
        $generated_gold = $this->moveCardsToLocation($this->gold, $gold_nbr, "unclaimed", "treasure", $player_id, $player_id);

        $this->notifyAllPlayers(
            "generateGold",
            clienttranslate('${player_name} generates ${generatedGold} of gold. The total is ${totalGold}'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "prevGold" => $prev_gold_nbr,
                "generatedGold" => count($generated_gold),
                "totalGold" => $prev_gold_nbr + count($generated_gold)
            )
        );

        return $this->getTreasure()[$player_id];
    }

    function increaseAttack(int $sword_nbr, $player_id): int
    {
        $prev_swords = $this->getAttack()[$player_id];

        $this->moveCardsToLocation($this->attack, $sword_nbr, "unclaimed", "attack", $player_id, $player_id);

        $total_swords = $this->getAttack()[$player_id];

        if ($prev_swords != $total_swords) {
            $this->notifyAllPlayers(
                "increaseAttack",
                clienttranslate('${player_name} gets ${newSwords} sword(s). The total is ${totalSwords}'),
                array(
                    "player_id" => $player_id,
                    "player_name" => $this->getPlayerNameById($player_id),
                    "prevSwords" => $prev_swords,
                    "newSwords" => $sword_nbr,
                    "totalSwords" => $total_swords,
                    "attack" => $this->getAttack()
                )
            );
        }

        return $total_swords;
    }

    function increaseDefense(int $shield_nbr, $player_id): int
    {
        $prev_shields = $this->getDefense()[$player_id];

        $this->moveCardsToLocation($this->defense, $shield_nbr, "unclaimed", "defense", $player_id, $player_id);

        $total_shields = $this->getDefense()[$player_id];

        if ($prev_shields != $total_shields) {
            $this->notifyAllPlayers(
                "increaseDefense",
                clienttranslate('${player_name} gets ${newShields} sword(s). The total is ${totalShields}'),
                array(
                    "player_id" => $player_id,
                    "player_name" => $this->getPlayerNameById($player_id),
                    "prevShields" => $prev_shields,
                    "newShields" => $shield_nbr,
                    "totalShields" => $total_shields,
                    "defense" => $this->getDefense()
                )
            );
        }

        return $total_shields;
    }

    function moveClergy(int $house_id, $player_id): void
    {
        $prev_house = $this->getChurch()[$player_id];

        if ($prev_house == $house_id) {
            throw new BgaUserException($this->_("You must move the clergy to other house"));
        }

        $house = $this->church_houses[$house_id];

        $this->notifyAllPlayers(
            "moveClergy",
            clienttranslate('${player_name} moves the clergy to the ${new_house_tr} square'),
            array(
                "i18n" => array("house_tr"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "new_house_tr" => $house["label_tr"],
                "newHouse" => $house_id,
                "prevHouse" => $prev_house,
                "church" => $this->getChurch()
            )
        );

        $this->clergy->moveAllCardsInLocation($prev_house, $house_id, $player_id, $player_id);
    }

    function levelUpDragon(int $level_nbr, $player_id): int
    {
        $this->moveCardsToLocation($this->dragon, $level_nbr, "unclaimed", "dragon", $player_id, $player_id);

        $dragon = $this->getDragon();

        $total_level = $this->getDragon()[$player_id];

        $this->notifyAllPlayers(
            "levelUpDragon",
            clienttranslate('${player_name} levels up the dragon. The current level is ${totalLevel}'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "totalLevel" => $total_level,
                "dragon" => $dragon
            ),
        );

        return $total_level;
    }

    function claimCrown($player_id): void
    {
        if ($this->crown->countCardInLocation("crown", $player_id) == 1) {
            throw new BgaVisibleSystemException("The crown is already in your castle");
        }

        $other_player_id = $this->getPlayerAfter($player_id);

        $is_owned = $this->crown->countCardInLocation("crown", $other_player_id) == 1;

        $this->crown->moveCard(1, "crown", $player_id);

        $this->notifyAllPlayers(
            "claimCrown",
            clienttranslate('${player_name} obtains the Crown token'),
            array(
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
        if ($this->cross->countCardInLocation("cross", $player_id) == 1) {
            throw new BgaVisibleSystemException("The cross is already in your castle");
        }

        $other_player_id = $this->getPlayerAfter($player_id);

        $is_owned = $this->cross->countCardInLocation("cross", $other_player_id) == 1;

        $this->cross->moveCard(1, "cross", $player_id);

        $this->notifyAllPlayers(
            "claimCross",
            clienttranslate('${player_name} obtains the Cross token'),
            array(
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
        if ($this->smith->countCardInLocation("smith", $player_id) == 1) {
            throw new BgaVisibleSystemException("The smith is already in your castle");
        }

        $other_player_id = $this->getPlayerAfter($player_id);

        $is_owned = $this->smith->countCardInLocation("smith", $other_player_id) == 1;

        $this->smith->moveCard(1, "smith", $player_id);

        $this->notifyAllPlayers(
            "claimSmith",
            clienttranslate('${player_name} claims the Smith token'),
            array(
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

        $this->moveCardsToLocation($this->gems, 1, "power", "treasure", $other_player_id, $player_id);

        $total_gems = $this->gems->countCardInLocation("treasure", $player_id);

        $gold_nbr = $this->getTreasure()[$player_id];
        $reduce_gold = $gold_nbr >= (8 - $total_gems);

        if ($reduce_gold) {
            $this->spendGold(1, $player_id, true);
        }

        $this->notifyAllPlayers(
            "claimGem",
            clienttranslate('${player_name} claims a gem. The total is ${totalGems}'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "other_player_id" => $other_player_id,
                "totalGems" => $total_gems,
                "gemsByLocations" => $this->getGemsByLocation(),
                "reduceGold" => $reduce_gold,
                "treasure" => $this->getTreasure()
            )
        );

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
                "counselor_name" => clienttranslate("Priest")
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

    function cancelActivation()
    {
        $this->checkAction("cancelActivation");

        $player_id = $this->getActivePlayerId();

        $counselor_id = $this->getGameStateValue("active_counselor");

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
