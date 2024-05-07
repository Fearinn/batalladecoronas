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

<div id="boc_game_area" class="boc_game_area">
  <div id="boc_supply-dice" class="boc_row_container boc_supply-dice">
    <div id="boc_dice" class="boc_row_container boc_dice">
      <div id="boc_die:1" class="boc_die boc_die_1"></div>
      <div id="boc_die:2" class="boc_die boc_die_2"></div>
    </div>
    <div id="boc_supply" class="boc_supply"></div>
  </div>
  <div id="boc_castles" class="boc_castles">
    <!-- BEGIN mycastleblock -->
    <div
      id="boc_castle_wrap:{MY_ID}"
      class="column_container whiteblock boc_castle_wrap"
    >
      <h3
        id="boc_castle_title:{MY_ID}"
        class="boc_castle_title"
        style="color: #{MY_COLOR}"
      >
        {YOUR CASTLE}
      </h3>
      <div id="boc_castle" class="boc_castle">
        <div id="boc_council:{MY_ID}" class="boc_council"></div>
        <div id="boc_defense:{MY_ID}" class="boc_defense">
          <div id="boc_shield${MY_ID}:0" class="boc_shield_0"></div>
          <div id="boc_shield${MY_ID}:1" class="boc_shield_1"></div>
          <div id="boc_shield${MY_ID}:2" class="boc_shield_2"></div>
          <div id="boc_shield${MY_ID}:3" class="boc_shield_3"></div>
          <div id="boc_shield${MY_ID}:4" class="boc_shield_4"></div>
          <div id="boc_shield${MY_ID}:5" class="boc_shield_5"></div>
          <div id="boc_shield${MY_ID}:" class="boc_shield_6"></div>
        </div>
        <div id="boc_power:{MY_ID}" class="boc_power"></div>
        <div id="boc_attack:{MY_ID}" class="boc_attack">
          <div id="boc_sword${MY_ID}:0" class="boc_sword_0"></div>
          <div id="boc_sword${MY_ID}:1" class="boc_sword_1"></div>
          <div id="boc_sword${MY_ID}:2" class="boc_sword_2"></div>
          <div id="boc_sword${MY_ID}:3" class="boc_sword_3"></div>
          <div id="boc_sword${MY_ID}:4" class="boc_sword_4"></div>
          <div id="boc_sword${MY_ID}:5" class="boc_sword_5"></div>
          <div id="boc_sword${MY_ID}:6" class="boc_sword_6"></div>
        </div>
        <div id="boc_church:{MY_ID}" class="boc_church"></div>
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
        <div id="boc_dragon:{MY_ID}" class="boc_dragon"></div>
      </div>
    </div>
    <!-- END mycastleblock -->
    <!-- BEGIN othercastleblock -->
    <div
      id="boc_castle_wrap:{PLAYER_ID}"
      class="column_container whiteblock boc_castle_wrap"
    >
      <h3
        id="boc_castle_title:{PLAYER_ID}"
        class="boc_castle_title"
        style="color: #{PLAYER_COLOR}"
      >
        {PLAYER_NAME}
      </h3>
      <div id="boc_castle" class="boc_castle">
        <div id="boc_council:{PLAYER_ID}" class="boc_council"></div>
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
        <div id="boc_church:{PLAYER_ID}" class="boc_church"></div>
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
        <div id="boc_dragon:{PLAYER_ID}" class="boc_dragon"></div>
      </div>
    </div>
    <!-- END othercastleblock -->
  </div>
</div>

<script type="text/javascript"></script>

{OVERALL_GAME_FOOTER}
