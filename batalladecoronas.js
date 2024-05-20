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

      this.dieSize = 60;
      this.supplyItemSize = 70;
      this.counselorSize = 100;
      this.gemSize = 80;
      this.tokenSize = 80;
      this.dragonSize = 80;

      this.counselorsInfo = {};
      this.churchSquares = {};

      this.supply = {};
      this.claimedSupply = {};
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

      this.counselorsInfo = gamedatas.counselorsInfo;
      this.churchSquares = gamedatas.churchSquares;

      this.supply = gamedatas.supply;
      this.claimedSupply = gamedatas.claimedSupply;
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

      for (let die = 1; die <= 2; die++) {
        const dieStock = `dieStock:${die}`;
        this[dieStock] = new ebg.stock();
        this[dieStock].create(
          this,
          $(`boc_die:${die}`),
          this.dieSize,
          this.dieSize
        );
        this[dieStock].setSelectionMode(0);

        this[dieStock].addItemType(0, 0, g_gamethemeurl + "img/dice.png", 5);
        this[dieStock].addToStock(0);
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

      this[supplyStock].onItemCreate = (element, type, id) => {
        const possibleTitles = {
          crown: _("Crown token"),
          cross: _("Cross token"),
          smith: _("Smith token"),
        };

        this.addTooltip(element.id, possibleTitles[type], "");
      };

      this[supplyStock].addItemType(
        "crown",
        0,
        g_gamethemeurl + "img/supply.png",
        0
      );

      this[supplyStock].addItemType(
        "sacredcross",
        1,
        g_gamethemeurl + "img/supply.png",
        1
      );

      this[supplyStock].addItemType(
        "smith",
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

      this[inactiveCouncilStock].onItemCreate = (element, type, id) => {
        const counselorName = this.counselorsInfo[type].name;
        this.addTooltip(element.id, counselorName, "");
      };

      const inactiveCouncil = this.inactiveCouncil[currentPlayerId];
      for (const cardId in inactiveCouncil) {
        const counselorId = inactiveCouncil[cardId].type_arg;
        const counselor = this.counselorsInfo[counselorId];
        const spritePos = counselor.spritePos;

        this[inactiveCouncilStock].addItemType(
          counselorId,
          spritePos,
          g_gamethemeurl + "img/counselors.png",
          spritePos
        );

        this[inactiveCouncilStock].addToStockWithId(counselorId, cardId);
      }

      for (const player_id in gamedatas.players) {
        const claimedSupply = this.claimedSupply[player_id];

        //crown tower
        const crownTowerStock = `crownTowerStock:${player_id}`;
        const crownTowerElement = $(`boc_crownTower:${player_id}`);

        this[crownTowerStock] = new ebg.stock();
        this[crownTowerStock].create(
          this,
          crownTowerElement,
          this.supplyItemSize,
          this.supplyItemSize
        );
        this[crownTowerStock].image_items_per_row = 3;
        this[crownTowerStock].setSelectionMode(0);

        this[crownTowerStock].onItemCreate = (element, type, id) => {
          this.addTooltip(element.id, _("Crown token"), "");
        };

        this[crownTowerStock].addItemType(
          "crown",
          0,
          g_gamethemeurl + "img/supply.png",
          0
        );

        if (claimedSupply["crown"]) {
          this[crownTowerStock].addToStock("crown");
        }

        //cross tower
        const crossTowerStock = `crossTowerStock:${player_id}`;
        const crossTowerElement = $(`boc_crossTower:${player_id}`);

        this[crossTowerStock] = new ebg.stock();
        this[crossTowerStock].create(
          this,
          crossTowerElement,
          this.supplyItemSize,
          this.supplyItemSize
        );
        this[crossTowerStock].image_items_per_row = 3;
        this[crossTowerStock].setSelectionMode(0);

        this[crossTowerStock].onItemCreate = (element, type, id) => {
          this.addTooltip(element.id, _("Cross token"), "");
        };

        this[crossTowerStock].addItemType(
          "sacredcross",
          0,
          g_gamethemeurl + "img/supply.png",
          1
        );

        if (claimedSupply["sacredcross"]) {
          this[crossTowerStock].addToStock("sacredcross");
        }

        //anvil
        const anvilStock = `anvilStock:${player_id}`;
        const anvilElement = $(`boc_anvil:${player_id}`);

        this[anvilStock] = new ebg.stock();
        this[anvilStock].create(
          this,
          anvilElement,
          this.supplyItemSize,
          this.supplyItemSize
        );
        this[anvilStock].image_items_per_row = 3;
        this[anvilStock].setSelectionMode(0);

        this[anvilStock].onItemCreate = (element, type, id) => {
          this.addTooltip(element.id, _("Smith token"), "");
        };

        this[anvilStock].addItemType(
          "smith",
          0,
          g_gamethemeurl + "img/supply.png",
          2
        );

        if (claimedSupply["smith"]) {
          this[anvilStock].addToStock("smith");
        }

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

          this[chairStock].onItemCreate = (element, type, id) => {
            const counselorName = this.counselorsInfo[type].name;
            this.addTooltip(element.id, counselorName, "");
          };

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
          "blue_gem",
          0,
          g_gamethemeurl + "img/gems.png",
          1
        );

        this[powerStock].addItemType(
          "purple_gem",
          1,
          g_gamethemeurl + "img/gems.png",
          0
        );

        const power = this.gems[player_id].power;

        if (power == 3) {
          this[powerStock].addToStockWithId("blue_gem", 1);
        }

        for (let i = 1; i <= 2 && i <= power; i++) {
          this[powerStock].addToStockWithId("purple_gem", 4 - i);
        }

        //church
        for (const square in this.churchSquares) {
          const clergyStock = `clergyStock$${player_id}:${square}`;

          const clergy = $(`boc_clergy$${player_id}:${square}`);

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
        }

        const activeSquare = this.church[player_id];

        const activeClergyStock = `clergyStock$${player_id}:${activeSquare}`;
        const initialClergy = $(`boc_clergy$${player_id}:0`);

        console.log(this[activeClergyStock]);
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

          this[treasureStock].addItemType(
            "purple_gem",
            0,
            g_gamethemeurl + "img/gems.png",
            0
          );
          this[treasureStock].addItemType(
            "blue_gem",
            0,
            g_gamethemeurl + "img/gems.png",
            1
          );
        }

        const goldNbr = this.treasure[player_id];
        const treasureStock = `treasureStock$${player_id}:${goldNbr}`;
        const treasureInitial = $(`boc_treasure$${player_id}:0`);
        this[treasureStock].addToStock("gold", treasureInitial);

        const gemNbr = this.gems[player_id].treasure;
        const gemsInitial = $(`boc_power:${player_id}`);

        //gem treasure
        for (let gem = 1; gem <= 2 && gem <= gemNbr; gem++) {
          const gemTreasureStock = `treasureStock$${player_id}:${8 - gem}`;
          this[gemTreasureStock].addToStock("purple_gem", gemsInitial);
        }

        if (gemNbr == 3) {
          const gemTreasureStock = `treasureStock$${player_id}:${5}`;
          this[gemTreasureStock].addToStock("blue_gem");
        }

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
      for (let die = 1; die <= 2; die++) {
        const dieStock = `dieStock:${die}`;

        dojo.connect(this[dieStock], "onChangeSelection", this, (targetId) => {
          this.onSelectDie(targetId, this[dieStock]);
        });
      }

      for (const player_id in gamedatas.players) {
        for (let chair = 1; chair <= 6; chair++) {
          const chairStock = `chairStock$${player_id}:${chair}`;
          dojo.connect(this[chairStock], "onChangeSelection", this, () => {
            this.onSelectCounselor(this[chairStock]);
          });
        }
      }

      dojo.connect(
        this[inactiveCouncilStock],
        "onChangeSelection",
        this,
        () => {
          this.onSelectInactiveCounselor(this[inactiveCouncilStock]);
        }
      );

      dojo.query(".boc_myMilitia").connect("onclick", (event) => {
        this.onPickMilitia(event);
      });

      dojo.query(".boc_myClergy").connect("onclick", (event) => {
        console.log("clergy");
        this.onPickSquare(event);
      });

      this.setupNotifications();

      console.log("Ending game setup");
    },

    onEnteringState: function (stateName, args) {
      console.log("Entering state: " + stateName);

      const player_id = this.getActivePlayerId();

      if (stateName === "diceRoll") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton("boc_rollDice", _("Roll dice"), "onRollDice");
        }
      }

      if (stateName === "decisionPhase") {
        if (this.isCurrentPlayerActive()) {
          dojo.query(".boc_die > *").addClass("boc_selectableItem");

          for (let die = 1; die <= 2; die++) {
            this[`dieStock:${die}`].setSelectionMode(1);
          }
        }
      }

      if (stateName === "counselorVesting") {
        if (this.isCurrentPlayerActive()) {
          dojo.query(".boc_unvestedCounselor").addClass("boc_selectableItem");

          this[`inactiveCouncilStock`].setSelectionMode(1);
        }
      }

      if (stateName === "counselorActivation") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "boc_activateCounselor",
            _("Activate"),
            "onActivateCounselor"
          );

          this.addActionButton(
            "boc_skip",
            _("Skip"),
            "onSkipActivation",
            null,
            null,
            "red"
          );
        }
      }

      if (stateName === "nobleActivation") {
        if (this.isCurrentPlayerActive()) {
          this["inactiveCouncilStock"].setSelectionMode(1);
          this.changeChairsSelection(player_id);

          this.addActionButton(
            "boc_cancel",
            _("Cancel"),
            "onCancelActivation",
            null,
            null,
            "red"
          );
        }
      }

      if (stateName === "commanderActivation") {
        if (this.isCurrentPlayerActive()) {
          dojo.query(".boc_myMilitia").addClass("boc_selectableContainer");

          this.addActionButton(
            "boc_cancel",
            _("Cancel"),
            "onCancelActivation",
            null,
            null,
            "red"
          );
        }
      }

      if (stateName === "priestActivation") {
        if (this.isCurrentPlayerActive()) {
          for (const squareId in this.churchSquares) {
            const currentSquareId = this.church[player_id];
            const squareElement = $(`boc_clergy$${player_id}:${squareId}`);

            if (squareId != currentSquareId) {
              dojo.addClass(squareElement, "boc_selectableContainer");
            }
          }

          this.addActionButton(
            "boc_cancel",
            _("Cancel"),
            "onCancelActivation",
            null,
            null,
            "red"
          );
        }
      }
    },

    onLeavingState: function (stateName) {
      console.log("Leaving state: " + stateName);

      const player_id = this.getActivePlayerId();

      if (stateName === "decisionPhase") {
        dojo.query(".boc_die > *").removeClass("boc_selectableItem");

        for (let die = 1; die <= 2; die++) {
          this[`dieStock:${die}`].setSelectionMode(0);
        }
      }

      if (stateName === "counselorVesting") {
        dojo.query(".boc_unvestedCounselor").removeClass("boc_selectableItem");
        this["inactiveCouncilStock"].setSelectionMode(0);
      }

      if (stateName === "nobleActivation") {
        dojo.query(".boc_unvestedCounselor").removeClass("boc_selectableItem");
        this["inactiveCouncilStock"].setSelectionMode(0);

        this.changeChairsSelection(player_id, false);
      }

      if (stateName === "commanderActivation") {
        dojo.query(".boc_myMilitia").removeClass("boc_selectableContainer");
      }

      if (stateName === "priestActivation") {
        dojo.query(".boc_myClergy").removeClass("boc_selectableContainer");
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

    changeChairsSelection: function (player_id, enable = true) {
      for (let chair = 1; chair <= 6; chair++) {
        const chairStock = `chairStock$${player_id}:${chair}`;

        if (enable) {
          this[chairStock].setSelectionMode(1);
          dojo.query(".boc_counselor").addClass("boc_selectableItem");
        } else {
          this[chairStock].setSelectionMode(0);
          dojo.query(".boc_counselor").removeClass("boc_selectableItem");
        }
      }
    },

    unselectOtherStocks: function (stock) {
      if (stock.getSelectedItems().length === 0) {
        return;
      }
      for (field in this) {
        if (
          this[field] &&
          this[field].unselectAll &&
          this[field].getSelectedItems().length > 0 &&
          this[field].control_name != stock.control_name
        ) {
          this[field].unselectAll();
        }
      }
    },

    ///////////////////////////////////////////////////
    //// Player's actions

    //stock selections
    onSelectDie: function (targetId, stock) {
      this.unselectOtherStocks(stock);

      const stateName = this.gamedatas.gamestate.name;
      const selectedItemsNbr = stock.getSelectedItems().length;
      const die = targetId.split(":")[1];

      if (stateName === "decisionPhase") {
        if (this.isCurrentPlayerActive()) {
          this.removeActionButtons();

          if (selectedItemsNbr == 1) {
            const otherStock = die == 1 ? `dieStock:2` : `dieStock:1`;
            this[otherStock].unselectAll();

            this.addActionButton(
              "boc_decideDice",
              _("Confirm selection"),
              () => {
                this.onDecideDice(die);
              }
            );
            return;
          }
        }
      }
    },

    onSelectInactiveCounselor: function (stock) {
      this.unselectOtherStocks(stock);

      const stateName = this.gamedatas.gamestate.name;
      const selectedItemsNbr = stock.getSelectedItems().length;

      if (stateName === "counselorVesting") {
        if (this.isCurrentPlayerActive()) {
          this.removeActionButtons();

          if (selectedItemsNbr == 1) {
            const cardId = stock.getSelectedItems()[0].id;

            this.addActionButton(
              "boc_vestCounselor",
              _("Confirm selection"),
              () => {
                this.onVestCounselor(cardId);
              }
            );
            return;
          }
        }
      }

      if (stateName === "nobleActivation") {
        if (this.isCurrentPlayerActive()) {
          this.removeActionButtons();

          if (selectedItemsNbr == 1) {
            const cardId = stock.getSelectedItems()[0].id;

            this.addActionButton(
              "boc_nobleActivation",
              _("Confirm selection"),
              () => {
                this.onActivateNoble(cardId);
              }
            );
            return;
          }

          this.addActionButton(
            "boc_cancel",
            _("Cancel"),
            "onCancelActivation",
            null,
            null,
            "red"
          );
        }
      }
    },

    onSelectCounselor: function (stock) {
      this.unselectOtherStocks(stock);

      const stateName = this.gamedatas.gamestate.name;
      const selectedItemsNbr = stock.getSelectedItems().length;

      if (stateName === "nobleActivation") {
        if (this.isCurrentPlayerActive()) {
          this.removeActionButtons();
          if (selectedItemsNbr == 1) {
            const cardId = stock.getSelectedItems()[0].id;

            this.addActionButton(
              "boc_nobleActivation",
              _("Confirm selection"),
              () => {
                this.onActivateNoble(cardId);
              }
            );
            return;
          }

          this.addActionButton(
            "boc_cancel",
            _("Cancel"),
            "onCancelActivation",
            null,
            null,
            "red"
          );
        }
      }
    },

    //actions
    onRollDice: function () {
      const action = "rollDice";
      this.sendAjaxCall(action);
    },

    onDecideDice: function (die) {
      const action = "decideDice";

      this.sendAjaxCall(action, { die });
    },

    onVestCounselor: function (cardId) {
      const action = "vestCounselor";

      this.sendAjaxCall(action, { cardId });
    },

    onActivateCounselor: function () {
      const action = "activateCounselor";

      this.sendAjaxCall(action);
    },

    onActivateNoble: function (cardId) {
      const action = "activateNoble";

      this.sendAjaxCall(action, { cardId });
    },

    onPickMilitia: function (event) {
      const stateName = this.gamedatas.gamestate.name;
      if (stateName === "commanderActivation") {
        const action = "activateCommander";

        const idInfo = event.currentTarget.id.split("boc_")[1].split(":");
        const militia = idInfo[0].toUpperCase();
        const player_id = idInfo[1];

        if (this.player_id != player_id) {
          return;
        }

        this.sendAjaxCall(action, { militia });
      }
    },

    onPickMilitia: function (event) {
      const stateName = this.gamedatas.gamestate.name;
      if (stateName === "commanderActivation") {
        const action = "activateCommander";

        const idInfo = event.currentTarget.id.split("boc_")[1].split(":");
        const militia = idInfo[0].toUpperCase();
        const player_id = idInfo[1];

        if (this.player_id != player_id) {
          return;
        }

        this.sendAjaxCall(action, { militia });
      }
    },

    onPickSquare: function (event) {
      const stateName = this.gamedatas.gamestate.name;
      if (stateName === "priestActivation") {
        const action = "activatePriest";

        const square = event.currentTarget.id.split(":")[1];

        this.sendAjaxCall(action, { square });
      }
    },

    onSkipActivation: function () {
      const action = "skipActivation";

      this.sendAjaxCall(action);
    },

    onCancelActivation: function () {
      const action = "cancelActivation";

      this.sendAjaxCall(action);
    },

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    setupNotifications: function () {
      console.log("notifications subscriptions setup");

      dojo.subscribe("dieRoll", this, "notif_dieRoll");
      dojo.subscribe("generateGold", this, "notif_generateGold");
      dojo.subscribe("vestCounselor", this, "notif_vestCounselor");
      dojo.subscribe(
        "vestCounselorPrivately",
        this,
        "notif_vestCounselorPrivately"
      );
      dojo.subscribe("increaseAttack", this, "notif_increaseAttack");
      dojo.subscribe("increaseDefense", this, "notif_increaseDefense");
      dojo.subscribe("moveClergy", this, "notif_moveClergy");
      dojo.subscribe("levelUpDragon", this, "notif_levelUpDragon");
      dojo.subscribe("claimCrown", this, "notif_claimCrown");
      dojo.subscribe("claimCross", this, "notif_claimCross");
      dojo.subscribe("claimSmith", this, "notif_claimSmith");
      dojo.subscribe("claimGem", this, "notif_claimGem");
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

      console.log(destinationStock);

      this[destinationStock].addToStock("gold", originElement);
      this[originStock].removeFromStock("gold");
    },

    notif_vestCounselorPrivately: function (notif) {
      const player_id = notif.args.player_id;
      const chair = notif.args.chair;
      const cardId = notif.args.cardId;
      const counselorId = notif.args.counselorId;

      const originStock = `inactiveCouncilStock`;
      const originElement = `boc_inactiveCouncil`;
      const destinationStock = `chairStock$${player_id}:${chair}`;

      this[destinationStock].addToStockWithId(
        counselorId,
        cardId,
        originElement
      );

      this[originStock].removeFromStockById(cardId);
    },

    notif_vestCounselor: function (notif) {
      const player_id = notif.args.player_id;

      if (player_id == this.player_id) {
        return;
      }

      const chair = notif.args.chair;
      const cardId = notif.args.cardId;
      const counselorId = notif.args.counselorId;

      const originElement = `overall_player_board_${player_id}`;
      const destinationStock = `chairStock$${player_id}:${chair}`;

      this[destinationStock].addToStockWithId(
        counselorId,
        cardId,
        originElement
      );
    },

    notif_increaseAttack: function (notif) {
      const player_id = notif.args.player_id;

      const totalSwords = notif.args.totalSwords;
      const prevSwords = notif.args.prevSwords;

      const originStock = `swordStock$${player_id}:${prevSwords}`;
      const originElement = `boc_sword$${player_id}:${prevSwords}`;
      const destinationStock = `swordStock$${player_id}:${totalSwords}`;

      this[destinationStock].addToStock("sword", originElement);
      this[originStock].removeFromStock("sword");

      this.attack = notif.args.attack;
    },
    notif_increaseDefense: function (notif) {
      const player_id = notif.args.player_id;

      const totalShields = notif.args.totalShields;
      const prevShields = notif.args.prevShields;

      const originStock = `shieldStock$${player_id}:${prevShields}`;
      const originElement = `boc_shield$${player_id}:${prevShields}`;
      const destinationStock = `shieldStock$${player_id}:${totalShields}`;

      this[destinationStock].addToStock("shield", originElement);
      this[originStock].removeFromStock("shield");

      this.defense = notif.args.defense;
    },

    notif_levelUpDragon: function (notif) {
      const player_id = notif.args.player_id;

      const totalLevel = notif.args.totalLevel;
      const prevLevel = notif.args.prevLevel;

      const originStock = `dragonStock$${player_id}:${prevLevel}`;
      const originElement = `boc_dragon$${player_id}:${prevLevel}`;
      const destinationStock = `dragonStock$${player_id}:${totalLevel}`;

      this[destinationStock].addToStock("dragon", originElement);
      this[originStock].removeFromStock("dragon");

      this.dragon = notif.args.dragon;
    },

    notif_moveClergy: function (notif) {
      const player_id = notif.args.player_id;

      const newSquare = notif.args.newSquare;
      const prevSquare = notif.args.prevSquare;

      const originStock = `clergyStock$${player_id}:${prevSquare}`;
      const originElement = `boc_clergy$${player_id}:${prevSquare}`;
      const destinationStock = `clergyStock$${player_id}:${newSquare}`;

      this[destinationStock].addToStock("clergy", originElement);
      this[originStock].removeFromStock("clergy");

      this.church = notif.args.church;
    },

    notif_claimCrown: function (notif) {
      const player_id = notif.args.player_id;
      const other_player_id = notif.args.other_player_id;

      const isOwned = notif.args.isOwned;

      const originStock = isOwned
        ? `crownTowerStock:${other_player_id}`
        : `supplyStock`;

      const originElement = isOwned
        ? `boc_crownTower:${other_player_id}`
        : `boc_supply`;
      const destinationStock = `crownTowerStock:${player_id}`;

      this[destinationStock].addToStock("crown", originElement);
      this[originStock].removeFromStock("crown");

      this.supply = notif.args.supply;
    },

    notif_claimCross: function (notif) {
      const player_id = notif.args.player_id;
      const other_player_id = notif.args.other_player_id;

      const isOwned = notif.args.isOwned;

      const originStock = isOwned
        ? `crossTowerStock:${other_player_id}`
        : `supplyStock`;

      const originElement = isOwned
        ? `boc_crossTower:${other_player_id}`
        : `boc_supply`;
      const destinationStock = `crossTowerStock:${player_id}`;

      this[destinationStock].addToStock("sacredcross", originElement);
      this[originStock].removeFromStock("sacredcross");

      this.supply = notif.args.supply;
    },

    notif_claimSmith: function (notif) {
      const player_id = notif.args.player_id;
      const other_player_id = notif.args.other_player_id;

      const isOwned = notif.args.isOwned;

      const originStock = isOwned
        ? `anvilStock:${other_player_id}`
        : `supplyStock`;

      const originElement = isOwned
        ? `boc_anvil:${other_player_id}`
        : `boc_supply`;
      const destinationStock = `anvilStock:${player_id}`;

      this[destinationStock].addToStock("smith", originElement);
      this[originStock].removeFromStock("smith");

      this.supply = notif.args.supply;
    },

    notif_claimGem: function (notif) {
      const player_id = notif.args.player_id;
      const other_player_id = notif.args.other_player_id;

      const totalGems = notif.args.totalGems;

      const originStock = `powerStock:${other_player_id}`;
      const originElement = `boc_power:${other_player_id}`;
      const destinationStock = `treasureStock$${player_id}:${8 - totalGems}`;

      if (totalGems == 3) {
        this[destinationStock].addToStock("blue_gem", originElement);
      } else {
        this[destinationStock].addToStock("purple_gem", originElement);
      }

      this[originStock].removeFromStockById(4 - totalGems);

      this.treasure = notif.args.treasure;
      this.gems = notif.args.gemsByLocations;
    },
  });
});
