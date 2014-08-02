# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.38-0+wheezy1)
# Database: fish
# Generation Time: 2014-08-02 22:10:59 +0200
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table baits
# ------------------------------------------------------------

LOCK TABLES `baits` WRITE;
/*!40000 ALTER TABLE `baits` DISABLE KEYS */;

INSERT INTO `baits` (`id`, `skill`, `level`, `title`, `price`)
VALUES
	(1,1,0,'kukorica',3),
	(2,2,1,'csontkukac',6);

/*!40000 ALTER TABLE `baits` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table items
# ------------------------------------------------------------

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;

INSERT INTO `items` (`id`, `skill`, `level`, `title`, `price`)
VALUES
	(1,1,1,'kapanyél',6),
	(2,2,2,'seprűnyél',9),
	(3,2,4,'nádbot',10);

/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table itemsets
# ------------------------------------------------------------

LOCK TABLES `itemsets` WRITE;
/*!40000 ALTER TABLE `itemsets` DISABLE KEYS */;

INSERT INTO `itemsets` (`id`, `parts`, `title`, `price`)
VALUES
	(1,'1,2,3','Pontyozó szett',0),
	(2,'4,5,6','Csukázó szett',0);

/*!40000 ALTER TABLE `itemsets` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table missions
# ------------------------------------------------------------

LOCK TABLES `missions` WRITE;
/*!40000 ALTER TABLE `missions` DISABLE KEYS */;

INSERT INTO `missions` (`id`, `water_id`, `gate`, `req_energy`, `req_bait_1`, `req_bait_1_count`, `req_bait_2`, `req_bait_2_count`, `award_xp`, `award_dollar_min`, `award_dollar_max`, `award_setpart`, `routine_gain`, `chance`, `title`, `txt`)
VALUES
	(1,1,0,3,0,0,0,0,1,1,1,0,9,0,'Busás lakoma','Összeismerkedsz egy családdal, Kissékkel, akik a közelben nyaralnak. Amikor szóba kerül, hogy mennyire szeretik a busát, de sehol sem lehet kapni, felajánlod, hogy kifogsz nekik párat. Fogj 10 darab busát a családnak!'),
	(2,1,0,3,1,2,0,0,1,1,1,1,9,85,'Jól láttam?','Csak nem egy márnát láttál a vízben? Még soha életedben nem fogtál ki egyetlen márnát sem! Gyorsan fogj ki 15 halat, hátha az egyik márna lesz!'),
	(3,1,0,4,2,1,0,0,1,1,3,0,9,0,'Felszökő árak','A piacon most nagyon jól fizetnek a balinért, úgyhogy legjobb lesz, ha megragadod az alkalmat és kifogsz 30 balint, hogy bezsebelj egy rakás pénzt!'),
	(5,1,2,6,0,0,0,0,5,3,7,0,9,0,'Búcsú a márnáktól','Cimboráid meggyőznek, hogy menjetek át a Mrtvica-tóhoz, mert ott sokkal több halat fogtok fogni. Beleegyezel, ám előtte még gyorsan kifogsz 5 márnát.'),
	(6,2,0,3,0,0,0,0,1,1,1,0,9,80,'A titokzatos rablás','Arni reggel azzal ébreszt, hogy ellopták a csalijait. Könyörög, hogy segíts neki elfogni a tolvajt. Amikor beleegyezel, végre kiböki, hogy véleménye szerint a kősüllők művelték a dolgot. Sóhajtozva eleget teszel az ígéretednek és kifogsz 20 kősüllőt, hogy bebizonyítsd Arninak, hogy a halaknak nem szokásuk csalikat lopni.');

/*!40000 ALTER TABLE `missions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table parts
# ------------------------------------------------------------

LOCK TABLES `parts` WRITE;
/*!40000 ALTER TABLE `parts` DISABLE KEYS */;

INSERT INTO `parts` (`id`, `skill`, `level`, `title`, `price`)
VALUES
	(1,3,0,'Pontyozó tekerő',0),
	(2,3,10,'Pontyozó damil',0),
	(3,4,20,'Pontyozó bot',0),
	(4,5,30,'Csukázó tekerő',0),
	(5,5,40,'Csukázó damil',0),
	(6,6,50,'Csukázó bot',0);

/*!40000 ALTER TABLE `parts` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table waters
# ------------------------------------------------------------

LOCK TABLES `waters` WRITE;
/*!40000 ALTER TABLE `waters` DISABLE KEYS */;

INSERT INTO `waters` (`id`, `county_id`, `from`, `from2`, `title`, `position`)
VALUES
	(1,1,0,0,'Dráva','45.807504,17.82389'),
	(2,1,1,0,'Mrtvica-tó','45.847471,17.705559');

/*!40000 ALTER TABLE `waters` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
