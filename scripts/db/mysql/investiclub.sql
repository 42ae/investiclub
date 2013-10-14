SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `investiclub` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `investiclub` ;

-- -----------------------------------------------------
-- Table `investiclub`.`permissions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`permissions` (
  `permission_id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `resource` VARCHAR(100) NOT NULL ,
  `privilege` VARCHAR(100) NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`permission_id`) )
ENGINE = MyISAM
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`brokers`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`brokers` (
  `broker_id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `url` VARCHAR(255) NULL DEFAULT NULL ,
  `country` CHAR(2) NULL DEFAULT NULL ,
  `is_default` TINYINT NULL DEFAULT FALSE ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`broker_id`) ,
  INDEX `idx_id_default` (`is_default` ASC) )
ENGINE = MyISAM
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`clubs`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`clubs` (
  `club_id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `broker_id` SMALLINT UNSIGNED NOT NULL ,
  `country` CHAR(2) NOT NULL ,
  `currency` CHAR(3) NOT NULL ,
  `registration_date` DATE NOT NULL ,
  `dissolution_date` DATE NULL DEFAULT NULL ,
  `active` TINYINT NOT NULL DEFAULT TRUE ,
  `created_on` DATETIME NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`club_id`) )
ENGINE = MyISAM
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`user_settings`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`user_settings` (
  `user_id` INT UNSIGNED NOT NULL ,
  `locale` VARCHAR(11) NOT NULL DEFAULT 'en_US' ,
  `currency` CHAR(3) NOT NULL DEFAULT 'EUR' COMMENT 'Currency code that follows ISO 4217 standard.' ,
  `timezone` VARCHAR(45) NOT NULL DEFAULT 'UTC' ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`user_id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(50) NULL DEFAULT NULL ,
  `password` CHAR(128) NOT NULL ,
  `last_name` VARCHAR(45) NOT NULL ,
  `first_name` VARCHAR(45) NOT NULL ,
  `gender` ENUM('M', 'F') NULL DEFAULT NULL ,
  `address` VARCHAR(100) NULL DEFAULT NULL ,
  `city` VARCHAR(50) NULL DEFAULT NULL ,
  `postal_code` VARCHAR(10) NULL DEFAULT NULL ,
  `country` CHAR(2) NULL DEFAULT NULL ,
  `phone_mobile` VARCHAR(20) NULL DEFAULT NULL ,
  `phone_home` VARCHAR(20) NULL DEFAULT NULL ,
  `date_of_birth` DATE NULL DEFAULT NULL ,
  `occupation` VARCHAR(45) NULL DEFAULT NULL ,
  `active` TINYINT NOT NULL DEFAULT 0 ,
  `last_login` DATETIME NULL DEFAULT NULL ,
  `created_on` DATETIME NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  INDEX `idx_fk_email` (`email` ASC) ,
  PRIMARY KEY (`user_id`) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) )
ENGINE = MyISAM
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`users_activity`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users_activity` (
  `user_id` int(11) NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`) )
ENGINE=MEMORY
DEFAULT CHARSET=latin1;


-- -----------------------------------------------------
-- Table `investiclub`.`members`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`members` (
  `member_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `club_id` MEDIUMINT UNSIGNED NOT NULL ,
  `user_id` INT(11) UNSIGNED NOT NULL ,
  `role` VARCHAR(65) NOT NULL ,
  `enrollement_date` DATE NOT NULL ,
  `departure_date` DATE NULL DEFAULT NULL ,
  `admin` TINYINT NOT NULL DEFAULT FALSE ,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'inactive' ,
  `pending` TINYINT NOT NULL DEFAULT TRUE ,
  `deleted_on` DATE NULL DEFAULT NULL ,
  `created_on` DATETIME NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`member_id`) ,
  UNIQUE INDEX `uq_idx_club_id_user_id` (`club_id` ASC, `user_id` ASC) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`logs`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`logs` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `priority` SMALLINT NOT NULL ,
  `priority_name` VARCHAR(45) NULL DEFAULT NULL ,
  `message` VARCHAR(255) NOT NULL ,
  `user_id` INT UNSIGNED NULL DEFAULT NULL ,
  `stack_trace` LONGTEXT NULL DEFAULT NULL ,
  `timestamp` TIMESTAMP NOT NULL ,
  `ip` VARCHAR(16) NULL DEFAULT NULL ,
  PRIMARY KEY (`log_id`) )
