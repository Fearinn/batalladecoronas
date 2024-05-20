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
    "spritePos" => 0
  ),
  2 => array(
    "name" => clienttranslate("Master of Coin"),
    "color" => "yellow",
    "spritePos" => 1
  ),
  3 => array(
    "name" => clienttranslate("Sorcerer"),
    "color" => "red",
    "spritePos" => 2
  ),
  4 => array(
    "name" => clienttranslate("Noble"),
    "color" => "purple",
    "spritePos" => 3
  ),
  5 => array(
    "name" => clienttranslate("Smith"),
    "color" => "black",
    "spritePos" => 4
  ),
  6 => array(
    "name" => clienttranslate("Priest"),
    "color" => "white",
    "spritePos" => 5
  )
);

$this->tokens_info = array(
  1 => array(
    "label" => "crown",
    "label_tr" => clienttranslate("Crown")
  ),
  2 => array(
    "label" => "sacredcross",
    "label_tr" => clienttranslate("Cross")
  ),
  3 => array(
    "label" => "smith",
    "label_tr" => clienttranslate("Smith")
  ),
);

$this->church_squares = array(
  0 => array(
    "label" => "DOOR",
    "label_tr" => clienttranslate("DOOR")
  ),
  1 => array(
    "label" => "GOLDEN",
    "label_tr" => clienttranslate("GOLDEN")
  ),
  2 => array(
    "label" => "BLUE",
    "label_tr" => clienttranslate("BLUE")
  ),
  3 => array(
    "label" => "RED",
    "label_tr" => clienttranslate("RED")
  )
);
