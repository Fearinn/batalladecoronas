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

$this->church_houses = array(
  "DOOR" => array(
    "label_tr" => clienttranslate("DOOR")
  ),
  "GOLDEN" => array(
    "label_tr" => clienttranslate("GOLDEN")
  ),
  "BLUE" => array(
    "label_tr" => clienttranslate("BLUE")
  ),
  "RED" => array(
    "label_tr" => clienttranslate("RED")
  )
);
