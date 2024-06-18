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

  private function checkVersion()
  {
    $clientVersion = (int) $this->getArg('gameVersion', AT_int, false);
    $this->game->checkVersion($clientVersion);
  }

  public function rollDice()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->rollDice();
    $this->ajaxResponse();
  }

  public function decideDice()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $die = $this->getArg("die", AT_enum, true, null, array(1, 2));
    $this->game->decideDice($die);
    $this->ajaxResponse();
  }

  public function vestCounselor()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $card_id = $this->getArg("cardId", AT_enum, true, null, range(1, 12));
    $this->game->vestCounselor($card_id);
    $this->ajaxResponse();
  }

  public function activateCounselor()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->activateCounselor();
    $this->ajaxResponse();
  }

  public function activateNoble()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $card_id = $this->getArg("cardId", AT_enum, true, null, range(1, 12));
    $this->game->activateNoble($card_id);
    $this->ajaxResponse();
  }

  public function activateCommander()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $militia = $this->getArg("militia", AT_enum, true, null, array("ATTACK", "DEFENSE"));
    $this->game->activateCommander($militia);
    $this->ajaxResponse();
  }

  public function activatePriest()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $square = $this->getArg("square", AT_enum, true, null, range(1, 3));
    $this->game->activatePriest($square);
    $this->ajaxResponse();
  }

  public function cancelActivation()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->cancelActivation();
    $this->ajaxResponse();
  }

  public function skipActivation()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->skipActivation();
    $this->ajaxResponse();
  }

  public function buyArea()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $area = $this->getArg("area", AT_enum, true, null, array("ATTACK", "DEFENSE", "DRAGON"));
    $this->game->buyArea($area);
    $this->ajaxResponse();
  }

  public function skipBuying()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->skipBuying();
    $this->ajaxResponse();
  }

  public function activateToken()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $token = $this->getArg("token", AT_enum, true, null, array("CROWN", "CROSS"));
    $this->game->activateToken($token);
    $this->ajaxResponse();
  }

  public function activateCrossToken()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $square = $this->getArg("square", AT_enum, true, null, range(1, 3));
    $this->game->activateCrossToken($square);
    $this->ajaxResponse();
  }

  public function activateSmithToken()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->activateSmithToken();
    $this->ajaxResponse();
  }

  public function cancelToken()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->cancelToken();
    $this->ajaxResponse();
  }

  public function skipToken()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->skipToken();
    $this->ajaxResponse();
  }

  public function skipSmithToken()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->skipSmithToken();
    $this->ajaxResponse();
  }

  public function startBattle()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->startBattle();
    $this->ajaxResponse();
  }

  public function skipBattle()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->skipBattle();
    $this->ajaxResponse();
  }

  public function disputeResult()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->disputeResult();
    $this->ajaxResponse();
  }

  public function skipDispute()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->skipDispute();
    $this->ajaxResponse();
  }

  public function destroyShields()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $shield_nbr = $this->getArg("shield_nbr", AT_enum, true, null, range(1, 5));
    $this->game->destroyShields($shield_nbr);
    $this->ajaxResponse();
  }

  public function skipDestruction()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->skipDestruction();
    $this->ajaxResponse();
  }
}
