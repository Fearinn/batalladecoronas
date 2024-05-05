<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * BatallaDeCoronas implementation : © Matheus Gomes matheusgomesforwork@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * batalladecoronas.action.php
 *
 * BatallaDeCoronas main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/batalladecoronas/batalladecoronas/myAction.html", ...)
 *
 */


class action_batalladecoronas extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if ($this->isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = $this->getArg("table", AT_posint, true);
    } else {
      $this->view = "batalladecoronas_batalladecoronas";
      $this->trace("Complete reinitialization of board game");
    }
  }

  public function rollDice()
  {
    $this->setAjaxMode();
    $this->game->rollDice();
    $this->ajaxResponse();
  }
}
