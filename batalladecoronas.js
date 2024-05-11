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

      this.supplyItemSize = 70;
      this.counselorSize = 100;
      this.gemSize = 80;
      this.tokenSize = 80;
      this.dragonSize = 80;

      this.supply = {};
      this.counselorsInfo = {};
      this.inactiveCouncil = {};
      this.council = {};
      this.gems = {};
      this.attack = {};
      this.defense = {};
      this.church = {};
      this.treasure = {};
      this.dragon = {};
    },

    setup: function (gamedatas) {
      console.log("Starting game setup");

      this.supply = gamedatas.supply;
      this.counselorsInfo = gamedatas.counselorsInfo;
      this.inactiveCouncil = gamedatas.inactiveCouncil;
      this.council = gamedatas.council;
      this.gems = gamedatas.gems;
      this.attack = gamedatas.attack;
      this.defense = gamedatas.defense;
      this.church = gamedatas.church;
      this.treasure = gamedatas.treasure;
      this.dragon = gamedatas.dragon;

      const currentPlayerId = this.player_id;

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
      this[supplyStock].create(
        this,
        $(`boc_supply`),
        this.supplyItemSize,
        this.supplyItemSize
      );
      this[supplyStock].image_items_per_row = 6;
      this[supplyStock].autowidth = true;
      this[supplyStock].setSelectionMode(0);

      this[supplyStock].addItemType(
        "crown",
        0,
        g_gamethemeurl + "img/supply.png",
        0
      );

      this[supplyStock].addItemType(
        "cross",
        1,
        g_gamethemeurl + "img/supply.png",
        1
      );

      this[supplyStock].addItemType(
        "blacksmith",
        2,
        g_gamethemeurl + "img/supply.png",
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

      //inactive council
      const inactiveCouncilStock = `inactiveCouncilStock`;
      const inactiveCouncilElement = $("boc_inactiveCouncil");

      this[inactiveCouncilStock] = new ebg.stock();
      this[inactiveCouncilStock].create(
        this,
        inactiveCouncilElement,
        this.counselorSize,
        this.counselorSize
      );
      this[inactiveCouncilStock].image_items_per_row = 6;
      this[inactiveCouncilStock].autowidth = true;
      this[inactiveCouncilStock].centerItems = true;
      this[inactiveCouncilStock].item_margin = 8;
      this[inactiveCouncilStock].extraClasses = "boc_unvestedCounselor";
      this[inactiveCouncilStock].setSelectionMode(0);

      const inactiveCouncil = this.inactiveCouncil[currentPlayerId];

      for (const cardId in inactiveCouncil) {
        const counselorId = inactiveCouncil[cardId].type_arg;
        const counselor = this.counselorsInfo[counselorId];
        const spritePos = counselor.spritePos;
        const counselorName = counselor.name;

        this[inactiveCouncilStock].addItemType(
          counselorId,
          spritePos,
          g_gamethemeurl + "img/counselors.png",
          spritePos
        );

        this[inactiveCouncilStock].addToStockWithId(counselorId, cardId);

        const counselorElement = `boc_inactiveCouncil_item_${cardId}`;
        this.addTooltip(counselorElement, counselorName, "");
      }

      for (const player_id in gamedatas.players) {
        //council
        for (let chair = 1; chair <= 6; chair++) {
          const chairStock = `chairStock$${player_id}:${chair}`;
          const chairElement = $(`boc_chair$${player_id}:${chair}`);

          this[chairStock] = new ebg.stock();
          this[chairStock].create(
            this,
            chairElement,
            this.counselorSize,
            this.counselorSize
          );
          this[chairStock].image_items_per_row = 6;
          this[chairStock].extraClasses = "boc_counselor";
          this[chairStock].setSelectionMode(0);

          for (const counselorId in this.counselorsInfo) {
            const counselor = this.counselorsInfo[counselorId];

            const spritePos = counselor.spritePos;
            this[chairStock].addItemType(
              counselorId,
              0,
              g_gamethemeurl + "img/counselors.png",
              spritePos
            );
          }

          const chairCounselor = this.council[player_id][chair];

          if (chairCounselor) {
            this[chairStock].addToStockWithId(
              chairCounselor.type_arg,
              chairCounselor.id
            );
          }
        }

        //power
        const powerStock = `powerStock:${player_id}`;
        const powerElement = $(`boc_power:${player_id}`);

        this[powerStock] = new ebg.stock();
        this[powerStock].create(this, powerElement, this.gemSize, this.gemSize);
        this[powerStock].image_items_per_row = 2;
        this[powerStock].centerItems = true;
        this[powerStock].extraClasses = "boc_gem";
        this[powerStock].setSelectionMode(0);

        this[powerStock].addItemType(
          "blue",
          0,
          g_gamethemeurl + "img/gems.png",
          1
        );

        this[powerStock].addItemType(
          "purple",
          1,
          g_gamethemeurl + "img/gems.png",
          0
        );

        const power = this.gems[player_id].power;

        if (power == 3) {
          this[powerStock].addToStockWithId("blue", 1);
        }

        for (let i = 1; i <= 2 && i <= power; i++) {
          this[powerStock].addToStockWithId("purple", 4 - i);
        }

        //church
        const church = ["DOOR", "GOLDEN", "BLUE", "RED"];

        church.forEach((house) => {
          const clergyStock = `clergyStock$${player_id}:${house}`;
          const clergy = $(`boc_clergy$${player_id}:${house}`);

          this[clergyStock] = new ebg.stock();
          this[clergyStock].create(
            this,
            clergy,
            this.tokenSize,
            this.tokenSize
          );

          this[clergyStock].image_items_per_row = 3;
          this[clergyStock].extraClasses = `boc_clergy`;
          this[clergyStock].setSelectionMode(0);

          this[clergyStock].addItemType(
            "clergy",
            0,
            g_gamethemeurl + "img/tokens.png",
            1
          );
        });

        const activeHouse = this.church[player_id];
        const activeClergyStock = `clergyStock$${player_id}:${activeHouse}`;
        const initialClergy = $(`boc_clergy$${player_id}:DOOR`);
        this[activeClergyStock].addToStock("clergy", initialClergy);

        //attack
        for (let sword = 0; sword <= 5; sword++) {
          const swordStock = `swordStock$${player_id}:${sword}`;
          const swordElement = $(`boc_sword$${player_id}:${sword}`);

          this[swordStock] = new ebg.stock();
          this[swordStock].create(
            this,
            swordElement,
            this.tokenSize,
            this.tokenSize
          );
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
          this[shieldStock].create(
            this,
            shieldElement,
            this.tokenSize,
            this.tokenSize
          );
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

        //treasure
        for (let gold = -1; gold <= 7; gold++) {
          const treasureStock = `treasureStock$${player_id}:${gold}`;
          const treasureElement = $(`boc_treasure$${player_id}:${gold}`);

          this[treasureStock] = new ebg.stock();
          this[treasureStock].create(
            this,
            treasureElement,
            this.tokenSize,
            this.tokenSize
          );
          this[treasureStock].image_items_per_row = 3;
          this[treasureStock].extraClasses = `boc_gold`;
          this[treasureStock].setSelectionMode(0);

          this[treasureStock].addItemType(
            "gold",
            0,
            g_gamethemeurl + "img/tokens.png",
            2
          );
        }

        const goldNbr = this.treasure[player_id];
        const treasureStock = `treasureStock$${player_id}:${goldNbr}`;
        const treasureInitial = $(`boc_treasure$${player_id}:0`);
        this[treasureStock].addToStock("gold", treasureInitial);

        //dragon
        for (let dragonLevel = 0; dragonLevel <= 5; dragonLevel++) {
          const dragonStock = `dragonStock$${player_id}:${dragonLevel}`;
          const dragonElement = $(`boc_dragon$${player_id}:${dragonLevel}`);

          this[dragonStock] = new ebg.stock();
          this[dragonStock].create(
            this,
            dragonElement,
            this.dragonSize,
            this.dragonSize
          );
          this[dragonStock].image_items_per_row = 1;
          this[dragonStock].extraClasses = `boc_bg_contain boc_dragon_level`;
          this[dragonStock].setSelectionMode(0);

          this[dragonStock].addItemType(
            "dragon",
            0,
            g_gamethemeurl + "img/dragon.png",
            0
          );
        }

        const currentDragon = this.dragon[player_id];
        const currentDragonStock = `dragonStock$${player_id}:${currentDragon}`;
        const initialDragon = $(`boc_dragon$${player_id}:0`);
        this[currentDragonStock].addToStock("dragon", initialDragon);
      }

      //connections
      dojo.query(".boc_die").connect("onclick", this, (event) => {
        this.onDecideDice(event);
      });
      dojo.query(".boc_unvestedCounselor").connect("onclick", this, (event) => {
        this.onVestCouncelor(event);
      });

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

      if (stateName === "decisionPhase") {
        if (this.isCurrentPlayerActive()) {
          dojo.query(".boc_die").addClass("boc_selectable");
        }
      }

      if (stateName === "counselorVesting") {
        if (this.isCurrentPlayerActive()) {
          dojo.query(".boc_unvestedCounselor").addClass("boc_selectable");
        }
      }
    },

    onLeavingState: function (stateName) {
      console.log("Leaving state: " + stateName);

      if (stateName === "decisionPhase") {
        dojo.query(".boc_die").removeClass("boc_selectable");
      }

      if (stateName === "counselorVesting") {
        dojo.query(".boc_unvestedCounselor").removeClass("boc_selectable");
      }
    },

    onUpdateActionButtons: function (stateName, args) {},

    ///////////////////////////////////////////////////
    //// Utility methods

    sendAjaxCall: function (action, args = {}) {
      args.lock = true;

      if (this.checkAction(action)) {
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

    onDecideDice: function (event) {
      const action = "decideDice";

      const die = event.target.id.split(":")[1];
      this.sendAjaxCall(action, { die });
    },

    onVestCouncelor: function (event) {
      const action = "vestCounselor";

      const cardId = event.target.id.split("item_")[1];
      this.sendAjaxCall(action, { cardId });
    },

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    setupNotifications: function () {
      console.log("notifications subscriptions setup");

      dojo.subscribe("dieRoll", this, "notif_dieRoll");
      dojo.subscribe("generateGold", this, "notif_generateGold");
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

    notif_generateGold: function (notif) {
      const player_id = notif.args.player_id;
      const prevGold = notif.args.prevGold;
      const totalGold = notif.args.totalGold;

      const originStock = `treasureStock$${player_id}:${prevGold}`;
      const originElement = `boc_treasure$${player_id}:${prevGold}`;
      const destinationStock = `treasureStock$${player_id}:${totalGold}`;

      this[destinationStock].addToStock("gold", originElement);
      this[originStock].removeFromStock("gold");
    },

    notif_vestCounselor: function (notif) {},
  });
});
