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
 * material.inc.php
 *
 * BatallaDeCoronas game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */
$this->counselors_info = array(
  1 => array(
    "name" => clienttranslate("Militia Commander"),
    "color" => "blue",
    "spritePos" => 0,
    "description" => clienttranslate("Militia Commander: obtains 1 shield/sword"),
  ),
  2 => array(
    "name" => clienttranslate("Master of Coin"),
    "color" => "yellow",
    "spritePos" => 1,
    "description" => clienttranslate("Master of Coin: generates 3 of gold")
  ),
  3 => array(
    "name" => clienttranslate("Sorcerer"),
    "color" => "red",
    "spritePos" => 2,
    "description" => clienttranslate("Sorcerer: levels up the dragon")
  ),
  4 => array(
    "name" => clienttranslate("Noble"),
    "color" => "purple",
    "spritePos" => 3,
    "description" => clienttranslate("Noble: activates other counselor (the copied counselor must be in a seat)")
  ),
  5 => array(
    "name" => clienttranslate("Smith"),
    "color" => "black",
    "spritePos" => 4,
    "description" => clienttranslate("Smith: obtains the Smith token")
  ),
  6 => array(
    "name" => clienttranslate("Priest"),
    "color" => "white",
    "spritePos" => 5,
    "description" => clienttranslate("Priest: moves the clergy")
  )
);

$this->tokens_info = array(
  1 => array(
    "label" => "crown",
    "label_tr" => clienttranslate("Crown"),
    "description" => clienttranslate("Crown token: generates 3 of gold"),
  ),
  2 => array(
    "label" => "cross",
    "label_tr" => clienttranslate("Cross"),
    "description" => clienttranslate("Cross token: moves the clergy")
  ),
  3 => array(
    "label" => "smith",
    "label_tr" => clienttranslate("Smith"),
    "description" => clienttranslate("Smith token: obtains an extra shield/sword for free")
  ),
);

$this->church_squares = array(
  0 => array(
    "label" => "DOOR",
    "label_tr" => clienttranslate("door")
  ),
  1 => array(
    "label" => "GOLDEN",
    "label_tr" => clienttranslate("golden")
  ),
  2 => array(
    "label" => "BLUE",
    "label_tr" => clienttranslate("blue")
  ),
  3 => array(
    "label" => "RED",
    "label_tr" => clienttranslate("red")
  )
);

$this->dragon_prices = array(
  1 => 2,
  2 => 3,
  3 => 4,
  4 => 5,
  5 => 6
);
