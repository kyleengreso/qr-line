-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: qr_queue_system
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `counters`
--

DROP TABLE IF EXISTS `counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `counters` (
  `idcounter` int NOT NULL AUTO_INCREMENT,
  `counterNumber` int NOT NULL DEFAULT (0),
  `idemployee` int DEFAULT NULL,
  `queue_count` int DEFAULT '0',
  `counter_pwd` enum('Y','N') DEFAULT 'N',
  `created_at` datetime DEFAULT NULL,
  `queue_remain` int DEFAULT NULL,
  PRIMARY KEY (`idcounter`),
  UNIQUE KEY `counterNumber` (`counterNumber`),
  KEY `idemployee` (`idemployee`),
  CONSTRAINT `counters_ibfk_1` FOREIGN KEY (`idemployee`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counters`
--

LOCK TABLES `counters` WRITE;
/*!40000 ALTER TABLE `counters` DISABLE KEYS */;
/*!40000 ALTER TABLE `counters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role_type` enum('admin','employee') DEFAULT 'employee',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `employee_last_login` datetime DEFAULT NULL,
  `attempt_login` int DEFAULT '0',
  `user_image` mediumblob,
  `user_id_extended` varchar(20) DEFAULT NULL,
  `cut_off_state` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'admin','$2y$10$62cQ4Ipvdymi9/ihTIEt/u1cJGm69NWvuCVmwKR6d6ZkFglL1QhTm','2025-03-13 15:47:13','admin','admin@gmail.com',1,'2025-03-23 19:20:00',NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requesters`
--

DROP TABLE IF EXISTS `requesters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `requesters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `payment` enum('registrar','assessment') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci CHECKSUM=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requesters`
--

LOCK TABLES `requesters` WRITE;
/*!40000 ALTER TABLE `requesters` DISABLE KEYS */;
/*!40000 ALTER TABLE `requesters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scheduler`
--

DROP TABLE IF EXISTS `scheduler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduler` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `enable` tinyint NOT NULL DEFAULT (0),
  `schedule_key` varchar(50) NOT NULL DEFAULT '0',
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `comment` text,
  `repeat` enum('daily','weekly','month','Sun','Mon','Tue','Wed','Thu','Fri','Sat') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `everyday` text,
  `schedule_type` enum('requester','maintenance') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `managed_by` int DEFAULT NULL,
  PRIMARY KEY (`schedule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Schedule for whole operation';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scheduler`
--

LOCK TABLES `scheduler` WRITE;
/*!40000 ALTER TABLE `scheduler` DISABLE KEYS */;
INSERT INTO `scheduler` VALUES (10,1,'requester_form',NULL,NULL,'08:00:00','23:00:00',NULL,'daily','sun;mon;tue;wed;thu;fri;sat','requester',NULL);
/*!40000 ALTER TABLE `scheduler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setup_system`
--

DROP TABLE IF EXISTS `setup_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `setup_system` (
  `setup_id` int NOT NULL AUTO_INCREMENT,
  `setup_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `setup_value` varchar(255) DEFAULT NULL,
  `setup_value_int` int DEFAULT NULL,
  `setup_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'Generated Itself',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setup_id`),
  UNIQUE KEY `unique_setup_key` (`setup_key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setup_system`
--

LOCK TABLES `setup_system` WRITE;
/*!40000 ALTER TABLE `setup_system` DISABLE KEYS */;
INSERT INTO `setup_system` VALUES (1,'queue_count',NULL,267,'Queue Count for requester form','2025-05-01 15:48:11'),(2,'employees_employee_active',NULL,0,'Count of employee/cashier are active','2025-05-17 13:18:27'),(3,'employees_employee_inactive',NULL,0,'Count of employee/cashier are inactive','2025-05-01 20:20:07'),(4,'employees_admin_active',NULL,1,'Count of admin are active','2025-05-01 22:53:52'),(5,'employees_admin_inactive',NULL,0,'Count of admin are inactive','2025-05-01 22:47:02'),(6,'corporate_email','palawan.edu.ph',NULL,'Specific corporate used for filter the transaction',NULL),(7,'employees_total',NULL,1,'Count of employees total','2025-05-17 13:18:27'),(8,'employees_active',NULL,1,'Count of employees are active','2025-05-17 13:18:27'),(9,'employees_inactive',NULL,0,'Count of employees are inactive','2025-05-01 22:47:02'),(10,'employees_admin',NULL,1,'Count employee admins','2025-05-01 22:53:52'),(11,'attempt_login',NULL,5,'Number of time of login before lockdown','2025-05-01 12:43:34'),(12,'transactions_total',NULL,0,'Count of total transactions','2025-05-17 13:19:00'),(13,'transactions_yesterday',NULL,0,'Count of transactions yesterday','2025-05-15 00:03:00'),(14,'transactions_this_week',NULL,0,'Count of transactions this week','2025-05-17 13:19:00'),(15,'transactions_this_month',NULL,0,'Count of transactions this month','2025-05-17 13:19:00'),(16,'transactions_this_year',NULL,0,'Count of transactions this year','2025-05-17 13:19:00'),(17,'transactions_today',NULL,0,'Count of transactions this day','2025-05-17 13:18:39'),(18,'transactions_today_pending',NULL,0,'Count of pending transactions this day','2025-05-17 13:17:39'),(19,'transactions_today_completed',NULL,0,'Count of completed transactions this day','2025-05-17 13:18:39'),(20,'transactions_today_cancelled',NULL,0,'Count of cancelled transactions this day','2025-05-01 20:17:44'),(21,'transactions_today_serve',NULL,0,'Count of serve transactions this day','2025-05-01 15:46:03'),(22,'transactions_today_missed',NULL,0,'Count of missed transactions this day','2025-05-01 15:46:06'),(23,'requester_corporate',NULL,NULL,'Generated Itself','2025-05-01 16:04:39'),(24,'requester_ordinary',NULL,NULL,'Generated Itself','2025-05-01 16:04:34'),(25,'requester_corporate_assessment',NULL,NULL,'Generated Itself','2025-05-01 15:50:22'),(26,'requester_corporate_registrar',NULL,NULL,'Generated Itself','2025-05-01 15:50:24'),(154,'requester_ordinary_assessment',NULL,NULL,'Generated Itself',NULL),(155,'requester_ordinary_registrar',NULL,NULL,'Generated Itself',NULL),(156,'patch_version','0.199.4',NULL,'Generated Itself','2025-05-13 19:44:37');
/*!40000 ALTER TABLE `setup_system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `idtransaction` int NOT NULL AUTO_INCREMENT,
  `idrequester` int DEFAULT NULL,
  `idemployee` int DEFAULT NULL,
  `idcounter` int DEFAULT NULL,
  `queue_number` int DEFAULT NULL,
  `token_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `transaction_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','completed','cancelled','missed','serve') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'pending',
  `email_sent` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idtransaction`),
  UNIQUE KEY `token_number` (`token_number`),
  KEY `idemployee` (`idemployee`),
  KEY `idcounter` (`idcounter`),
  KEY `iduser` (`idrequester`) USING BTREE,
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`idrequester`) REFERENCES `requesters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`idemployee`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`idcounter`) REFERENCES `counters` (`idcounter`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_logs`
--

DROP TABLE IF EXISTS `user_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `comment` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`log_id`) USING BTREE,
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_logs`
--

LOCK TABLES `user_logs` WRITE;
/*!40000 ALTER TABLE `user_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-17 21:20:58
