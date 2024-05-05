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
  <div id="boc_supply-dices" class="boc_row_container boc_supply-dices">
    <div id="boc_dices" class="boc_row_container boc_dices">
      <div id="boc_dice:1" class="boc_dice boc_dice_1"></div>
      <div id="boc_dice:2" class="boc_dice boc_dice_2"></div>
    </div>
    <div
      id="boc_supply_wrap"
      class="whiteblock boc_column_container boc_supply_wrap"
    >
      <h3>{SUPPLY}</h3>
      <div id="boc_supply" class="boc_supply"></div>
    </div>
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
        <div id="boc_defense:{MY_ID}" class="boc_defense"></div>
        <div id="boc_power:{MY_ID}" class="boc_power"></div>
        <div id="boc_attack:{MY_ID}" class="boc_attack"></div>
        <div id="boc_church:{MY_ID}" class="boc_church"></div>
        <div id="boc_gold:{MY_ID}" class="boc_gold"></div>
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
        <div id="boc_defense:{PLAYER_ID}" class="boc_defense"></div>
        <div id="boc_power:{PLAYER_ID}" class="boc_power"></div>
        <div id="boc_attack:{PLAYER_ID}" class="boc_attack"></div>
        <div id="boc_church:{PLAYER_ID}" class="boc_church"></div>
        <div id="boc_gold:{PLAYER_ID}" class="boc_gold"></div>
        <div id="boc_dragon:{PLAYER_ID}" class="boc_dragon"></div>
      </div>
    </div>
    <!-- END othercastleblock -->
  </div>
</div>

<script type="text/javascript"></script>

{OVERALL_GAME_FOOTER}
