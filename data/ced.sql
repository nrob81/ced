# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.38-0+wheezy1)
# Database: fish
# Generation Time: 2014-08-01 21:01:35 +0200
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table account
# ------------------------------------------------------------

CREATE TABLE `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(16) DEFAULT NULL,
  `email` varchar(128) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  `registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verified` timestamp NULL DEFAULT NULL,
  `verifyCode` varchar(128) NOT NULL DEFAULT '',
  `resetPasswordCode` varchar(128) NOT NULL DEFAULT '',
  `changeMailCode` varchar(128) NOT NULL DEFAULT '',
  `emailTemp` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table baits
# ------------------------------------------------------------

CREATE TABLE `baits` (
  `id` mediumint(4) NOT NULL AUTO_INCREMENT,
  `skill` mediumint(4) NOT NULL DEFAULT '0',
  `level` mediumint(3) NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL DEFAULT '?',
  `price` mediumint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table challenge
# ------------------------------------------------------------

CREATE TABLE `challenge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caller` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `opponent` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `loot_caller` int(11) unsigned NOT NULL DEFAULT '0',
  `loot_opponent` int(11) unsigned NOT NULL DEFAULT '0',
  `cnt_won_caller` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `cnt_won_opponent` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `point_caller` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `point_opponent` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `name_caller` varchar(20) NOT NULL DEFAULT '',
  `name_opponent` varchar(20) NOT NULL DEFAULT '',
  `winner` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `c_o` (`caller`,`opponent`),
  KEY `winner` (`winner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table club
# ------------------------------------------------------------

