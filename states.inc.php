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
        "description" => clienttranslate('${actplayer} must roll the dice to start a new turn'),
        "descriptionmyturn" => clienttranslate('${you} must roll the dice to start a new turn'),
        "type" => "activeplayer",
        "possibleactions" => array("rollDice", "activateToken"),
        "transitions" => array(
            "decisionPhase" => 3,
            "counselorVesting" => 31,
            "couselorActivaction" => 32,
        ),
    ),

    3 =>  array(
        "name" => "decisionPhase",
        "description" => clienttranslate('${actplayer} must pick a die to activate a counselor. The other die shall generate gold'),
        "descriptionmyturn" => clienttranslate('${you} must pick a die to activate a counselor. The other die shall generate gold'),
        "type" => "activeplayer",
        "possibleactions" => array("decideDice", "activateToken"),
        "transitions" => array(
            "counselorVesting" => 31,
            "couselorActivaction" => 32,
            "buyingPhase" => 4
        ),
    ),

    31 => array(
        "name" => "counselorVesting",
        "description" => clienttranslate('${actplayer} must pick a counselor to occupy the chair ${chair}'),
        "descriptionmyturn" => clienttranslate('${you} must pick a counselor to occupy the chair ${chair}'),
        "type" => "activeplayer",
        "args" => "argCounselorVesting",
        "possibleactions" => array("vestCounselor"),
        "transitions" => array("counselorActivation" => 32, "buyingPhase" => 4),
    ),

    32 => array(
        "name" => "counselorActivation",
        "description" => clienttranslate('${actplayer} may activate the ${counselor_name}'),
        "descriptionmyturn" => clienttranslate('${you} may activate the ${counselor_name}'),
        "type" => "activeplayer",
        "args" => "argCounselorActivation",
        "possibleactions" => array("activateCounselor", "skipActivation", "activateToken"),
        "transitions" => array(
            "nobleActivation" => 33,
            "commanderActivation" => 34,
            "priestActivation"  => 35,
            "buyingPhase" => 4,
            "skip" => 4,
        ),
    ),

    33 => array(
        "name" => "nobleActivation",
        "description" => clienttranslate('${actplayer} activated the Noble and must now pick other counselor to activate'),
        "descriptionmyturn" => clienttranslate('${you} activated the Noble and must now pick other counselor to activate'),
        "type" => "activeplayer",
        "possibleactions" => array("activateNoble", "cancelActivation"),
        "transitions" => array("buyingPhase" => 4, "commanderActivation" => 34, "cancel" => 32),
    ),

    34 => array(
        "name" => "commanderActivation",
        "description" => clienttranslate('${actplayer} activated the Commander and must now pick a militia to improve'),
        "descriptionmyturn" => clienttranslate('${you} activated the Commander and must now pick a militia to improve'),
        "type" => "activeplayer",
        "possibleactions" => array("activateCommander", "cancelActivation"),
        "transitions" => array("buyingPhase" => 4, "cancel" => 32),
    ),

    35 => array(
        "name" => "priestActivation",
        "description" => clienttranslate('${actplayer} activated the Priest and must now pick a square to move the Clergy to'),
        "descriptionmyturn" => clienttranslate('${you} activated the Priest and must now pick a square to move the Clergy to'),
        "type" => "activeplayer",
        "possibleactions" => array("activatePriest", "cancelActivation"),
        "transitions" => array("buyingPhase" => 4, "cancel" => 32),
    ),

    4 => array(
        "name" => "buyingPhase",
        "description" => clienttranslate('${actplayer} may select an area to spend his gold with'),
        "descriptionmyturn" => clienttranslate('${you} may select an area to spend your gold with'),
        "type" => "activeplayer",
        "args" => "argBuyingPhase",
        "possibleactions" => array("buyArea", "skipBuying", "activateToken"),
        "transitions" => array("buyAgain" => 4, "battlePhase" => 5, "skip" => 5)
    ),

    5 => array(
        "name" => "tokenActivation",
        "description" => clienttranslate('${actplayer} may use a token'),
        "descriptionmyturn" => clienttranslate('${you} may use a token'),
        "type" => "activeplayer",
        "possibleactions" => array("activateToken", "skipTokenActivation"),
        "transitions" => array("tokenUsed" => 5, "battlePhase" => 6, "skip" => 6)
    ),

    51 => array(
        "name" => "crossTokenActivation",
        "description" => clienttranslate('${actplayer} may pick a square to move the Clergy to with the Cross token'),
        "descriptionmyturn" => clienttranslate('${you} may pick a square to move the Clergy to with the Cross token'),
        "type" => "activeplayer",
        "possibleactions" => array("activateCrossToken", "cancelTokenActivation"),
        "transitions" => array()
    ),

    52 => array(
        "name" => "smithTokenActivation",
        "description" => clienttranslate('${actplayer} may pick a militia to improve with the Smith token'),
        "descriptionmyturn" => clienttranslate('${you} may pick a militia to improve with the Smith token'),
        "type" => "activeplayer",
        "possibleactions" => array("activateSmithToken", "cancelTokenActivation"),
        "transitions" => array()
    ),

    6 => array(
        "name" => "battlePhase",
        "description" => clienttranslate('${actplayer} may start a battle'),
        "descriptionmyturn" => clienttranslate('${you} may start a battle'),
        "type" => "activeplayer",
        "possibleActions" => array("startBattle", "skip"),
        "transitions" => array("battle" => 61, "skip" => 2),
    ),

    61 => array(
        "name" => "battle",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stBattle",
        "transitions" => array("destroyShields" => 62, "diceRoll" => 2),
    ),

    62 => array(
        "name" => "shieldDestruction",
        "description" => clienttranslate('${actplayer} may pick how many swords shall be used in the attack'),
        "descriptionmyturn" => clienttranslate('${you} may pick how many swords shall be used in the attack'),
        "type" => "activeplayer",
        "possibleactions" => array("shieldDestruction", "skip"),
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
