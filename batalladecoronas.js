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
  "ebg/stock",
], function (dojo, declare) {
  return declare("bgagame.batalladecoronas", ebg.core.gamegui, {
    constructor: function () {
      console.log("batalladecoronas constructor");

      this.tokenSize = 50;

      this.supply = {};
      this.gems = {};
      this.treasure = {};
    },

    setup: function (gamedatas) {
      console.log("Starting game setup");

      this.supply = gamedatas.supply;
      this.gems = gamedatas.gems;
      this.treasure = gamedatas.treasure;
      this.attack = gamedatas.attack;
      this.defense = gamedatas.defense;

      console.log(this.attack, this.defense, "attack and defense");
      console.log(this.treasure, "treasure");

      //Setting up player boards
      for (const player_id in gamedatas.players) {
        const player = gamedatas.players[player_id];
        const castleTitle = $(`boc_castle_title:${player_id}`);

        if (player_id != this.player_id) {
          castleTitle.textContent = this.format_string_recursive(
            _("${player_name}'s castle"),
            { player_name: player.name }
          );
        }
      }

      //supply
      const supplyStock = `supplyStock`;
      this[supplyStock] = new ebg.stock();
      this[supplyStock].create(this, $(`boc_supply`), 90, 90);
      this[supplyStock].image_items_per_row = 6;
      this[supplyStock].autowidth = true;
      this[supplyStock].setSelectionMode(0);

      this[supplyStock].addItemType(
        "crown",
        0,
        g_gamethemeurl + "img/elements.png",
        0
      );

      this[supplyStock].addItemType(
        "cross",
        1,
        g_gamethemeurl + "img/elements.png",
        1
      );

      this[supplyStock].addItemType(
        "blacksmith",
        2,
        g_gamethemeurl + "img/elements.png",
        2
      );

      for (const item in this.supply) {
        if (this.supply[item]) {
          this[supplyStock].addToStockWithId(item, item);
        }
      }

      for (const die in gamedatas.dice) {
        const value = gamedatas.dice[die];
        const dieElement = $(`boc_die:${die}`);

        dojo.addClass(dieElement, `boc_face_${value}`);
      }

      for (const player_id in gamedatas.players) {
        //power
        const powerStock = `powerStock:${player_id}`;
        const powerElement = $(`boc_power:${player_id}`);

        this[powerStock] = new ebg.stock();
        this[powerStock].create(this, powerElement, 60, 60);
        this[powerStock].image_items_per_row = 2;
        this[powerStock].centerItems = true;
        this[powerStock].extraClasses = `boc_gem`;
        this[powerStock].setSelectionMode(0);

        this[powerStock].addItemType(
          "purple",
          0,
          g_gamethemeurl + "img/gems.png",
          4
        );
        this[powerStock].addItemType(
          "blue",
          1,
          g_gamethemeurl + "img/gems.png",
          5
        );

        const power = this.gems[player_id].power;

        if (power == 3) {
          this[powerStock].addToStockWithId("purple", 1);
        }

        for (let i = 1; i <= 2 && i <= power; i++) {
          this[powerStock].addToStockWithId("blue", 4 - i);
        }

        //treasure
        for (gold = -1; gold <= 7; gold++) {
          const treasureStock = `treasureStock$${player_id}:${gold}`;
          const treasureElement = $(`boc_treasure$${player_id}:${gold}`);

          this[treasureStock] = new ebg.stock();
          this[treasureStock].create(this, treasureElement, this.tokenSize, this.tokenSize);
          this[treasureStock].image_items_per_row = 10;
          this[treasureStock].extraClasses = `boc_gold`;
          this[treasureStock].setSelectionMode(0);

          this[treasureStock].addItemType(
            "gold",
            0,
            g_gamethemeurl + "img/tokens.png",
            7
          );
        }

        const goldNbr = this.treasure[player_id];
        const treasureStock = `treasureStock$${player_id}:${goldNbr}`;
        const treasureInitial = $(`boc_treasure$${player_id}:0`);
        this[treasureStock].addToStock("gold", treasureInitial);

        //attack
        for (let sword = 0; sword <= 5; sword++) {
          const swordStock = `swordStock$${player_id}:${sword}`;
          const swordElement = $(`boc_sword$${player_id}:${sword}`);

          this[swordStock] = new ebg.stock();
          this[swordStock].create(this, swordElement, this.tokenSize, this.tokenSize);
          this[swordStock].image_items_per_row = 10;
          this[swordStock].extraClasses = `boc_sword`;
          this[swordStock].setSelectionMode(0);

          this[swordStock].addItemType(
            "sword",
            0,
            g_gamethemeurl + "img/tokens.png",
            0
          );
        }

        const swordNbr = this.attack[player_id];
        const swordStock = `swordStock$${player_id}:${swordNbr}`;
        const swordInitial = $(`boc_sword$${player_id}:2`);
        this[swordStock].addToStock("sword", swordInitial);

        //defense
        for (let shield = 0; shield <= 5; shield++) {
          const shieldStock = `shieldStock$${player_id}:${shield}`;
          const shieldElement = $(`boc_shield$${player_id}:${shield}`);

          this[shieldStock] = new ebg.stock();
          this[shieldStock].create(this, shieldElement, this.tokenSize, this.tokenSize);
          this[shieldStock].image_items_per_row = 10;
          this[shieldStock].extraClasses = `boc_shield`;
          this[shieldStock].setSelectionMode(0);

          this[shieldStock].addItemType(
            "shield",
            0,
            g_gamethemeurl + "img/tokens.png",
            0
          );
        }

        const shieldNbr = this.defense[player_id];
        const shieldStock = `shieldStock$${player_id}:${shieldNbr}`;
        const shieldInitial = $(`boc_shield$${player_id}:2`);
        this[shieldStock].addToStock("shield", shieldInitial);
      }

      this.setupNotifications();

      console.log("Ending game setup");
    },

    onEnteringState: function (stateName, args) {
      console.log("Entering state: " + stateName);

      if (stateName === "diceRoll") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton("boc_rollDice", _("Roll dice"), "onRollDice");
        }
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

    onRollDice: function () {
      const action = "rollDice";
      this.sendAjaxCall(action);
    },

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    setupNotifications: function () {
      console.log("notifications subscriptions setup");

      dojo.subscribe("dieRoll", this, "notif_dieRoll");
    },

    notif_dieRoll: function (notif) {
      const die = notif.args.die;
      const result = notif.args.result;
      const dieElement = $(`boc_die:${die}`);

      dojo.addClass(dieElement, "boc_die_rolled");

      setTimeout(() => {
        dojo.addClass(dieElement, `boc_face_${result}`);
      }, 500);

      setTimeout(() => {
        dojo.removeClass(dieElement, "boc_die_rolled");
      }, 1000);
    },
  });
});
