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
 * states.inc.php
 *
 * BatallaDeCoronas game states description
 *
 */

$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 2)
    ),

    2 => array(
        "name" => "diceRoll",
        "description" => clienttranslate('${actplayer} must roll the die to start a new turn'),
        "descriptionmyturn" => clienttranslate('${you} must roll the die to start a new turn'),
        "type" => "activeplayer",
        "possibleactions" => array("rollDice"),
        "transitions" => array("decisionPhase" => 3)
    ),

    3 =>  array(
        "name" => "decisionPhase",
        "description" => clienttranslate('${actplayer} must pick a die to activate a counselor. The other die shall generate gold'),
        "descriptionmyturn" => clienttranslate('${you} must pick a die to activate a counselor. The other die shall generate gold'),
        "type" => "activeplayer",
        "possibleactions" => array("decideDice"),
        "transitions" => array("chairPicking" => 31, "couselorActivaction" => 32)
    ),

    31 => array(
        "name" => "chairPicking",
        "description" => clienttranslate('${actplayer} must pick a counselor to occupy this chair'),
        "descriptionmyturn" => clienttranslate('${you} must pick a counselor to occupy this chair'),
        "type" => "activeplayer",
        "possibleactions" => array("pickChair"),
        "transitions" => array("counselorActivation" => 32),
    ),

    32 => array(
        "name" => "counselorActivation",
        "description" => clienttranslate('${actplayer} may activate the counselor'),
        "descriptionmyturn" => clienttranslate('${you} may activate the counselor'),
        "type" => "activeplayer",
        "possibleactions" => array("counselorActivation", "skip"),
        "transitions" => array("pickCounselor" => 33, "skip" => 4),
    ),

    33 => array(
        "name" => "counselorPicking",
        "description" => clienttranslate('${actplayer} activated the Noble and must now pick other counselor to activate'),
        "descriptionmyturn" => clienttranslate('${you} activated the Noble and must now pick other counselor to activate'),
        "type" => "activeplayer",
        "possibleactions" => array("pickCounselor"),
        "transitions" => array("pickCounselor" => 33, "purchase" => 4),
    ),

    4 => array(
        "name" => "purchasePhase",
        "description" => clienttranslate('${actplayer} may select an area to spend his gold with'),
        "descriptionmyturn" => clienttranslate('${you} may select an area to spend his gold with'),
        "type" => "activeplayer",
        "possibleactions" => array("purchaseShields", "purchaseSwords", "evolveDragon", "skip"),
        "transitions" => array("purchaseAgain" => 4, "battlePhase" => 5, "skip" => 5)
    ),

    5 => array(
        "name" => "battlePhase",
        "description" => clienttranslate('${actplayer} may start a battle'),
        "descriptionmyturn" => clienttranslate('${you} may start a battle'),
        "type" => "active",
        "possibleActions" => array("startBattle", "skip"),
        "transitions" => array("battle" => 51, "skip" => 2),
    ),

    51 => array(
        "name" => "battle",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stBattle",
        "transitions" => array("destroyShields" => 52, "diceRoll" => 2),
    ),

    52 => array(
        "name" => "destroyShields",
        "description" => clienttranslate('${actplayer} may pick how many swords shall be used in the attack'),
        "descriptionmyturn" => clienttranslate('${you} may pick how many swords shall be used in the attack'),
        "type" => "activeplayer",
        "possibleactions" => array("destroyShields", "skip"),
        "transitions" => array("diceRoll" => 2, "skip" => 2),
    ),

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);