ENGINE = MyISAM
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`stats_cache_session`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`stats_cache_session` (
  `session_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`session_id`) )
ENGINE = MyISAM
AUTO_INCREMENT = 72
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`stats_cache`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`stats_cache` (
  `stat_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `session_id` INT(11) NOT NULL ,
  `validity` INT(11) NOT NULL ,
  `scope` VARCHAR(32) NOT NULL ,
  `key` VARCHAR(45) NOT NULL ,
  `full_key` VARCHAR(45) NOT NULL ,
  `size` MEDIUMINT NOT NULL ,
  `action` VARCHAR(45) NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`stat_id`) )
ENGINE = MyISAM
AUTO_INCREMENT = 546
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`treasury_balance_sheet`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`treasury_balance_sheet` (
  `balance_sheet_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `club_id` MEDIUMINT UNSIGNED NOT NULL ,
  `balance` FLOAT NOT NULL ,
  `portfolio` FLOAT NOT NULL ,
  `profit` FLOAT NOT NULL ,
  `total_profit` FLOAT NOT NULL ,
  `unit` FLOAT NOT NULL ,
  `unit_nb` FLOAT NOT NULL ,
  `status` ENUM('ongoing', 'closed', 'valid', 'validation') NOT NULL ,
  `date` DATE NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`balance_sheet_id`) )
ENGINE = MyISAM
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`stocks`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`stocks` (
  `stock_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `symbol` VARCHAR(65) NOT NULL ,
  `name` VARCHAR(155) NOT NULL ,
  `stock_exchange` VARCHAR(155) NULL DEFAULT NULL ,
  `currency` CHAR(3) NOT NULL ,
  `is_default` TINYINT NOT NULL DEFAULT FALSE ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`stock_id`) ,
  INDEX `uq_symbol_currency` (`symbol` ASC, `currency` ASC) ,
  UNIQUE INDEX `symbol_UNIQUE` (`symbol` ASC) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`treasury_transactions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`treasury_transactions` (
  `transaction_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `club_id` MEDIUMINT UNSIGNED NOT NULL ,
  `stock_id` INT UNSIGNED NOT NULL ,
  `type` ENUM('buy', 'sell', 'dividend') NOT NULL ,
  `shares` MEDIUMINT UNSIGNED NOT NULL ,
  `price` FLOAT NOT NULL ,
  `fees` FLOAT NOT NULL ,
  `date` DATE NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`transaction_id`) )
ENGINE = MyISAM
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`treasury_capital_users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`treasury_capital_users` (
  `capital_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `member_id` INT UNSIGNED NOT NULL ,
  `balance_sheet_id` INT UNSIGNED NOT NULL ,
  `paid` FLOAT NOT NULL ,
  `value` FLOAT NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`capital_id`) ,
  UNIQUE INDEX `uq_idx_balance_sheet_id_club_user_id` (`member_id` ASC, `balance_sheet_id` ASC) )
