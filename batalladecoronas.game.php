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
            "dice_1" => 10,
            "dice_2" => 11,
        ));

        $this->crown = $this->getNew("module.common.deck");
        $this->crown->init("crown");

        $this->cross = $this->getNew("module.common.deck");
        $this->cross->init("sacredcross");

        $this->blacksmith = $this->getNew("module.common.deck");
        $this->blacksmith->init("blacksmith");

        $this->gems = $this->getNew("module.common.deck");
        $this->gems->init("gem");

        $this->gold = $this->getNew("module.common.deck");
        $this->gold->init("gold");
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

        $this->setGameStateInitialValue('dice_1', 0);
        $this->setGameStateInitialValue('dice_2', 0);

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

        foreach ($players as $player_id => $player) {
            $this->gems->pickCardsForLocation(3, "box", "power", $player_id);
            $this->gold->pickCardsForLocation(7, "box", "vault", $player_id);
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
        $result["dices"] = $this->getDices();
        $result["supply"] = $this->getSupply();
        $result["gems"] = $this->getGemsByLocation();
        $result["treasure"] = $this->getTreasure();

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

    function getDices()
    {
        return array(
            1 => $this->getGameStateValue("dice_1"),
            2 => $this->getGameStateValue("dice_2")
        );
    }

    function getSupply()
    {
        return array(
            "crown" => $this->crown->countCardsInLocation("supply") > 0,
            "cross" => $this->cross->countCardsInLocation("supply") > 0,
            "blacksmith" => $this->blacksmith->countCardsInLocation("supply") > 0
        );
    }

    function getGemsByLocation()
    {
        $gem_nbr = array();
        $players = $this->loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            $gem_nbr[$player_id]["power"] = $this->gems->countCardsInLocation("power", $player_id);
            $gem_nbr[$player_id]["treasure"] = $this->gems->countCardsInLocation("treasure", $player_id);
        }

        return $gem_nbr;
    }

    function getTreasure()
    {
        $treasure = array();
        $players = $this->loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            $treasure[$player_id] = $this->gold->countCardsInLocation("treasure", $player_id);
        }

        return $treasure;
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


    function rollDices()
    {
        $this->checkAction("rollDices");

        $dice_1 = bga_rand(1, 6);
        $dice_2 = bga_rand(1, 6);

        $this->setGameStateValue("dice_1", $dice_1);
        $this->setGameStateValue("dice_2", $dice_2);

        $player_name = $this->getActivePlayerName();

        $this->notifyAllPlayers(
            "dicesRoll",
            clienttranslate('${player_name} rolls the first dice. The result is ${result}'),
            array(
                "player_name" => $player_name,
                "dice" => 1,
                "result" => $dice_1
            )
        );

        $this->notifyAllPlayers(
            "dicesRoll",
            clienttranslate('${player_name} rolls the second dice. The result is ${result}'),
            array(
                "player_name" => $player_name,
                "dice" => 2,
                "result" => $dice_2,
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