CREATE TABLE `club` (
  `id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT,
  `owner` mediumint(6) NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL DEFAULT '?',
  `would_compete` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`),
  KEY `name` (`name`),
  KEY `would_compete` (`would_compete`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `add_club` AFTER INSERT ON `club` FOR EACH ROW UPDATE main SET in_club=new.id WHERE uid=new.owner */;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `remove_club` AFTER DELETE ON `club` FOR EACH ROW UPDATE main SET in_club=0 WHERE uid=old.owner */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table club_members
# ------------------------------------------------------------

CREATE TABLE `club_members` (
  `club_id` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `approved` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `club_uid` (`club_id`,`uid`),
  KEY `approved` (`approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table command_stack
# ------------------------------------------------------------

CREATE TABLE `command_stack` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `command` varchar(20) NOT NULL,
  `params` text NOT NULL,
  `process_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `process_time` (`process_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table duel
# ------------------------------------------------------------

CREATE TABLE `duel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `winner` enum('?','caller','opponent') NOT NULL DEFAULT '?',
  `caller` mediumint(6) NOT NULL DEFAULT '0',
  `opponent` mediumint(6) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `challenge_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `winner` (`winner`),
  KEY `caller` (`caller`),
  KEY `opponent` (`opponent`),
  KEY `challenge_id` (`challenge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `after_del` AFTER DELETE ON `duel` FOR EACH ROW DELETE FROM duel_player WHERE duel_id=old.id; */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table duel_player
# ------------------------------------------------------------

CREATE TABLE `duel_player` (
  `duel_id` int(11) unsigned NOT NULL,
  `role` enum('caller','opponent') NOT NULL DEFAULT 'caller',
  `uid` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `skill` int(10) unsigned NOT NULL DEFAULT '0',
  `chance` int(10) unsigned NOT NULL DEFAULT '0',
  `energy` int(10) unsigned NOT NULL DEFAULT '0',
  `dollar` int(10) unsigned NOT NULL DEFAULT '0',
  `req_energy` int(10) unsigned NOT NULL DEFAULT '0',
  `req_dollar` int(10) unsigned NOT NULL DEFAULT '0',
  `award_xp` int(10) unsigned NOT NULL DEFAULT '0',
  `award_dollar` int(10) unsigned NOT NULL DEFAULT '0',
  `duel_points` int(10) unsigned NOT NULL DEFAULT '0',
  `best_item` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `best_bait` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `winner` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `club` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`duel_id`,`role`),
  UNIQUE KEY `duelid_uid` (`duel_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table forum
# ------------------------------------------------------------

CREATE TABLE `forum` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `club_id` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `user` varchar(16) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `club_id` (`club_id`),
  KEY `private` (`private`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table items
# ------------------------------------------------------------

CREATE TABLE `items` (
  `id` mediumint(4) NOT NULL AUTO_INCREMENT,
  `skill` mediumint(4) NOT NULL DEFAULT '0',
  `level` mediumint(3) NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL DEFAULT '?',
  `price` mediumint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table itemsets
# ------------------------------------------------------------

CREATE TABLE `itemsets` (
  `id` smallint(2) NOT NULL AUTO_INCREMENT,
  `parts` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(50) NOT NULL DEFAULT '?',
  `price` mediumint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table log
# ------------------------------------------------------------

CREATE TABLE `log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('travel','level_up','setpart') DEFAULT NULL,
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `xp_all` int(7) unsigned NOT NULL DEFAULT '0',
  `xp_delta` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `level` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `energy_max` int(7) unsigned NOT NULL DEFAULT '0',
  `energy` int(7) unsigned NOT NULL DEFAULT '0',
  `skill` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `skill_extended` int(7) unsigned NOT NULL DEFAULT '0',
  `strength` int(7) unsigned NOT NULL DEFAULT '0',
  `dollar` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `gold` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `owned_items` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `owned_baits` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `traveled_to` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `found_setpart` varchar(50) DEFAULT NULL,
  `interval` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `type` (`type`),
  KEY `uid` (`uid`),
  KEY `interval` (`interval`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table log_counters
# ------------------------------------------------------------

CREATE TABLE `log_counters` (
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `level` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `mission_success` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `mission_gate_success` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `mission_fail` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `mission_gate_fail` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `duel_success` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `duel_fail` mediumint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table main
# ------------------------------------------------------------

CREATE TABLE `main` (
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `user` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `xp_all` int(7) unsigned NOT NULL DEFAULT '0',
  `xp_delta` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `xp_recommended` mediumint(6) unsigned NOT NULL DEFAULT '10',
  `level` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `status_points` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `energy_max` int(7) unsigned NOT NULL DEFAULT '30',
  `energy_incr_at` datetime NOT NULL,
  `energy` int(7) unsigned NOT NULL DEFAULT '0',
  `skill` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `skill_extended` int(7) unsigned NOT NULL DEFAULT '2',
  `strength` int(7) unsigned NOT NULL DEFAULT '30',
  `dollar` int(7) NOT NULL DEFAULT '0',
  `gold` mediumint(4) unsigned NOT NULL DEFAULT '50',
  `last_location` mediumint(3) unsigned NOT NULL DEFAULT '1',
  `owned_items` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `owned_baits` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `found_setitem_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `found_setitem_xp` int(7) unsigned NOT NULL DEFAULT '0',
  `duel_points` int(7) unsigned NOT NULL DEFAULT '0',
  `tutorial_mission` smallint(1) unsigned NOT NULL DEFAULT '0',
  `in_club` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `black_market` datetime NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table missions
# ------------------------------------------------------------

CREATE TABLE `missions` (
  `id` mediumint(4) unsigned NOT NULL AUTO_INCREMENT,
  `water_id` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `gate` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `req_energy` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `req_bait_1` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `req_bait_1_count` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `req_bait_2` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `req_bait_2_count` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `award_xp` mediumint(4) unsigned NOT NULL,
  `award_dollar_min` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `award_dollar_max` mediumint(5) unsigned NOT NULL DEFAULT '1',
  `award_setpart` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `routine_gain` smallint(2) unsigned NOT NULL DEFAULT '9',
  `chance` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL DEFAULT '?',
  `txt` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `water_id` (`water_id`),
  KEY `gate` (`gate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table parts
# ------------------------------------------------------------

CREATE TABLE `parts` (
  `id` mediumint(4) NOT NULL AUTO_INCREMENT,
  `skill` mediumint(4) NOT NULL DEFAULT '0',
  `level` mediumint(3) NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL DEFAULT '?',
  `price` mediumint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table users_baits
# ------------------------------------------------------------

CREATE TABLE `users_baits` (
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `item_id` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `item_count` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `skill` mediumint(4) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `uid_iid` (`uid`,`item_id`),
  KEY `skill` (`skill`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `insert_baits` AFTER INSERT ON `users_baits` FOR EACH ROW UPDATE main SET owned_baits=owned_baits+new.item_count WHERE uid=new.uid */;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `update_baits` AFTER UPDATE ON `users_baits` FOR EACH ROW UPDATE main SET owned_baits=(SELECT SUM(item_count) FROM users_baits WHERE uid=new.uid) WHERE uid=new.uid */;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `delete_baits` AFTER DELETE ON `users_baits` FOR EACH ROW UPDATE main SET owned_baits=owned_baits-old.item_count WHERE uid=old.uid */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table users_items
# ------------------------------------------------------------

CREATE TABLE `users_items` (
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `item_id` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `item_count` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `skill` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `price` mediumint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`item_id`),
  KEY `skill` (`skill`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `insert` AFTER INSERT ON `users_items` FOR EACH ROW UPDATE main SET owned_items=owned_items+new.item_count WHERE uid=new.uid */;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `update` AFTER UPDATE ON `users_items` FOR EACH ROW UPDATE main SET owned_items=(SELECT SUM(item_count) FROM users_items WHERE uid=new.uid) WHERE uid=new.uid */;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `delete` AFTER DELETE ON `users_items` FOR EACH ROW UPDATE main SET owned_items=owned_items-old.item_count WHERE uid=old.uid */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table users_missions
# ------------------------------------------------------------

CREATE TABLE `users_missions` (
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `id` mediumint(4) unsigned NOT NULL,
  `water_id` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `routine` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `completed_count` mediumint(4) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `uid_id` (`uid`,`id`),
  KEY `water_id` (`water_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table users_parts
# ------------------------------------------------------------

CREATE TABLE `users_parts` (
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `item_id` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `item_count` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `skill` mediumint(4) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `uid_iid` (`uid`,`item_id`),
  KEY `skill` (`skill`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table visited
# ------------------------------------------------------------

CREATE TABLE `visited` (
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `water_id` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `routine` int(5) unsigned NOT NULL DEFAULT '0',
  `visit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `skill_extended_at_visit` int(7) unsigned NOT NULL DEFAULT '0',
  KEY `uid_water_id` (`uid`,`water_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table wall
# ------------------------------------------------------------

CREATE TABLE `wall` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(6) NOT NULL DEFAULT '0',
  `body` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table waters
# ------------------------------------------------------------

CREATE TABLE `waters` (
  `id` mediumint(3) unsigned NOT NULL AUTO_INCREMENT,
  `county_id` smallint(2) unsigned NOT NULL DEFAULT '0',
  `from` mediumint(3) unsigned NOT NULL,
  `from2` mediumint(3) unsigned NOT NULL,
  `title` varchar(50) NOT NULL DEFAULT '?',
  `position` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
