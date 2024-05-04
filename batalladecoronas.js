/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * BatallaDeCoronas implementation : © Matheus Gomes matheusgomesforwork@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * batalladecoronas.js
 *
 * BatallaDeCoronas user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
  "dojo",
  "dojo/_base/declare",
  "ebg/core/gamegui",
  "ebg/counter",
], function (dojo, declare) {
  return declare("bgagame.batalladecoronas", ebg.core.gamegui, {
    constructor: function () {
      console.log("batalladecoronas constructor");
    },

    setup: function (gamedatas) {
      console.log("Starting game setup");

      // Setting up player boards
      for (var player_id in gamedatas.players) {
        var player = gamedatas.players[player_id];
      }

      for (const dice in gamedatas.dices) {
        const value = gamedatas.dices[dice];
        const diceElement = $(`boc_dice:${dice}`);

        dojo.addClass(diceElement, `boc_face_${value}`);
      }

      this.setupNotifications();

      console.log("Ending game setup");
    },

    onEnteringState: function (stateName, args) {
      console.log("Entering state: " + stateName);

      if (stateName === "dicesRoll") {
        this.addActionButton("boc_rollDices", _("Roll dices"), "onRollDices");
      }
    },

    onLeavingState: function (stateName) {
      console.log("Leaving state: " + stateName);

      switch (stateName) {
        /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */

        case "dummmy":
          break;
      }
    },

    onUpdateActionButtons: function (stateName, args) {},

    ///////////////////////////////////////////////////
    //// Utility methods

    sendAjaxCall: function (action, args = {}) {
      args.lock = true;

      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          args,
          this,
          (result) => {},
          (isError) => {}
        );
      }
    },

    ///////////////////////////////////////////////////
    //// Player's actions

    onRollDices: function () {
      const action = "rollDices";
      this.sendAjaxCall(action);
    },

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    setupNotifications: function () {
      console.log("notifications subscriptions setup");

      dojo.subscribe("dicesRoll", this, "notif_dicesRoll");
    },

    notif_dicesRoll: function (notif) {
      const dice = notif.args.dice;
      const result = notif.args.result;
      const diceElement = $(`boc_dice:${dice}`);

      dojo.addClass(diceElement, "boc_dice_rolled");

      setTimeout(() => {
        dojo.addClass(diceElement, `boc_face_${result}`);
      }, 500);

      setTimeout(() => {
        dojo.removeClass(diceElement, "boc_dice_rolled");
      }, 1000);
    },
  });
});
