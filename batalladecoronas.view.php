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
 * batalladecoronas.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in batalladecoronas_batalladecoronas.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_batalladecoronas_batalladecoronas extends game_view
{
    protected function getGameName()
    {
        return "batalladecoronas";
    }

    function build_page($viewArgs)
    {
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count($players);

        $template = $this->getGameName() . "_" . $this->getGameName();

        global $g_user;
        $current_player_id = $g_user->get_id();

        $this->page->begin_block($template, "mycastleblock");
        if (in_array($current_player_id, array_keys($players))) {
            $this->page->insert_block("mycastleblock", array(
                "MY_ID" => $current_player_id,
                "MY_COLOR" => $players[$current_player_id]["player_color"],
            ));
        }

        $this->page->begin_block($template, "othercastleblock");
        foreach ($players as $player_id => $player) {
            if ($player_id != $current_player_id) {
                $this->page->insert_block("othercastleblock", array(
                    "PLAYER_NAME" => $player["player_name"],
                    "PLAYER_ID" => $player_id,
                    "PLAYER_COLOR" => $player["player_color"],
                ));
            }
        }

        $this->tpl["YOUR CASTLE"] = $this->_("Your castle");

        /*********** Do not change anything below this line  ************/
    }
}
