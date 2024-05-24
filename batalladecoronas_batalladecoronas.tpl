{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- BatallaDeCoronas implementation : © Matheus Gomes matheusgomesforwork@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<div id="boc_gameArea" class="boc_gameArea">
  <div id="boc_supply-dice" class="boc_rowContainer boc_supply-dice">
    <div id="boc_dice" class="boc_rowContainer boc_dice">
      <div id="boc_die:1" class="boc_die boc_die_1"></div>
      <div id="boc_die:2" class="boc_die boc_die_2"></div>
    </div>
    <div id="boc_supply" class="boc_supply"></div>
  </div>
  <div id="boc_inactiveCouncil" class="boc_inactiveCouncil"></div>
  <div id="boc_castles" class="boc_castles">
    <!-- BEGIN mycastleblock -->
    <div
      id="boc_castleWrapper:{MY_ID}"
      class="columnContainer whiteblock boc_castleWrapper"
    >
      <h3
        id="boc_castle_title:{MY_ID}"
        class="boc_castle_title"
        style="color: #{MY_COLOR}"
      >
        {YOUR CASTLE}
      </h3>
      <div id="boc_castle" class="boc_castle">
        <div id="boc_crownTower:{MY_ID}" class="boc_crownTower"></div>
        <div id="boc_crossTower:{MY_ID}" class="boc_crossTower"></div>
        <div id="boc_anvil:{MY_ID}" class="boc_anvil"></div>
        <div id="boc_council:{MY_ID}" class="boc_council">
          <div id="boc_chair${MY_ID}:1" class="boc_chair_1"></div>
          <div id="boc_chair${MY_ID}:2" class="boc_chair_2"></div>
          <div id="boc_chair${MY_ID}:3" class="boc_chair_3"></div>
          <div id="boc_chair${MY_ID}:4" class="boc_chair_4"></div>
          <div id="boc_chair${MY_ID}:5" class="boc_chair_5"></div>
          <div id="boc_chair${MY_ID}:6" class="boc_chair_6"></div>
        </div>
        <div
          id="boc_defense:{MY_ID}"
          class="boc_defense"
          data-militia="DEFENSE"
          data-area="DEFENSE"
        >
          <div id="boc_shield${MY_ID}:0" class="boc_shield_0"></div>
          <div id="boc_shield${MY_ID}:1" class="boc_shield_1"></div>
          <div id="boc_shield${MY_ID}:2" class="boc_shield_2"></div>
          <div id="boc_shield${MY_ID}:3" class="boc_shield_3"></div>
          <div id="boc_shield${MY_ID}:4" class="boc_shield_4"></div>
          <div id="boc_shield${MY_ID}:5" class="boc_shield_5"></div>
          <div id="boc_shield${MY_ID}:" class="boc_shield_6"></div>
        </div>
        <div id="boc_power:{MY_ID}" class="boc_power"></div>
        <div
          id="boc_attack:{MY_ID}"
          class="boc_attack"
          data-militia="ATTACK"
          data-area="ATTACK"
        >
          <div id="boc_sword${MY_ID}:0" class="boc_sword_0"></div>
          <div id="boc_sword${MY_ID}:1" class="boc_sword_1"></div>
          <div id="boc_sword${MY_ID}:2" class="boc_sword_2"></div>
          <div id="boc_sword${MY_ID}:3" class="boc_sword_3"></div>
          <div id="boc_sword${MY_ID}:4" class="boc_sword_4"></div>
          <div id="boc_sword${MY_ID}:5" class="boc_sword_5"></div>
          <div id="boc_sword${MY_ID}:6" class="boc_sword_6"></div>
        </div>
        <div id="boc_church:{MY_ID}" class="boc_church">
          <div id="boc_clergy${MY_ID}:0" class="boc_clergy_DOOR"></div>
          <div
            id="boc_clergy${MY_ID}:1"
            class="boc_clergy_GOLDEN"
            data-clergy="1"
          ></div>
          <div
            id="boc_clergy${MY_ID}:2"
            class="boc_clergy_BLUE"
            data-clergy="2"
          ></div>
          <div
            id="boc_clergy${MY_ID}:3"
            class="boc_clergy_RED"
            data-clergy="3"
          ></div>
        </div>
        <div id="boc_treasure:{MY_ID}" class="boc_treasure">
          <div id="boc_treasure${MY_ID}:-1" class="boc_treasure_-1"></div>
          <div id="boc_treasure${MY_ID}:0" class="boc_treasure_0"></div>
          <div id="boc_treasure${MY_ID}:1" class="boc_treasure_1"></div>
          <div id="boc_treasure${MY_ID}:2" class="boc_treasure_2"></div>
          <div id="boc_treasure${MY_ID}:3" class="boc_treasure_3"></div>
          <div id="boc_treasure${MY_ID}:4" class="boc_treasure_4"></div>
          <div id="boc_treasure${MY_ID}:5" class="boc_treasure_5"></div>
          <div id="boc_treasure${MY_ID}:6" class="boc_treasure_6"></div>
          <div id="boc_treasure${MY_ID}:7" class="boc_treasure_7"></div>
        </div>
        <div id="boc_dragon:{MY_ID}" class="boc_dragon" data-area="DRAGON">
          <div id="boc_dragon${MY_ID}:0" class="boc_dragon_0"></div>
          <div id="boc_dragon${MY_ID}:1" class="boc_dragon_1"></div>
          <div id="boc_dragon${MY_ID}:2" class="boc_dragon_2"></div>
          <div id="boc_dragon${MY_ID}:3" class="boc_dragon_3"></div>
          <div id="boc_dragon${MY_ID}:4" class="boc_dragon_4"></div>
          <div id="boc_dragon${MY_ID}:5" class="boc_dragon_5"></div>
        </div>
      </div>
    </div>
    <!-- END mycastleblock -->
    <!-- BEGIN othercastleblock -->
    <div
      id="boc_castleWrapper:{PLAYER_ID}"
      class="columnContainer whiteblock boc_castleWrapper"
    >
      <h3
        id="boc_castle_title:{PLAYER_ID}"
        class="boc_castle_title"
        style="color: #{PLAYER_COLOR}"
      >
        {PLAYER_NAME}
      </h3>
      <div id="boc_castle" class="boc_castle">
        <div id="boc_crownTower:{PLAYER_ID}" class="boc_crownTower"></div>
        <div id="boc_crossTower:{PLAYER_ID}" class="boc_crossTower"></div>
        <div id="boc_anvil:{PLAYER_ID}" class="boc_anvil"></div>
        <div id="boc_council:{PLAYER_ID}" class="boc_council">
          <div id="boc_chair${PLAYER_ID}:1" class="boc_chair_1"></div>
          <div id="boc_chair${PLAYER_ID}:2" class="boc_chair_2"></div>
          <div id="boc_chair${PLAYER_ID}:3" class="boc_chair_3"></div>
          <div id="boc_chair${PLAYER_ID}:4" class="boc_chair_4"></div>
          <div id="boc_chair${PLAYER_ID}:5" class="boc_chair_5"></div>
          <div id="boc_chair${PLAYER_ID}:6" class="boc_chair_6"></div>
        </div>
        <div id="boc_defense:{PLAYER_ID}" class="boc_defense">
          <div id="boc_shield${PLAYER_ID}:0" class="boc_shield_0"></div>
          <div id="boc_shield${PLAYER_ID}:1" class="boc_shield_1"></div>
          <div id="boc_shield${PLAYER_ID}:2" class="boc_shield_2"></div>
          <div id="boc_shield${PLAYER_ID}:3" class="boc_shield_3"></div>
          <div id="boc_shield${PLAYER_ID}:4" class="boc_shield_4"></div>
          <div id="boc_shield${PLAYER_ID}:5" class="boc_shield_5"></div>
          <div id="boc_shield${PLAYER_ID}:" class="boc_shield_6"></div>
        </div>
        <div id="boc_power:{PLAYER_ID}" class="boc_power"></div>
        <div id="boc_attack:{PLAYER_ID}" class="boc_attack">
          <div id="boc_sword${PLAYER_ID}:0" class="boc_sword_0"></div>
          <div id="boc_sword${PLAYER_ID}:1" class="boc_sword_1"></div>
          <div id="boc_sword${PLAYER_ID}:2" class="boc_sword_2"></div>
          <div id="boc_sword${PLAYER_ID}:3" class="boc_sword_3"></div>
          <div id="boc_sword${PLAYER_ID}:4" class="boc_sword_4"></div>
          <div id="boc_sword${PLAYER_ID}:5" class="boc_sword_5"></div>
          <div id="boc_sword${PLAYER_ID}:" class="boc_sword_6"></div>
        </div>
        <div id="boc_church:{PLAYER_ID}" class="boc_church">
          <div id="boc_clergy${PLAYER_ID}:0" class="boc_clergy_DOOR"></div>
          <div id="boc_clergy${PLAYER_ID}:1" class="boc_clergy_GOLDEN"></div>
          <div id="boc_clergy${PLAYER_ID}:2" class="boc_clergy_BLUE"></div>
          <div id="boc_clergy${PLAYER_ID}:3" class="boc_clergy_RED"></div>
        </div>
        <div id="boc_treasure:{PLAYER_ID}" class="boc_treasure">
          <div id="boc_treasure${PLAYER_ID}:-1" class="boc_treasure_-1"></div>
          <div id="boc_treasure${PLAYER_ID}:0" class="boc_treasure_0"></div>
          <div id="boc_treasure${PLAYER_ID}:1" class="boc_treasure_1"></div>
          <div id="boc_treasure${PLAYER_ID}:2" class="boc_treasure_2"></div>
          <div id="boc_treasure${PLAYER_ID}:3" class="boc_treasure_3"></div>
          <div id="boc_treasure${PLAYER_ID}:4" class="boc_treasure_4"></div>
          <div id="boc_treasure${PLAYER_ID}:5" class="boc_treasure_5"></div>
          <div id="boc_treasure${PLAYER_ID}:6" class="boc_treasure_6"></div>
          <div id="boc_treasure${PLAYER_ID}:7" class="boc_treasure_7"></div>
        </div>
        <div id="boc_dragon:{PLAYER_ID}" class="boc_dragon">
          <div id="boc_dragon${PLAYER_ID}:0" class="boc_dragon_0"></div>
          <div id="boc_dragon${PLAYER_ID}:1" class="boc_dragon_1"></div>
          <div id="boc_dragon${PLAYER_ID}:2" class="boc_dragon_2"></div>
          <div id="boc_dragon${PLAYER_ID}:3" class="boc_dragon_3"></div>
          <div id="boc_dragon${PLAYER_ID}:4" class="boc_dragon_4"></div>
          <div id="boc_dragon${PLAYER_ID}:5" class="boc_dragon_5"></div>
        </div>
      </div>
    </div>
    <!-- END othercastleblock -->
  </div>
</div>

<script type="text/javascript"></script>

{OVERALL_GAME_FOOTER}