ENGINE = MyISAM
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`club_settings`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`club_settings` (
  `club_id` MEDIUMINT UNSIGNED NOT NULL ,
  `min_members` SMALLINT NULL DEFAULT 0 ,
  `max_members` SMALLINT NULL DEFAULT 0 ,
  `transaction_fee_flat` FLOAT NULL DEFAULT 0 ,
  `transaction_fee_percent` FLOAT NULL DEFAULT 0 ,
  `min_contribution` FLOAT NULL DEFAULT 0 ,
  `max_contribution` FLOAT NULL DEFAULT 0 ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`club_id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`user_permissions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`user_permissions` (
  `user_id` INT UNSIGNED NOT NULL ,
  `permission_id` MEDIUMINT UNSIGNED NOT NULL ,
  `is_allowed` TINYINT NULL DEFAULT FALSE ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`user_id`, `permission_id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`treasury_cashflow`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`treasury_cashflow` (
  `cashflow_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `club_id` MEDIUMINT UNSIGNED NOT NULL ,
  `club_user_id` INT UNSIGNED NULL DEFAULT NULL ,
  `type` ENUM('debit', 'credit') NOT NULL ,
  `value` FLOAT NULL ,
  `comment` VARCHAR(255) NULL ,
  `date` DATE NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`cashflow_id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`treasury_departure`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`treasury_departure` (
  `departure_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `member_id` INT UNSIGNED NOT NULL ,
  `club_id` MEDIUMINT UNSIGNED NOT NULL ,
  `date` DATE NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`departure_id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`treasury_revaluation`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`treasury_revaluation` (
  `revaluation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `club_id` MEDIUMINT UNSIGNED NOT NULL ,
  `value` FLOAT NOT NULL ,
  `comment` VARCHAR(255) NOT NULL ,
  `date` DATE NOT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`revaluation_id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`treasury_transac_cash`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`treasury_transac_cash` (
  `transac_cash_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `transaction_id` INT(11) UNSIGNED NOT NULL ,
  `cashflow_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`transac_cash_id`) ,
  UNIQUE INDEX `uq_transaction_id_cashflow_id` (`transaction_id` ASC, `cashflow_id` ASC) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`notifications`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`notifications` (
  `notification_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `recipient_id` INT UNSIGNED NOT NULL ,
  `sender_id` INT UNSIGNED NULL DEFAULT NULL ,
  `notification_type` ENUM('error', 'warning', 'success', 'user','club') NOT NULL ,
  `json` longtext NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`notification_id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`quotes_historical`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`quotes_historical` (
  `symbol` VARCHAR(45) NOT NULL ,
  `date` DATE NOT NULL ,
  `open` FLOAT NULL DEFAULT NULL ,
  `close` FLOAT NULL DEFAULT NULL ,
  `high` FLOAT NULL DEFAULT NULL ,
  `low` FLOAT NULL DEFAULT NULL ,
  `volume` BIGINT NULL DEFAULT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`symbol`, `date`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `investiclub`.`quotes_live`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`quotes_live` (
  `symbol` VARCHAR(45) NOT NULL ,
  `last_trade` FLOAT NULL DEFAULT NULL ,
  `ask` FLOAT NULL DEFAULT NULL ,
  `bid` FLOAT NULL DEFAULT NULL ,
  `datetime` DATETIME NULL DEFAULT NULL ,
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`symbol`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `investiclub`.`datastore_files_meta`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `investiclub`.`datastore_files_meta` (
  `datastore_files_meta_id` INT NOT NULL AUTO_INCREMENT ,
  `club_id` INT NOT NULL ,
  `create_member_id` INT NOT NULL ,
  `create_date` TIMESTAMP NOT NULL ,
  `path` VARCHAR(128) NOT NULL ,
  `type` ENUM('default', 'ivc') NOT NULL ,
  `size` INT NOT NULL ,
  PRIMARY KEY (`datastore_files_meta_id`) ,
  INDEX `path_idx` (`path` ASC) )
ENGINE = MyISAM;


--
-- Table structure for table `newsletter`
--


CREATE TABLE IF NOT EXISTS `newsletter` (
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Structure de la table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` int(10) unsigned NOT NULL,
  `recipient_id` int(10) unsigned NOT NULL,
  `namespace` enum('general','portfolio') NOT NULL,
  `subject` varchar(1024) NOT NULL DEFAULT 'No subject',
  `message` longtext NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=57 ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

--
-- Contenu de la table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `recipient_id`, `namespace`, `subject`, `message`, `timestamp`, `deleted`) VALUES
(1, 110, 100, 'general', 'Réunion club demain soir', 'Salut,\n\nIl y a une réunion club demain soir, on analysera principalement les valeurs ALTEN et ILIAD pour définir notre stratégie dans le secteur technologique.\n\nMerci de me tenir au courant si tu ne peux pas être là,\nUser A', '2013-01-20 16:46:49', 0),
(2, 112, 110, 'general', 'A vendre d\'urgence: APPL', 'L\action APPLE à droppée de $20 à l\'ouverture, il faut réagir vite. Réunion demain soir à la salle Kerviel.', '2013-01-18 12:41:39', 0),
(3, 101, 110, 'general', 'No subject', 'Tu es où ? On a réunion dans 5 minutes sur Skype.', '2013-01-12 11:59:33', 0),
(4, 110, 102, 'general', 'Décision du 12 décembre dernier', 'Il a été décidé d\'acheter du BX4 en ce début d\année.', '2013-01-11 22:12:10', 0),
(5, 100, 110, 'general', 'Bonne année', 'Je te souhaite une excellente année 2013!', '2013-01-02 09:16:39', 0);


-- -----------------------------------------------------
-- Data for table `investiclub`.`brokers`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`brokers` (`broker_id`, `name`, `url`, `country`, `is_default`, `last_update`) VALUES (1, 'Fortuneo', 'http://www.fortuneo.fr', 'FR', 1, '2012-01-01 04:34:42');
INSERT INTO `investiclub`.`brokers` (`broker_id`, `name`, `url`, `country`, `is_default`, `last_update`) VALUES (2, 'Boursorama', 'http://www.boursorama.com', 'FR', 1, '2012-01-01 04:34:42');
INSERT INTO `investiclub`.`brokers` (`broker_id`, `name`, `url`, `country`, `is_default`, `last_update`) VALUES (3, 'Soci�t� G�n�rale', 'http://particuliers.societegenerale.fr', 'FR', 1, '2012-01-01 04:34:42');
INSERT INTO `investiclub`.`brokers` (`broker_id`, `name`, `url`, `country`, `is_default`, `last_update`) VALUES (4, 'BNP Paribas', 'http://www.bnpparibas.net', 'FR', 1, '2012-01-01 04:34:42');
INSERT INTO `investiclub`.`brokers` (`broker_id`, `name`, `url`, `country`, `is_default`, `last_update`) VALUES (5, 'Banque Populaire', 'http://www.banquepopulaire.fr', 'FR', 1, '2012-01-01 04:34:42');
INSERT INTO `investiclub`.`brokers` (`broker_id`, `name`, `url`, `country`, `is_default`, `last_update`) VALUES (6, 'CIC', 'https://www.cic.fr/fr/', 'FR', 1, '2012-01-01 04:34:42');

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`clubs`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`clubs` (`club_id`, `name`, `broker_id`, `country`, `currency`, `registration_date`, `dissolution_date`, `active`, `created_on`, `last_update`) VALUES (10, 'PortfolioTest', 1, 'FR', 'EUR', '2012-01-01', NULL, 1, '2012-01-01 04:34:42', NULL);
INSERT INTO `investiclub`.`clubs` (`club_id`, `name`, `broker_id`, `country`, `currency`, `registration_date`, `dissolution_date`, `active`, `created_on`, `last_update`) VALUES (11, 'Americana', 2, 'US', 'USD', '2012-01-01', NULL, 1, '2012-01-01 04:34:42', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`user_settings`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (100, 'en_US', 'EUR', 'Asia/Chongqing', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (101, 'en_US', 'EUR', 'Asia/Shanghai', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (102, 'en_US', 'USD', 'America/New_York', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (103, 'en_US', 'EUR', 'Europe/Paris', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (104, 'en_US', 'GBP', 'Europe/London', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (110, 'en_US', 'EUR', 'Europe/Paris', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (111, 'en_US', 'EUR', 'Europe/Paris', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (112, 'en_US', 'EUR', 'Europe/Paris', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (113, 'en_US', 'EUR', 'Europe/Paris', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (114, 'en_US', 'EUR', 'Europe/Paris', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (115, 'en_US', 'EUR', 'Europe/Paris', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (116, 'en_US', 'EUR', 'Europe/Paris', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (117, 'en_US', 'EUR', 'Europe/Paris', NULL);
INSERT INTO `investiclub`.`user_settings` (`user_id`, `locale`, `currency`, `timezone`, `last_update`) VALUES (118, 'en_US', 'EUR', 'Europe/Paris', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`users`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (100, 'alex@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'Esser', 'Alexandre', 'M', '1 Jiaotong Daxue Road', 'Beijing', '400040', 'CN', '86-18610014352', NULL, '1988-12-10', 'Engineer', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (101, 'jonathan@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'Hickson', 'Jonathan', 'M', NULL, NULL, NULL, 'CN', '86-18610014349', NULL, '1970-01-01', 'Student', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (102, 'us@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'Doe', 'John', 'M', NULL, NULL, NULL, 'US', NULL, NULL, '1970-01-01', NULL, 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (103, 'fr@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'De La For�t', 'Jean', 'M', NULL, NULL, NULL, 'FR', NULL, NULL, '2008-01-01', NULL, 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (104, 'uk@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'Brown', 'Eva', 'F', NULL, NULL, NULL, 'UK', NULL, NULL, '1920-01-01', NULL, 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (110, 'a@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'A', 'User', 'M', NULL, NULL, NULL, 'FR', NULL, NULL, '', 'Tester', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (111, 'b@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'B', 'User', 'M', NULL, NULL, NULL, 'FR', NULL, NULL, NULL, 'Tester', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (112, 'c@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'C', 'User', 'F', NULL, NULL, NULL, 'FR', NULL, NULL, NULL, 'Tester', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (113, 'd@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'D', 'User', 'F', NULL, NULL, NULL, 'FR', NULL, NULL, '', 'Tester', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (114, 'e@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'E', 'User', 'M', NULL, NULL, NULL, 'FR', NULL, NULL, NULL, 'Tester', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (115, 'f@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'F', 'User', 'F', NULL, NULL, NULL, 'FR', NULL, NULL, NULL, 'Tester', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (116, 'g@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'G', 'User', 'F', NULL, NULL, NULL, 'FR', NULL, NULL, '', 'Tester', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (117, 'h@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'H', 'User', 'M', NULL, NULL, NULL, 'FR', NULL, NULL, NULL, 'Tester', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (118, 'i@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'I', 'User', 'M', NULL, NULL, NULL, 'FR', NULL, NULL, '', 'Tester', 1, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`users` (`user_id`, `email`, `password`, `last_name`, `first_name`, `gender`, `address`, `city`, `postal_code`, `country`, `phone_mobile`, `phone_home`, `date_of_birth`, `occupation`, `active`, `last_login`, `created_on`, `last_update`) VALUES (119, 'j@investiclub.net', '8690c0c10bc1b38ecef4eeab26170da09e6e6dca2990274dd1bc72469b346ebff6dc301a14488a0ff8e500cc94d3d67b7038a7347b14a93e7570a42e634bc63a', 'J', 'User', 'M', NULL, NULL, NULL, 'FR', NULL, NULL, NULL, 'Tester', 1, NULL, '2012-01-06 04:34:42', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`members`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (1, 10, 110, 'president', '2011-12-10', NULL, 1, 'active', FALSE, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (2, 10, 111, 'treasurer', '2011-12-10', NULL, 0, 'active', FALSE, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (3, 10, 112, 'secretary', '2011-12-10', NULL, 0, 'active', FALSE, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (4, 10, 113, 'member', '2011-12-10', NULL, 0, 'active', FALSE, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (5, 10, 114, 'member', '2011-12-10', NULL, 0, 'active', FALSE, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (6, 10, 115, 'member', '2011-12-10', NULL, 0, 'active', FALSE, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (7, 10, 116, 'member', '2011-12-10', NULL, 0, 'active', FALSE, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (8, 10, 117, 'member', '2011-12-10', NULL, 0, 'active', FALSE, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (9, 10, 118, 'member', '2011-12-10', NULL, 0, 'active', FALSE, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (10, 10, 119, 'member', '2011-12-10', NULL, 0, 'active',FALSE, NULL, '2012-01-06 04:34:42', NULL);
INSERT INTO `investiclub`.`members` (`member_id`, `club_id`, `user_id`, `role`, `enrollement_date`, `departure_date`, `admin`, `status`, `pending`, `deleted_on`, `created_on`, `last_update`) VALUES (11, 11, 102, 'president', '2011-12-10', NULL, 1, 'active', FALSE, NULL, '2012-01-06 04:34:42', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`logs`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`logs` (`log_id`, `priority`, `priority_name`, `message`, `user_id`, `stack_trace`, `timestamp`, `ip`) VALUES (1, 3, 'CRIT', 'Db fail', 101, NULL, '2012-01-06 04:34:42', '8.8.8.8');

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`stocks`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`stocks` (`stock_id`, `symbol`, `name`, `stock_exchange`, `currency`, `is_default`, `last_update`) VALUES (1, 'ILD.PA', 'Iliad S.A', 'Paris', 'EUR', 1, NULL);
INSERT INTO `investiclub`.`stocks` (`stock_id`, `symbol`, `name`, `stock_exchange`, `currency`, `is_default`, `last_update`) VALUES (2, 'ALO.PA', 'Alstom', 'Paris', 'EUR', 1, NULL);
INSERT INTO `investiclub`.`stocks` (`stock_id`, `symbol`, `name`, `stock_exchange`, `currency`, `is_default`, `last_update`) VALUES (3, 'IVC.PA', 'InvestiClub', 'Paris', 'EUR', 0, NULL);
INSERT INTO `investiclub`.`stocks` (`stock_id`, `symbol`, `name`, `stock_exchange`, `currency`, `is_default`, `last_update`) VALUES (5, 'actionZ', 'actionZ', 'Paris', 'EUR', 0, NULL);
INSERT INTO `investiclub`.`stocks` (`stock_id`, `symbol`, `name`, `stock_exchange`, `currency`, `is_default`, `last_update`) VALUES (6, 'actionX', 'actionX', 'Paris', 'EUR', 0, NULL);
INSERT INTO `investiclub`.`stocks` (`stock_id`, `symbol`, `name`, `stock_exchange`, `currency`, `is_default`, `last_update`) VALUES (7, 'actionY', 'actionY', 'Paris', 'EUR', 0, NULL);
INSERT INTO `investiclub`.`stocks` (`stock_id`, `symbol`, `name`, `stock_exchange`, `currency`, `is_default`, `last_update`) VALUES (8, 'actionP', 'actionP', 'Paris', 'EUR', 0, NULL);
INSERT INTO `investiclub`.`stocks` (`stock_id`, `symbol`, `name`, `stock_exchange`, `currency`, `is_default`, `last_update`) VALUES (9, 'actionQ', 'actionQ', 'Paris', 'EUR', 0, NULL);
INSERT INTO `investiclub`.`stocks` (`stock_id`, `symbol`, `name`, `stock_exchange`, `currency`, `is_default`, `last_update`) VALUES (10, 'actionR', 'actionR', 'Paris', 'EUR', 0, NULL);
INSERT INTO `investiclub`.`stocks` (`stock_id`, `symbol`, `name`, `stock_exchange`, `currency`, `is_default`, `last_update`) VALUES (11, 'actionS', 'actionS', 'Paris', 'EUR', 0, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`treasury_transactions`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (1, 10, 5, 'buy', 10, 115, 0, '2011-01-02', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (2, 10, 6, 'buy', 5, 308, 0, '2011-02-06', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (3, 10, 7, 'buy', 10, 77, 0, '2011-02-06', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (4, 10, 8, 'buy', 30, 104, 0, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (5, 10, 9, 'buy', 20, 51, 0, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (6, 10, 10, 'buy', 10, 123, 0, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (7, 10, 11, 'buy', 5, 228, 0, '2011-04-28', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (8, 10, 5, 'dividend', 1, 50, 0, '2011-03-07', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (23, 10, 6, 'sell', 5, 692, 0, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (24, 10, 5, 'sell', 10, 115, 0, '2011-04-02', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (25, 10, 7, 'sell', 10, 77, 0, '2011-04-02', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (65, 10, 1, 'buy', 5, 308, 10, '2011-04-06', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (66, 10, 1, 'sell', 2, 310, 0, '2011-04-09', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (67, 10, 1, 'buy', 3, 306, 0, '2011-04-15', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (68, 10, 1, 'sell', 4, 312, 10, '2011-04-18', NULL);
INSERT INTO `investiclub`.`treasury_transactions` (`transaction_id`, `club_id`, `stock_id`, `type`, `shares`, `price`, `fees`, `date`, `last_update`) VALUES (69, 10, 1, 'buy', 1, 308, 8, '2011-04-20', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`club_settings`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`club_settings` (`club_id`, `min_members`, `max_members`, `transaction_fee_flat`, `transaction_fee_percent`, `min_contribution`, `max_contribution`, `last_update`) VALUES (10, 5, 20, 3, 1, NULL, NULL, '2012-01-01 04:34:42');
INSERT INTO `investiclub`.`club_settings` (`club_id`, `min_members`, `max_members`, `transaction_fee_flat`, `transaction_fee_percent`, `min_contribution`, `max_contribution`, `last_update`) VALUES (11, 0, 0, 0, 0, 25, 400, '2012-01-01 04:34:42');

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`treasury_cashflow`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (1, 10, 1, 'credit', 185, NULL, '2011-01-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (2, 10, 2, 'credit', 185, NULL, '2011-01-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (3, 10, 3, 'credit', 185, NULL, '2011-01-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (4, 10, 4, 'credit', 185, NULL, '2011-01-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (5, 10, 5, 'credit', 185, NULL, '2011-01-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (6, 10, 6, 'credit', 185, NULL, '2011-01-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (7, 10, 7, 'credit', 185, NULL, '2011-01-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (8, 10, 8, 'credit', 185, NULL, '2011-01-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (9, 10, 9, 'credit', 185, NULL, '2011-01-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (10, 10, 10, 'credit', 185, NULL, '2011-01-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (11, 10, 1, 'credit', 185, NULL, '2011-02-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (12, 10, 2, 'credit', 185, NULL, '2011-02-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (13, 10, 3, 'credit', 185, NULL, '2011-02-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (14, 10, 4, 'credit', 185, NULL, '2011-02-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (15, 10, 5, 'credit', 185, NULL, '2011-02-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (16, 10, 6, 'credit', 185, NULL, '2011-02-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (17, 10, 7, 'credit', 185, NULL, '2011-02-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (18, 10, 8, 'credit', 185, NULL, '2011-02-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (19, 10, 9, 'credit', 185, NULL, '2011-02-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (20, 10, 10, 'credit', 185, NULL, '2011-02-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (21, 10, 1, 'credit', 185, NULL, '2011-03-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (22, 10, 2, 'credit', 185, NULL, '2011-03-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (23, 10, 3, 'credit', 185, NULL, '2011-03-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (24, 10, 4, 'credit', 185, NULL, '2011-03-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (25, 10, 5, 'credit', 185, NULL, '2011-03-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (26, 10, 6, 'credit', 185, NULL, '2011-03-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (27, 10, 7, 'credit', 185, NULL, '2011-03-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (28, 10, 8, 'credit', 185, NULL, '2011-03-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (29, 10, 9, 'credit', 185, NULL, '2011-03-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (30, 10, 10, 'credit', 185, NULL, '2011-03-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (31, 10, 1, 'debit', NULL, NULL, '2011-04-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (32, 10, 2, 'debit', NULL, NULL, '2011-04-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (33, 10, 3, 'debit', NULL, NULL, '2011-04-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (34, 10, 4, 'debit', NULL, NULL, '2011-04-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (35, 10, 5, 'debit', NULL, NULL, '2011-04-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (36, 10, 6, 'debit', NULL, NULL, '2011-04-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (37, 10, 7, 'debit', NULL, NULL, '2011-04-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (38, 10, 8, 'debit', NULL, NULL, '2011-04-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (39, 10, 9, 'debit', NULL, NULL, '2011-04-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (40, 10, 10, 'debit', NULL, NULL, '2011-04-01', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (41, 10, NULL, 'credit', 200, 'ajout comme ca', '2011-02-20', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (42, 10, NULL, 'debit', 45, 'paiement IVC', '2011-04-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (43, 10, 1, 'credit', 185, NULL, '2011-04-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (44, 10, 2, 'credit', 185, NULL, '2011-04-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (45, 10, 3, 'credit', 185, NULL, '2011-04-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (46, 10, 4, 'credit', 185, NULL, '2011-04-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (47, 10, 5, 'credit', 185, NULL, '2011-04-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (48, 10, 6, 'credit', 185, NULL, '2011-04-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (49, 10, 7, 'credit', 185, NULL, '2011-04-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (50, 10, 8, 'credit', 185, NULL, '2011-04-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (51, 10, 9, 'credit', 185, NULL, '2011-04-05', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (74, 10, 1, 'credit', NULL, NULL, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (75, 10, 2, 'credit', NULL, NULL, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (76, 10, 3, 'credit', NULL, NULL, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (77, 10, 4, 'credit', NULL, NULL, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (78, 10, 5, 'credit', NULL, NULL, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (79, 10, 6, 'credit', NULL, NULL, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (80, 10, 7, 'credit', NULL, NULL, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (81, 10, 8, 'credit', NULL, NULL, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (82, 10, 9, 'credit', NULL, NULL, '2011-03-08', NULL);
INSERT INTO `investiclub`.`treasury_cashflow` (`cashflow_id`, `club_id`, `club_user_id`, `type`, `value`, `comment`, `date`, `last_update`) VALUES (83, 10, 10, 'credit', NULL, NULL, '2011-03-08', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`treasury_departure`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`treasury_departure` (`departure_id`, `member_id`, `club_id`, `date`, `last_update`) VALUES (1, 10, 10, '2011-04-03', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`treasury_revaluation`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`treasury_revaluation` (`revaluation_id`, `club_id`, `value`, `comment`, `date`, `last_update`) VALUES (1, 10, 274, 'reev portfolio', '2011-04-03', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `investiclub`.`treasury_transac_cash`
-- -----------------------------------------------------
START TRANSACTION;
USE `investiclub`;
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (1, 8, 31);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (2, 8, 32);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (3, 8, 33);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (4, 8, 34);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (5, 8, 35);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (6, 8, 36);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (7, 8, 37);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (8, 8, 38);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (9, 8, 39);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (10, 8, 40);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (11, 23, 74);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (12, 23, 75);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (13, 23, 76);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (14, 23, 77);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (15, 23, 78);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (16, 23, 79);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (17, 23, 80);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (18, 23, 81);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (19, 23, 82);
INSERT INTO `investiclub`.`treasury_transac_cash` (`transac_cash_id`, `transaction_id`, `cashflow_id`) VALUES (20, 23, 83);

COMMIT;
