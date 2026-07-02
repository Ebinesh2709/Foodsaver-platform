-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: foodsaver_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `businesses`
--

DROP TABLE IF EXISTS `businesses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `businesses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `businesses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `businesses`
--

LOCK TABLES `businesses` WRITE;
/*!40000 ALTER TABLE `businesses` DISABLE KEYS */;
INSERT INTO `businesses` VALUES (1,2,'hilton hotels','colombo','colombo 3','pastry items',NULL,'2026-06-23 21:49:19'),(2,3,'food','church road','wattala','onwoqnofq',NULL,'2026-06-25 09:15:00'),(3,6,'bun','35 bun','wattala','bakery',NULL,'2026-06-29 14:36:34'),(4,8,'Test Biz','123 Main Street','Colombo 3','A test business description',NULL,'2026-06-29 19:14:10');
/*!40000 ALTER TABLE `businesses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `food_listings`
--

DROP TABLE IF EXISTS `food_listings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `food_listings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('meals','bakery','produce','dairy','other') NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `discounted_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `pickup_start` datetime NOT NULL,
  `pickup_end` datetime NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('available','reserved','collected','sold_out','expired') DEFAULT 'available',
  `urgency_score` enum('high','medium','low') DEFAULT 'low',
  `ai_summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `business_id` (`business_id`),
  CONSTRAINT `food_listings_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `food_listings`
--

LOCK TABLES `food_listings` WRITE;
/*!40000 ALTER TABLE `food_listings` DISABLE KEYS */;
INSERT INTO `food_listings` VALUES (1,1,'rice','friedrice','meals',1000.00,600.00,10,'2026-10-10 11:11:00','2026-10-11 10:11:00','food_6a3afff7161af8.29758294.jpg','reserved','low',NULL,'2026-06-23 21:51:16'),(2,2,'chicken','kfc','meals',4000.00,3000.00,50,'2026-06-25 10:10:00','2026-06-27 11:11:00','food_6a3cf22e75fb02.36140179.png','reserved','medium','Fresh meals available at a great price — grab it before it\'s gone!','2026-06-25 09:17:34'),(3,3,'bun','iu3ft','bakery',1000.00,500.00,10,'2026-10-10 11:11:00','2026-10-11 10:10:00','food_6a42835a992a70.33096494.png','collected','low','Grab 1','2026-06-29 14:38:18'),(4,3,'fried rice','nice rice','meals',1000.00,700.00,50,'2026-06-29 11:11:00','2026-06-30 10:10:00',NULL,'reserved','medium','Grab delicious','2026-06-29 15:42:14'),(6,3,'fried chicken','chicken','meals',500.00,300.00,15,'2026-06-29 15:20:00','2026-07-02 18:20:00',NULL,'available','low','Grab delicious fried','2026-06-29 15:45:24'),(15,3,'noodles','spicy one','meals',100.00,50.00,20,'2026-06-29 10:10:00','2026-06-30 11:11:00','food_6a42a00e6f4e33.51330537.png','available','medium','Fresh meals available at a great price — grab it before it\'s gone!','2026-06-29 15:47:57'),(16,3,'fried rice','nice','meals',500.00,300.00,17,'2026-06-29 11:11:00','2026-06-30 11:11:00','food_6a429faecea227.46767843.png','collected','medium','Fresh meals available at a great price — grab it before it\'s gone!','2026-06-29 16:39:10'),(17,3,'cupcake','delious cup cake','bakery',120.00,100.00,11,'2026-06-29 22:56:00','2026-06-30 15:59:00',NULL,'available','medium','Fresh bakery available at a great price — grab it before it\'s gone!','2026-06-29 17:26:56');
/*!40000 ALTER TABLE `food_listings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `status` enum('pending','confirmed','collected','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `listing_id` (`listing_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `food_listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,1,1,1,'cancelled','2026-06-23 21:53:03'),(2,1,1,1,'pending','2026-06-23 21:53:17'),(3,2,4,1,'confirmed','2026-06-25 09:20:15'),(4,3,7,1,'collected','2026-06-29 14:41:09'),(5,4,4,1,'confirmed','2026-06-29 15:58:16'),(6,6,4,5,'confirmed','2026-06-29 16:30:55'),(7,16,4,3,'collected','2026-06-29 16:51:32'),(8,17,4,4,'pending','2026-06-29 18:53:29');
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('business','customer','admin') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'vikkash','gowry2003@gmail.com','$2y$10$z6ChiNryf8Hoc8dCmp/v9OXCkq8wRx6gJHHnS9R4KQeRK1zk/NUkC','customer','','2026-06-23 21:48:04'),(2,'hilton','hilton@gmail.com','$2y$10$KPtCskQ2X9OIWYwPmSNFueODJMGpqoa0H5H7d7/3K7sP1ZEzribT.','business','','2026-06-23 21:49:19'),(3,'Ebi','admin@fooder.com','$2y$10$7.3zAhdQjGs5avSOursuoejCx8ysU4WJCSKzWAKFoiA33cDj6JL6S','business','','2026-06-25 09:15:00'),(4,'Ebi','uebineshi@gmail.com','$2y$10$ToWF/0Z.1r179powNRI0xOSqlyqPYk2WlvyvYlckpCUcdDyt9CfzO','customer','','2026-06-25 09:18:22'),(5,'nesh','uebineshe@gmail.com','$2y$10$UR7jzQAyJMNgGEDUeXhJpugz/n02kJTMvgOL1d4lzo7bLNQ0nUf66','customer','','2026-06-25 09:30:11'),(6,'ebinesh','uebinesho@gmail.com','$2y$10$fkgphHgsK3k1CV8miFCPweFPl1b3yuLkPmKLYEI1Vf6TTURbZgvOO','business','','2026-06-29 14:36:34'),(7,'ehdgc','uebineshu@gmail.com','$2y$10$JVJ/eLeRsn0/tWkE2.ybV.37JP5yrUtz9NvSYZxlWNpvIOmnFg7fS','customer','','2026-06-29 14:40:40'),(8,'Test Biz','biz@test.com','$2y$10$A3rMKiQCmZiB4f9VSlakkOor0naf/RhBb/b6b22G7stS3fXuSiLz2','business','','2026-06-29 19:14:10');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-30  1:07:42
