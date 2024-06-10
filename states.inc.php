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
        "description" => clienttranslate('${actplayer} must roll the dice to start the match'),
        "descriptionmyturn" => clienttranslate('${you} must roll the dice to start the match'),
        "type" => "activeplayer",
        "possibleactions" => array("rollDice"),
        "transitions" => array(
            "decisionPhase" => 3,
            "counselorVesting" => 31,
            "couselorActivaction" => 32,
            "buyingPhase" => 4,
            "preBattle" => 5,
        ),
    ),

    21 => array(
        "name" => "autoDiceRoll",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stAutoDiceRoll",
        "transitions" => array(
            "decisionPhase" => 3,
            "counselorVesting" => 31,
            "counselorActivation" => 32,
            "buyingPhase" => 4,
            "preBattle" => 5,
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
            "counselorActivation" => 32,
            "buyingPhase" => 4,
            "preBattle" => 5,
        ),
    ),

    31 => array(
        "name" => "counselorVesting",
        "description" => clienttranslate('${actplayer} must pick a counselor to occupy the chair ${chair}'),
        "descriptionmyturn" => clienttranslate('${you} must pick a counselor to occupy the chair ${chair}'),
        "type" => "activeplayer",
        "args" => "argCounselorVesting",
        "possibleactions" => array("vestCounselor"),
        "transitions" => array("counselorActivation" => 32, "buyingPhase" => 4, "preBattle" => 5),
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
            "preBattle" => 5,
            "skip" => 4,
        ),
    ),

    33 => array(
        "name" => "nobleActivation",
        "description" => clienttranslate('${actplayer} activated the Noble and must now pick other counselor to activate'),
        "descriptionmyturn" => clienttranslate('${you} activated the Noble and must now pick other counselor to activate'),
        "type" => "activeplayer",
        "possibleactions" => array("activateNoble", "cancelActivation"),
        "transitions" => array("commanderActivation" => 34, "buyingPhase" => 4, "preBattle" => 5, "cancel" => 32),
    ),

    34 => array(
        "name" => "commanderActivation",
        "description" => clienttranslate('${actplayer} activated the Commander and must now pick a militia to improve'),
        "descriptionmyturn" => clienttranslate('${you} activated the Commander and must now pick a militia to improve'),
        "type" => "activeplayer",
        "possibleactions" => array("activateCommander", "cancelActivation"),
        "transitions" => array("buyingPhase" => 4, "preBattle" => 5, "cancel" => 32),
    ),

    35 => array(
        "name" => "priestActivation",
        "description" => clienttranslate('${actplayer} activated the Priest and must now pick a square to move the Clergy to'),
        "descriptionmyturn" => clienttranslate('${you} activated the Priest and must now pick a square to move the Clergy to'),
        "type" => "activeplayer",
        "possibleactions" => array("activatePriest", "cancelActivation"),
        "transitions" => array("buyingPhase" => 4, "preBattle" => 5, "cancel" => 32),
    ),

    4 => array(
        "name" => "buyingPhase",
        "description" => clienttranslate('${actplayer} may select an area to spend his gold with'),
        "descriptionmyturn" => clienttranslate('${you} may select an area to spend your gold with'),
        "type" => "activeplayer",
        "args" => "argBuyingPhase",
        "possibleactions" => array("buyArea", "skipBuying", "activateToken", "activateSmithToken"),
        "transitions" => array("buyAgain" => 4, "smithTokenActivation" => 42, "preBattle" => 5, "skip" => 5)
    ),

    42 => array(
        "name" => "smithTokenActivation",
        "description" => clienttranslate('${actplayer} may obtain an extra equipment for free with the Smith token'),
        "descriptionmyturn" => clienttranslate('Do ${you} wish to obtain an extra equipment for free with the Smith token?'),
        "type" => "activeplayer",
        "possibleactions" => array("activateSmithToken", "skipSmithToken"),
        "transitions" => array("buyAgain" => 4, "preBattle" => 5, "battlePhase" => 6, "skip" => 5)
    ),

    5 => array(
        "name" => "preBattle",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stPreBattle",
        "transitions" => array("battlePhase" => 6, "betweenTurns" => 7)
    ),

    6 => array(
        "name" => "battlePhase",
        "description" => clienttranslate('${actplayer} may start a battle'),
        "descriptionmyturn" => clienttranslate('${you} may start a battle'),
        "type" => "activeplayer",
        "possibleactions" => array("startBattle", "skipBattle", "activateToken"),
        "transitions" => array("battle" => 61, "skip" => 7),
    ),

    61 => array(
        "name" => "battle",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stBattle",
        "transitions" => array(
            "resultDispute" => 62,
            "shieldDestruction" => 64,
            "disputeToken" => 65,
            "betweenTurns" => 7
        ),
    ),

    62 => array(
        "name" => "resultDispute",
        "description" => clienttranslate('${actplayer} may spend gold to reroll the dice'),
        "descriptionmyturn" => clienttranslate('${you} may spend gold reroll the dice'),
        "type" => "activeplayer",
        "possibleactions" => array("disputeResult", "skipDispute", "activateToken"),
        "transitions" => array("betweenDisputes" => 63, "skip" => 63),
    ),

    63 => array(
        "name" => "betweenDisputes",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stBetweenDisputes",
        "transitions" => array(
            "resultDispute" => 62,
            "betweenDisputes" => 63,
            "shieldDestruction" => 64,
            "disputeToken" => 65,
            "responseToCrown" => 66,
            "betweenTurns" => 7
        ),
    ),

    64 => array(
        "name" => "shieldDestruction",
        "description" => clienttranslate('${actplayer} may destroy up to ${damagedShields} shield(s) of ${player_name}'),
        "descriptionmyturn" => clienttranslate('${you} may destroy up to ${damagedShields} shield(s) of ${player_name}'),
        "type" => "activeplayer",
        "args" => "argShieldDestruction",
        "possibleactions" => array("destroyShields", "skipDestruction"),
        "transitions" => array("betweenTurns" => 7, "skip" => 7, "gameEnd" => 99),
    ),

    65 => array(
        "name" => "disputeToken",
        "description" => clienttranslate('${actplayer} may activate a token before you have the chance to reroll the die'),
        "descriptionmyturn" => clienttranslate('${you} may activate a token before your opponent has the chance to reroll the die'),
        "type" => "activeplayer",
        "possibleactions" => array("activateToken", "skipToken"),
        "transitions" => array("crossTokenActivation" => 67, "skip" => 63)
    ),

    66 => array(
        "name" => "responseToCrown",
        "description" => clienttranslate('${actplayer} may activate the Cross in response to the Crown used by you'),
        "descriptionmyturn" => clienttranslate('${you} may activate the Cross in response to the Crown used by your opponent'),
        "type" => "activeplayer",
        "possibleactions" => array("activateToken", "skipToken"),
        "transitions" => array("crossTokenActivation" => 67, "skip" => 63)
    ),

    67 => array(
        "name" => "crossTokenActivation",
        "description" => clienttranslate('${actplayer} may pick a square to move the Clergy to with the Cross token'),
        "descriptionmyturn" => clienttranslate('${you} may pick a square to move the Clergy to with the Cross token'),
        "type" => "activeplayer",
        "possibleactions" => array("activateCrossToken", "cancelToken"),
        "transitions" => array(
            "betweenDisputes" => 63
        )
    ),

    7 => array(
        "name" => "betweenTurns",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stBetweenTurns",
        "transitions" => array("nextTurn" => 21),
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
