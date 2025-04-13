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
  PRIMARY KEY (`idcounter`),
  UNIQUE KEY `counterNumber` (`counterNumber`),
  KEY `idemployee` (`idemployee`),
  CONSTRAINT `counters_ibfk_1` FOREIGN KEY (`idemployee`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counters`
--

LOCK TABLES `counters` WRITE;
/*!40000 ALTER TABLE `counters` DISABLE KEYS */;
INSERT INTO `counters` VALUES (3,-1,1,0,'N',NULL),(27,1,21,0,'N',NULL),(30,10,27,0,'N',NULL),(31,5,7,0,'N',NULL),(32,7,18,0,'N',NULL),(34,43,5,0,'N',NULL),(35,2,2,0,'N',NULL);
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'marc','$2y$10$oneW.gRL5SQBXMD5p1RaM.j2lesU6k72Voy04UopC.NB4xiKdi9CC','2025-03-13 15:47:13','admin','master@gmail.com',1,'2025-03-23 19:20:00',-96,NULL,NULL),(2,'kyle','$2y$10$4ucIeH0mZ4AByu4enxtpKeIx573T6qJxXHqlc1wac0GE9UcMNZ4Ji','2025-03-13 15:51:07','employee',NULL,1,NULL,0,NULL,NULL),(3,'ronald','$2y$10$NLxe1YdenLW7Qe6jNUonce77nCqWmjF7S6p/ws6MGlw52ELDR6aCC','2025-03-13 15:51:25','employee',NULL,1,NULL,0,NULL,NULL),(4,'buds','$2y$10$paeqZzxCsEgITNLhuXjiXeDECbbf8L86MoUv9D52WSGbUJandhg1.','2025-03-13 17:07:05','employee',NULL,0,NULL,0,NULL,NULL),(5,'pael','$2y$10$56lAk2ntCmcHGqikg1jAG.rNU8RXHxwi7IuYnNN0IkeqndObeBw2G','2025-03-13 17:08:17','employee','pael@gmail.com',0,NULL,0,NULL,NULL),(6,'marc2','$2y$10$Nx852RMiw0S3rz7KEaLk3unbW.SDljHtkd8Vk/6G1r1OOQcZUCLfa','2025-03-13 17:47:28','admin',NULL,1,NULL,0,NULL,NULL),(7,'marc1','$2y$10$IYEEhFJUPHiI2jD4r3HcKO51zwlHW.9C6djxoDPg/NzjCwliSzBhO','2025-03-18 12:23:37','employee',NULL,1,NULL,0,NULL,NULL),(8,'mas','$2y$10$2qtLrtuFBCH98OHG2zS0uOntNWQBPguO0lxSO24VV5NN3qZh0NeWC','2025-03-18 12:36:43','employee',NULL,0,NULL,2,NULL,NULL),(9,'ai','$2y$10$qwqxPZlsu7jzjsAEmiZTNu2fVkadpdogE5QBP7eW2c/3MmP.GhhoS','2025-03-18 12:44:55','admin',NULL,0,NULL,0,NULL,NULL),(16,'engine4','$2y$10$kSUvHY7IRLfFoETvmeNzi./Tc1Z9X71QQbAxmFUrkbYJF5sqH2/l.','2025-03-22 17:14:20','admin','m4@g.co',1,NULL,0,NULL,NULL),(18,'engine6','$2y$10$4gR06YyUmzacyyAHQ1xisuhQiUUVyVNno0tnwjprNy8C5N2491XvO','2025-03-22 17:15:28','employee','m1@g.co',1,NULL,0,NULL,NULL),(19,'wew','$2y$10$3cLLdbqn0FCrTEJs1yD5O.hQ3hJNwUDZh/nEWKj3V6MH3PtCqSNuC','2025-03-23 12:35:56','employee',NULL,1,NULL,0,NULL,NULL),(20,'kou','$2y$10$3K1FnjMgSMCVnzm7kMHycOaKTghF8LElIKbvT.jlH14NugT7kQc2S','2025-03-23 12:47:10','employee','kou@g.co',0,NULL,0,NULL,NULL),(21,'master15','$2y$10$ZHEFdnYF.2ruqrCe4.nCSu5NoQvX8STxJS/Orp6bJiK8msD19zNWS','2025-03-23 12:47:37','employee','marc@gm.com',1,NULL,0,NULL,NULL),(22,'master15s','$2y$10$1RwkdNHSdyRm8tyaKyIwnOmX0pRT1Ahpu1PfskeiKYBFHiTcRn9Xy','2025-03-23 12:48:08','employee','marc@gms.com',1,NULL,0,NULL,NULL),(23,'master15ss','$2y$10$vwSZfTBatTn/e0jH0yqdDu8sCWPTy7J1QivzEA7vDOqtMKA/7yFA6','2025-03-23 12:53:42','employee','marc@gmsss.com',1,NULL,0,NULL,NULL),(24,'iedix','$2y$10$Dh/kkOcOInt0zeWfUjwh0ezyoUgvgCFUjUY1vZB3y3grZVOGzgnSe','2025-03-23 12:53:51','employee','iedix@g.co',1,NULL,0,NULL,NULL),(25,'jacj','$2y$10$AuZquXsBdXTyhyNorbD3juK5TUX6vLdUglNA/pMzfW4KV76VNWiPW','2025-03-23 12:54:09','employee','jack@j.com',1,NULL,0,NULL,NULL),(26,'engine7','$2y$10$HM8de.bwqfCLNZhwMHDFEe3MytsJ2kOwHT4.jIY/z9XehTCmv5.U.','2025-03-24 17:26:55','employee','m7@g.co',1,NULL,0,NULL,NULL),(27,'engine8','$2y$10$4vmdRISeEepg9EgxVn38o.CfFKgq3DS0pC0z4Wv1awL1YvQLWWbK.','2025-03-24 17:28:03','employee','m8@g.co',1,NULL,0,NULL,NULL),(28,'engine9','$2y$10$xZITbwiY.rD8zJuKklhcz.wwEmrJrY9XH6c8s3/TlqckVXpuleLHW','2025-03-24 17:28:25','employee','m9@g.co',0,NULL,0,NULL,NULL),(29,'mig11','$2y$10$v4rqaWL1oFvL6u7fUPLc9uy1A3UuN/5qVAxjX.ZSKR4XvV7GHR8DK','2025-03-24 17:31:54','admin','mig11@gmail.com',0,NULL,0,NULL,NULL),(30,'base','$2y$10$ngj1y9mCrdL3QPADzb0Y1.uU3lk5dOvWouG/rOao7goLQfgM4kmga','2025-03-24 19:48:52','admin','base@b.com',0,NULL,0,NULL,NULL),(31,'jj','$2y$10$b3XT7WiiFIfrYdzWtMMya.6z4tFSKLoY4UoQBQQDW.IvW5NodBB/.','2025-03-24 19:50:34','admin','jj@j.com',1,NULL,0,NULL,NULL),(32,'qq','$2y$10$6h6Aa1HarhKe45/t3MCp7uMICaYbbP1Zn0tMJilqL5S/AXGqMwUJS','2025-03-24 19:51:56','admin','qq@q.com',0,NULL,0,NULL,NULL),(33,'ww','$2y$10$3L6oh.U0zTQ8h/JgOa3VtO9knQkM3pmHF7D74QCM1kETgrJUa7l3O','2025-03-24 19:54:10','admin','ww@w.com',1,NULL,0,NULL,NULL),(34,'we','$2y$10$lXv2t614dwBbTX0x45S7DOdBeYd7SSVn0yBLhaGZooFlePrXfm76q','2025-04-01 18:22:33','employee','we@f.com',0,NULL,0,NULL,NULL),(35,'flak','$2y$10$V6j05gEAUp0JL8bJGTBOVO0i/K2fP.8w9TLvSiBCxIMCVwBthSM82','2025-04-11 02:44:43','admin','flak@flak.com',1,NULL,0,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci CHECKSUM=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requesters`
--

LOCK TABLES `requesters` WRITE;
/*!40000 ALTER TABLE `requesters` DISABLE KEYS */;
INSERT INTO `requesters` VALUES (1,'Oni-Chan','markcediebuday@gmail.com','registrar','2025-03-16 11:34:24'),(2,'Ace','markcediebuday@gmail.com','registrar','2025-03-16 11:35:15'),(3,'Day','markcediebuday@gmail.com','assessment','2025-03-16 11:35:27'),(4,'Marh','markcediebuday@gmail.com','assessment','2025-03-16 11:35:39'),(5,'Marj','markcediebuday@gmail.com','assessment','2025-03-16 11:35:55'),(6,'Maureen','markcediebuday@gmail.com','assessment','2025-03-16 11:36:06'),(7,'Merriam','markcediebuday@gmail.com','assessment','2025-03-16 12:25:42'),(8,'Merriam','markcediebuday@gmail.com','assessment','2025-03-16 12:26:20'),(9,'Gerald','markcediebuday@gmail.com','assessment','2025-03-16 12:26:35'),(10,'Senpai','markcediebuday@gmail.com','registrar','2025-03-16 14:57:06'),(11,'May','markcediebuday@gmail.com','registrar','2025-03-17 13:09:42'),(12,'wews','markcediebuday@gmail.com','assessment','2025-03-17 15:07:40'),(13,'Trial','markcediebuday@gmail.com','assessment','2025-03-17 15:08:18'),(14,'router','markcediebuday@gmail.com','registrar','2025-03-17 15:11:05'),(15,'aar','markcediebuday@gmail.com','registrar','2025-03-17 15:23:09'),(16,'aar','markcediebuday@gmail.com','assessment','2025-03-17 15:27:33'),(17,'Gerald','markcediebuday@gmail.com','assessment','2025-03-17 15:29:17'),(18,'Test','markcediebuday@gmail.com','registrar','2025-03-17 15:29:49'),(19,'Gerald','markcediebuday@gmail.com','assessment','2025-03-23 13:35:26'),(20,'Gerald','markcediebuday@gmail.com','assessment','2025-03-23 21:24:36'),(21,'test','markcediebuday@gmail.com','registrar','2025-04-07 18:32:25');
/*!40000 ALTER TABLE `requesters` ENABLE KEYS */;
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
  PRIMARY KEY (`setup_id`),
  UNIQUE KEY `unique_setup_key` (`setup_key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setup_system`
--

LOCK TABLES `setup_system` WRITE;
/*!40000 ALTER TABLE `setup_system` DISABLE KEYS */;
INSERT INTO `setup_system` VALUES (1,'queue_count',NULL,43,'Generated Itself'),(2,'transactions_today_total',NULL,0,'Generated Itself'),(3,'transactions_today_pending_total',NULL,0,'Generated Itself'),(4,'transactions_today_completed_total',NULL,0,'Generated Itself'),(5,'transactions_today_cancelled_total',NULL,0,'Generated Itself'),(6,'corporate_email','palawan.edu.ph',NULL,'Specific corporate used for filter the transaction'),(7,'employees_total',NULL,28,'Generated Itself'),(8,'employees_active_total',NULL,18,'Generated Itself'),(9,'employees_not_active_total',NULL,10,'Generated Itself'),(10,'employees_admin_total',NULL,NULL,'Ge'),(11,'attempt_login',NULL,3,'Number of time of login before lockdown'),(139,'transactions_total',NULL,21,'Generated Itself'),(140,'employees_employees_total',NULL,0,'Generated Itself');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,1,NULL,NULL,1,'AA2A3C','2025-03-16 19:34:24','pending',1),(2,2,NULL,NULL,2,'82CB7E','2025-03-16 19:35:15','pending',1),(3,3,NULL,NULL,25,'D68A19','2025-03-16 19:35:27','pending',1),(4,4,NULL,NULL,26,'1811BB','2025-03-16 19:35:39','pending',1),(5,5,NULL,NULL,27,'C72AE3','2025-03-16 19:35:55','pending',1),(6,6,NULL,NULL,28,'87396C','2025-03-16 19:36:06','pending',1),(7,7,NULL,NULL,29,'EAE984','2025-03-16 20:25:42','pending',1),(8,8,NULL,NULL,30,'B4715D','2025-03-16 20:26:20','pending',1),(9,9,NULL,NULL,31,'56AD0A','2025-03-16 20:26:35','pending',1),(10,10,NULL,NULL,32,'C457E9','2025-03-16 22:57:06','pending',1),(11,11,NULL,NULL,33,'B36296','2025-03-17 21:09:42','pending',1),(12,12,NULL,NULL,34,'41375B','2025-03-17 23:07:40','pending',1),(13,13,NULL,NULL,35,'F37ED4','2025-03-17 23:08:18','pending',1),(14,14,NULL,NULL,36,'7B1BEE','2025-03-17 23:11:05','pending',1),(15,15,NULL,NULL,37,'8A2722','2025-03-17 23:23:09','pending',1),(16,16,NULL,NULL,38,'41869E','2025-03-17 23:27:33','pending',1),(17,17,NULL,NULL,39,'0414CE','2025-03-17 23:29:17','pending',1),(18,18,NULL,NULL,40,'E69E47','2025-03-17 23:29:49','pending',1),(19,19,NULL,NULL,41,'D75CCF','2025-03-23 21:35:26','pending',1),(20,20,NULL,NULL,42,'938834','2025-03-24 05:24:36','pending',1),(21,21,NULL,NULL,43,'BE479D','2025-04-08 02:32:25','pending',1);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-11 23:42:09
