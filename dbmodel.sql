-- ------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- BatallaDeCoronas implementation : © Matheus Gomes matheusgomesforwork@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----
-- dbmodel.sql
-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here
-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.
-- Example 2: add a custom field to the standard "player" table
-- ALTER TABLE `player` ADD `player_my_custom_field` INT UNSIGNED NOT NULL DEFAULT '0';
CREATE TABLE IF NOT EXISTS `counselor` (
    `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `card_type` varchar(16) NOT NULL,
    `card_type_arg` int(11) NOT NULL,
    `card_location` varchar(16) NOT NULL,
    `card_location_arg` int(11) NOT NULL,
    PRIMARY KEY (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
ALTER TABLE `player`
ADD `attack` SMALLINT UNSIGNED NOT NULL DEFAULT '2';
ALTER TABLE `player`
ADD `defense` SMALLINT UNSIGNED NOT NULL DEFAULT '2';
ALTER TABLE `player`
ADD `gem_power` SMALLINT UNSIGNED NOT NULL DEFAULT '3';
ALTER TABLE `player`
ADD `gem_treasure` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player`
ADD `gold` SMALLINT NOT NULL DEFAULT '0';
ALTER TABLE `player`
ADD `max_gold` SMALLINT UNSIGNED NOT NULL DEFAULT '7';
ALTER TABLE `player`
ADD `dragon` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player`
ADD `crown` TINYINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player`
ADD `sacredcross` TINYINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player`
ADD `smith` TINYINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player`
ADD `clergy` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player`
ADD `reroll` SMALLINT UNSIGNED NOT NULL DEFAULT '1';