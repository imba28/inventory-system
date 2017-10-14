-- MySQL dump 10.13  Distrib 5.7.19, for Linux (x86_64)
--
-- Host: localhost    Database: av
-- ------------------------------------------------------
-- Server version	5.7.19-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `av_actions`
--

DROP TABLE IF EXISTS `av_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `av_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rentDate` datetime DEFAULT NULL,
  `returnDate` datetime DEFAULT NULL,
  `expectedReturnDate` datetime DEFAULT NULL,
  `createDate` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `stamp` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `CUSTOMER` (`customer_id`),
  KEY `PRODUCT` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `av_actions`
--

LOCK TABLES `av_actions` WRITE;
/*!40000 ALTER TABLE `av_actions` DISABLE KEYS */;
INSERT INTO `av_actions` VALUES (1,3,10,'2017-10-14 01:25:58',NULL,'2017-10-20 00:00:00','2017-10-14 01:25:58',1,NULL,'0'),(3,1,10,'2017-10-14 02:25:03',NULL,NULL,'2017-10-14 02:25:03',1,NULL,'0'),(4,6,10,'2017-10-14 02:40:58',NULL,NULL,'2017-10-14 02:40:58',1,NULL,'0');
/*!40000 ALTER TABLE `av_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `av_customers`
--

DROP TABLE IF EXISTS `av_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `av_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `internal_id` varchar(100) DEFAULT NULL,
  `createDate` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `stamp` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `internal_id_UNIQUE` (`internal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `av_customers`
--

LOCK TABLES `av_customers` WRITE;
/*!40000 ALTER TABLE `av_customers` DISABLE KEYS */;
INSERT INTO `av_customers` VALUES (1,'Otto Normal','aaa','fhs39877','2017-10-09 20:52:15',1,NULL,'0'),(10,'Lukas Gruber',NULL,'fhs39827','2017-10-14 01:19:10',1,NULL,'0');
/*!40000 ALTER TABLE `av_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `av_products`
--

DROP TABLE IF EXISTS `av_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `av_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  `invNr` varchar(45) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `note` text,
  `description` text,
  `condition` varchar(50) DEFAULT NULL,
  `user_id` varchar(45) DEFAULT NULL,
  `createDate` datetime NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL,
  `deleted` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `av_products`
--

LOCK TABLES `av_products` WRITE;
/*!40000 ALTER TABLE `av_products` DISABLE KEYS */;
INSERT INTO `av_products` VALUES (1,'HTC Vive',NULL,'HMD','','','neu','1','2017-10-09 20:52:15',NULL,'0'),(3,'Playstation 4',NULL,'Konsole','Nur ein Controller dabei.','Lieferumfang: PlayStation 4 1TB, Blu-ray Version von EA SPORTS™ FIFA 18, 2x Jet Black DUALSHOCK 4 Wireless Controller, HDMI Kabel, USB Kabel, Mono Headset, FIFA Ultimate Team™ Rare Players Pack und FIFA Ultimate Team™ ICON Spieler Voucher Code ','aa','1','2017-10-09 21:05:00',NULL,'0'),(4,'Playstation 3',NULL,'Konsole',NULL,NULL,NULL,'1','2017-10-09 21:05:47',NULL,'0'),(5,'Playstation 3',NULL,'Konsole',NULL,NULL,NULL,'1','2017-10-09 21:11:40',NULL,'0'),(6,'iPhone 5s',NULL,'Smartphone',NULL,NULL,NULL,'1','2017-10-13 21:11:40',NULL,'0');
/*!40000 ALTER TABLE `av_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `av_users`
--

DROP TABLE IF EXISTS `av_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `av_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `createDate` datetime NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `av_users`
--

LOCK TABLES `av_users` WRITE;
/*!40000 ALTER TABLE `av_users` DISABLE KEYS */;
INSERT INTO `av_users` VALUES (1,'lg','keins','Lukas Gruber','lgruber.mmt-b2016@fh-salzburg.ac.at','2017-10-11 21:54:00','2017-10-11 19:54:56','0');
/*!40000 ALTER TABLE `av_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-10-14  2:55:24
