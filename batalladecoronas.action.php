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

  public function decideDice()
  {
    $this->setAjaxMode();
    $die = $this->getArg("die", AT_enum, true, null, array(1, 2));
    $this->game->decideDice($die);
    $this->ajaxResponse();
  }

  public function vestCounselor()
  {
    $this->setAjaxMode();
    $card_id = $this->getArg("cardId", AT_enum, true, null, range(1, 6));
    $this->game->vestCounselor($card_id);
    $this->ajaxResponse();
  }

  public function activateCounselor()
  {
    $this->setAjaxMode();
    $this->game->activateCounselor();
    $this->ajaxResponse();
  }

  public function activateNoble()
  {
    $this->setAjaxMode();
    $card_id = $this->getArg("cardId", AT_enum, true, null, range(1, 6));
    $this->game->activateNoble($card_id);
    $this->ajaxResponse();
  }

  public function activateCommander()
  {
    $this->setAjaxMode();
    $militia = $this->getArg("militia", AT_enum, true, null, array("ATTACK", "DEFENSE"));
    $this->game->activateCommander($militia);
    $this->ajaxResponse();
  }

  public function cancelActivation()
  {
    $this->setAjaxMode();
    $this->game->cancelActivation();
    $this->ajaxResponse();
  }

  public function skipActivation()
  {
    $this->setAjaxMode();
    $this->game->skipActivation();
    $this->ajaxResponse();
  }
}
