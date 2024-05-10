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
        ));

        $this->crown = $this->getNew("module.common.deck");
        $this->crown->init("crown");

        $this->cross = $this->getNew("module.common.deck");
        $this->cross->init("sacredcross");

        $this->blacksmith = $this->getNew("module.common.deck");
        $this->blacksmith->init("blacksmith");

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

        $this->crown->createCards(array(
            array("type" => "crown", "type_arg" => 0, "nbr" => 1)
        ), "supply");

        $this->cross->createCards(array(
            array("type" => "cross", "type_arg" => 0, "nbr" => 1)
        ), "supply");

        $this->blacksmith->createCards(array(
            array("type" => "blacksmith", "type_arg" => 0, "nbr" => 1)
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
                    "type" => $counselor["name"],
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
            $this->moveCardsToLocation($this->attack, 2, "unclaimed", "attack", null, $player_id);

            $this->moveCardsToLocation($this->defense, 5, "box", "unclaimed", null, $player_id);
            $this->moveCardsToLocation($this->defense, 2, "unclaimed", "defense", null, $player_id);

            $this->moveCardsToLocation($this->clergy, 1, "box", "DOOR", null, $player_id);

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
        $result["players"] = $this->getCollectionFromDb($sql);
        $result["counselorsInfo"] = $this->counselors_info;
        $result["dice"] = $this->getDice();
        $result["supply"] = $this->getSupply();
        $result["inactiveCouncil"] = $this->getInactiveCouncil();
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
            "blacksmith" => $this->blacksmith->countCardInLocation("supply") > 0
        );
    }

    function getInactiveCouncil()
    {
        $players = $this->loadPlayersBasicInfos();

        $council = array();

        foreach ($players as $player_id => $player) {
            $council[$player_id] = array();
            $location_cards = $this->council->getCardsInLocation("inactive", $player_id);

            foreach ($location_cards as $card) {
                $counselor_id = $card["type_arg"];
                $council[$player_id][$counselor_id] = $card;
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
            foreach ($this->church_houses as $house_label => $house) {
                $house_cards = $this->clergy->getCardsInLocation($house_label, $player_id);
                $card = array_shift($house_cards);

                if ($card !== null) {
                    $church[$player_id] = $house_label;
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

        foreach ($players as $player_id => $player_id) {
            $dragon[$player_id] = $this->dragon->countCardInLocation("dragon", $player_id);
        }

        return $dragon;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    //////////// 

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////


    function rollDice()
    {
        $this->checkAction("rollDice");

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

        $this->gamestate->nextState("decisionPhase");
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
