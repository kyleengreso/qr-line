-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               8.0.41 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- Version:                      12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for qr_queue_system
CREATE DATABASE IF NOT EXISTS `qr_queue_system` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `qr_queue_system`;

-- Dumping structure for table qr_queue_system.counters
CREATE TABLE IF NOT EXISTS `counters` (
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table qr_queue_system.counters: ~0 rows (approximately)
DELETE FROM `counters`;

-- Dumping structure for procedure qr_queue_system.employeeMonitor
DELIMITER //
CREATE PROCEDURE `employeeMonitor`()
    COMMENT 'Monitor the employee''s activity even login and logout'
BEGIN
	DECLARE employees_total INT DEFAULT 0;						-- Total of Employees 
	DECLARE employees_admin INT DEFAULT 0;						-- Total of Admin in Employees
	DECLARE employees_employee INT DEFAULT 0;					-- Total of Employee/Cashier in Employees
	DECLARE employees_active INT DEFAULT 0;					-- Total of Employees are active
	DECLARE employees_inactive INT DEFAULT 0;					-- Total of Employees are inactive
	DECLARE employees_admin_active INT DEFAULT 0;			-- Total of Admin in Employees are active
	DECLARE employees_admin_inactive INT DEFAULT 0;			-- Total of Admin in Employees are inactive
	DECLARE employees_employee_active INT DEFAULT 0;		-- Total of Employee/Cashier in Employees are active
	DECLARE employees_employee_inactive INT DEFAULT 0;		-- Total of Employee/Cashier in Employees are inactive
	
	-- Count total employees
   SELECT COUNT(id) 
   INTO employees_total
   FROM employees;
   
   UPDATE setup_system
   SET setup_value_int = employees_total
   WHERE setup_key = 'employees_total';
   
	-- Count total employees admin
   SELECT COUNT(id) 
   INTO employees_admin
   FROM employees
	WHERE role_type = 'admin';
   
   UPDATE setup_system
   SET setup_value_int = employees_admin
   WHERE setup_key = 'employees_admin';
	
	-- Count total employees employee/cashier
   SELECT COUNT(id) 
   INTO employees_employee
   FROM employees
	WHERE role_type = 'employee';
   
   UPDATE setup_system
   SET setup_value_int = employees_employee
   WHERE setup_key = 'employees_employee';
   
	-- Count total employees are active
   SELECT COUNT(id) 
   INTO employees_active
   FROM employees
	WHERE `active` = 1;
   
   UPDATE setup_system
   SET setup_value_int = employees_active
   WHERE setup_key = 'employees_active';
   
	-- Count total employees are inactive
   SELECT COUNT(id) 
   INTO employees_inactive
   FROM employees
	WHERE `active` = 0;
   
   UPDATE setup_system
   SET setup_value_int = employees_inactive
   WHERE setup_key = 'employees_inactive';
   
	-- Count total admin employees are active
   SELECT COUNT(id) 
   INTO employees_admin_active
   FROM employees
	WHERE `active` = 1 AND role_type = 'admin';
   
   UPDATE setup_system
   SET setup_value_int = employees_admin_active
   WHERE setup_key = 'employees_admin_active';
   
	-- Count total admin employees are inactive
   SELECT COUNT(id) 
   INTO employees_admin_active
   FROM employees
	WHERE `active` = 0 AND role_type = 'admin';
   
   UPDATE setup_system
   SET setup_value_int = employees_admin_active
   WHERE setup_key = 'employees_admin_inactive';
   
	-- Count total cashier/employee employees are active
   SELECT COUNT(id) 
   INTO employees_employee_active
   FROM employees
	WHERE `active` = 1 AND role_type = 'employee';
   
   UPDATE setup_system
   SET setup_value_int = employees_employee_active
   WHERE setup_key = 'employees_employee_active';
   
	-- Count total cashier/employee employees are inactive
   SELECT COUNT(id) 
   INTO employees_employee_inactive
   FROM employees
	WHERE `active` = 0 AND role_type = 'employee';
   
   UPDATE setup_system
   SET setup_value_int = employees_employee_inactive
   WHERE setup_key = 'employees_employee_inactive';
END//
DELIMITER ;

-- Dumping structure for event qr_queue_system.employeeMonitorEvent
DELIMITER //
CREATE EVENT `employeeMonitorEvent` ON SCHEDULE EVERY 5 SECOND STARTS '2025-01-01 12:18:17' ON COMPLETION PRESERVE ENABLE DO BEGIN
	CALL `employeeMonitor`();
	
	
END//
DELIMITER ;

-- Dumping structure for table qr_queue_system.employees
CREATE TABLE IF NOT EXISTS `employees` (
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
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table qr_queue_system.employees: ~10 rows (approximately)
DELETE FROM `employees`;
INSERT INTO `employees` (`id`, `username`, `password`, `created_at`, `role_type`, `email`, `active`, `employee_last_login`, `attempt_login`, `user_image`, `user_id_extended`, `cut_off_state`) VALUES
	(1, 'marc', '$2y$10$npINOHJFBrzmyYBkm31jH.s0wOpNGeDIMlis7.yRiv9uby7GzOdhC', '2025-03-13 15:47:13', 'admin', 'markcediebuday@gmail.com2', 1, '2025-03-23 19:20:00', -96, NULL, NULL, 0),
	(2, 'kyle', '$2y$10$LOmN.0JisVKw7niiC.8h7uGjBi37ZM8oUEW2/rmCKv3aziqROId0S', '2025-03-13 15:51:07', 'employee', 'py@gmail.com', 1, NULL, 3, NULL, NULL, 0),
	(3, 'ronald', '$2y$10$ebA29Vdog1//ZpZ4R7ZFt.JcQYaGUarDEya/z4HIvrIFBmp3jd.T6', '2025-03-13 15:51:25', 'employee', NULL, 1, NULL, 0, NULL, NULL, 0),
	(4, 'root', '$2y$10$hiCjuciNXNnOe7ZWuEc9Oui5efXq1bmlgGmVtW54azXgHq2hW81Ny', '2025-03-13 17:07:05', 'employee', 'markcediebuday@gmail.com', 1, NULL, 0, NULL, NULL, 0),
	(5, 'pael', '$2y$10$56lAk2ntCmcHGqikg1jAG.rNU8RXHxwi7IuYnNN0IkeqndObeBw2G', '2025-03-13 17:08:17', 'employee', 'pael@gmail.com', 1, NULL, 0, NULL, NULL, 0),
	(6, 'marc2', '$2y$10$Nx852RMiw0S3rz7KEaLk3unbW.SDljHtkd8Vk/6G1r1OOQcZUCLfa', '2025-03-13 17:47:28', 'admin', NULL, 1, NULL, 0, NULL, NULL, 0),
	(7, 'marc1', '$2y$10$IYEEhFJUPHiI2jD4r3HcKO51zwlHW.9C6djxoDPg/NzjCwliSzBhO', '2025-03-18 12:23:37', 'employee', NULL, 1, NULL, 0, NULL, NULL, 0),
	(8, 'mas', '$2y$10$2qtLrtuFBCH98OHG2zS0uOntNWQBPguO0lxSO24VV5NN3qZh0NeWC', '2025-03-18 12:36:43', 'admin', NULL, 0, NULL, 2, NULL, NULL, 0),
	(9, 'ai', '$2y$10$qwqxPZlsu7jzjsAEmiZTNu2fVkadpdogE5QBP7eW2c/3MmP.GhhoS', '2025-03-18 12:44:55', 'admin', NULL, 1, NULL, 0, NULL, NULL, 0),
	(18, 'engine6', '$2y$10$R6rChq5xdK7KgGE9nflxxe7owfTJ0W/OqZkZhZOc1nw.RIQ3nD83C', '2025-03-22 17:15:28', 'employee', 'm1@g.com', 1, NULL, 0, NULL, NULL, 0);

-- Dumping structure for procedure qr_queue_system.requesterCount
DELIMITER //
CREATE PROCEDURE `requesterCount`()
BEGIN
	DECLARE requesters_corporate INT DEFAULT 0;
	DECLARE requesters_ordinary INT DEFAULT 0;
	DECLARE requesters_corporate_assessment INT DEFAULT 0;
	DECLARE requesters_corporate_registrar INT DEFAULT 0;
	DECLARE requesters_ordinary_assessment INT DEFAULT 0;
	DECLARE requesters_ordinary_registrar INT DEFAULT 0;
	
	-- Total Requesters in corporate
	SELECT COUNT(id)
	INTO requesters_corporate
	FROM requesters;
END//
DELIMITER ;

-- Dumping structure for table qr_queue_system.requesters
CREATE TABLE IF NOT EXISTS `requesters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `payment` enum('registrar','assessment') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci CHECKSUM=1;

-- Dumping data for table qr_queue_system.requesters: ~0 rows (approximately)
DELETE FROM `requesters`;

-- Dumping structure for table qr_queue_system.setup_system
CREATE TABLE IF NOT EXISTS `setup_system` (
  `setup_id` int NOT NULL AUTO_INCREMENT,
  `setup_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `setup_value` varchar(255) DEFAULT NULL,
  `setup_value_int` int DEFAULT NULL,
  `setup_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'Generated Itself',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setup_id`),
  UNIQUE KEY `unique_setup_key` (`setup_key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table qr_queue_system.setup_system: ~28 rows (approximately)
DELETE FROM `setup_system`;
INSERT INTO `setup_system` (`setup_id`, `setup_key`, `setup_value`, `setup_value_int`, `setup_description`, `updated_at`) VALUES
	(1, 'queue_count', NULL, 267, 'Queue Count for requester form', '2025-05-01 15:48:11'),
	(2, 'employees_employee_active', NULL, 6, 'Count of employee/cashier are active', '2025-05-01 20:20:07'),
	(3, 'employees_employee_inactive', NULL, 0, 'Count of employee/cashier are inactive', '2025-05-01 20:20:07'),
	(4, 'employees_admin_active', NULL, 3, 'Count of admin are active', '2025-05-01 20:20:07'),
	(5, 'employees_admin_inactive', NULL, 1, 'Count of admin are inactive', '2025-05-01 20:20:07'),
	(6, 'corporate_email', 'palawan.edu.ph', NULL, 'Specific corporate used for filter the transaction', NULL),
	(7, 'employees_total', NULL, 10, 'Count of employees total', '2025-05-01 20:20:07'),
	(8, 'employees_active', NULL, 9, 'Count of employees are active', '2025-05-01 20:20:07'),
	(9, 'employees_inactive', NULL, 1, 'Count of employees are inactive', '2025-05-01 20:20:07'),
	(10, 'employees_admin', NULL, 4, 'Count employee admins', '2025-05-01 20:20:07'),
	(11, 'attempt_login', NULL, 5, 'Number of time of login before lockdown', '2025-05-01 12:43:34'),
	(12, 'transactions_total', NULL, 0, 'Count of total transactions', '2025-05-01 20:23:00'),
	(13, 'transactions_yesterday', NULL, 0, 'Count of transactions yesterday', '2025-05-01 20:18:00'),
	(14, 'transactions_this_week', NULL, 0, 'Count of transactions this week', '2025-05-01 20:23:00'),
	(15, 'transactions_this_month', NULL, 0, 'Count of transactions this month', '2025-05-01 20:23:00'),
	(16, 'transactions_this_year', NULL, 0, 'Count of transactions this year', '2025-05-01 20:23:00'),
	(17, 'transactions_today', NULL, 0, 'Count of transactions this day', '2025-05-01 20:22:04'),
	(18, 'transactions_today_pending', NULL, 0, 'Count of pending transactions this day', '2025-05-01 20:22:04'),
	(19, 'transactions_today_completed', NULL, 0, 'Count of completed transactions this day', '2025-05-01 15:46:00'),
	(20, 'transactions_today_cancelled', NULL, 0, 'Count of cancelled transactions this day', '2025-05-01 20:17:44'),
	(21, 'transactions_today_serve', NULL, 0, 'Count of serve transactions this day', '2025-05-01 15:46:03'),
	(22, 'transactions_today_missed', NULL, 0, 'Count of missed transactions this day', '2025-05-01 15:46:06'),
	(23, 'requester_corporate', NULL, NULL, 'Generated Itself', '2025-05-01 16:04:39'),
	(24, 'requester_ordinary', NULL, NULL, 'Generated Itself', '2025-05-01 16:04:34'),
	(25, 'requester_corporate_assessment', NULL, NULL, 'Generated Itself', '2025-05-01 15:50:22'),
	(26, 'requester_corporate_registrar', NULL, NULL, 'Generated Itself', '2025-05-01 15:50:24'),
	(154, 'requester_ordinary_assessment', NULL, NULL, 'Generated Itself', NULL),
	(155, 'requester_ordinary_registrar', NULL, NULL, 'Generated Itself', NULL);

-- Dumping structure for event qr_queue_system.TransactionHistoryStatsEvent
DELIMITER //
CREATE EVENT `TransactionHistoryStatsEvent` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-01-01 00:00:00' ON COMPLETION PRESERVE ENABLE COMMENT 'Get all transaction' DO BEGIN
	CALL `TransactionsHistoryStats`();
END//
DELIMITER ;

-- Dumping structure for table qr_queue_system.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
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

-- Dumping data for table qr_queue_system.transactions: ~1 rows (approximately)
DELETE FROM `transactions`;

-- Dumping structure for procedure qr_queue_system.TransactionsHistoryStats
DELIMITER //
CREATE PROCEDURE `TransactionsHistoryStats`()
BEGIN
   DECLARE transactions_yesterday INT DEFAULT 0;
   DECLARE transactions_this_week INT DEFAULT 0;
   DECLARE transactions_this_month INT DEFAULT 0;
   DECLARE transactions_this_year INT DEFAULT 0;
   DECLARE transactions_total_all INT DEFAULT 0;

   -- Calculate transactions for yesterday
   SELECT COUNT(idtransaction) 
   INTO transactions_yesterday
   FROM transactions 
   WHERE DATE(transaction_time) = CURDATE() - INTERVAL 1 DAY;

   -- Calculate transactions for this week
   SELECT COUNT(idtransaction) 
   INTO transactions_this_week
   FROM transactions 
   WHERE WEEK(transaction_time) = WEEK(CURDATE()) 
     AND YEAR(transaction_time) = YEAR(CURDATE());

   -- Calculate transactions for this month
   SELECT COUNT(idtransaction) 
   INTO transactions_this_month
   FROM transactions 
   WHERE MONTH(transaction_time) = MONTH(CURDATE()) 
     AND YEAR(transaction_time) = YEAR(CURDATE());

   -- Calculate transactions for this year
   SELECT COUNT(idtransaction) 
   INTO transactions_this_year
   FROM transactions 
   WHERE YEAR(transaction_time) = YEAR(CURDATE());

   -- Calculate total transactions
   SELECT COUNT(*) 
   INTO transactions_total_all
   FROM transactions;

   -- Update the setup_system table with the calculated values
   UPDATE setup_system 
   SET setup_value_int = transactions_yesterday 
   WHERE setup_key = 'transactions_yesterday';

   UPDATE setup_system 
   SET setup_value_int = transactions_this_week 
   WHERE setup_key = 'transactions_this_week';

   UPDATE setup_system 
   SET setup_value_int = transactions_this_month 
   WHERE setup_key = 'transactions_this_month';

   UPDATE setup_system 
   SET setup_value_int = transactions_this_year 
   WHERE setup_key = 'transactions_this_year';

   UPDATE setup_system 
   SET setup_value_int = transactions_total_all
   WHERE setup_key = 'transactions_total';
END//
DELIMITER ;

-- Dumping structure for procedure qr_queue_system.TransactionsToday
DELIMITER //
CREATE PROCEDURE `TransactionsToday`()
    COMMENT 'Count all transactions in current day'
BEGIN

   DECLARE transactions_today INT DEFAULT 0;                 -- Transactions today: All
   DECLARE transactions_today_pending INT DEFAULT 0;         -- Transactions today: Pending
   DECLARE transactions_today_completed INT DEFAULT 0;       -- Transactions today: Completed
   DECLARE transactions_today_cancelled INT DEFAULT 0;       -- Transactions today: Cancelled
	DECLARE transactions_today_missed INT DEFAULT 0;	       -- Transactions today: Missed
   DECLARE transactions_today_serve INT DEFAULT 0;       	 -- Transactions today: Serve

   -- Calculate transactions for today
   SELECT COUNT(idtransaction) 
   INTO transactions_today
   FROM transactions
   WHERE DATE(transaction_time) = CURDATE();

   -- Calculate pending transactions for today
   SELECT COUNT(idtransaction) 
   INTO transactions_today_pending
   FROM transactions 
   WHERE DATE(transaction_time) = CURDATE() AND status = 'pending';

   -- Calculate completed transactions for today
   SELECT COUNT(idtransaction) 
   INTO transactions_today_completed
   FROM transactions 
   WHERE DATE(transaction_time) = CURDATE() AND status = 'completed';

   -- Calculate canceled transactions for today
   SELECT COUNT(idtransaction) 
   INTO transactions_today_cancelled
   FROM transactions 
   WHERE DATE(transaction_time) = CURDATE() AND status = 'cancelled';

	
   -- Update the setup_system table with the calculated values
   UPDATE setup_system 
   SET setup_value_int = transactions_today 
   WHERE setup_key = 'transactions_today';

   UPDATE setup_system 
   SET setup_value_int = transactions_today_pending 
   WHERE setup_key = 'transactions_today_pending';

   UPDATE setup_system 
   SET setup_value_int = transactions_today_completed 
   WHERE setup_key = 'transactions_today_completed';

   UPDATE setup_system 
   SET setup_value_int = transactions_today_cancelled 
   WHERE setup_key = 'transactions_today_cancelled';
   
   UPDATE setup_system 
   SET setup_value_int = transactions_today_missed
   WHERE setup_key = 'transactions_today_missed';
   
   UPDATE setup_system 
   SET setup_value_int = transactions_today_serve
   WHERE setup_key = 'transactions_today_serve';
END//
DELIMITER ;

-- Dumping structure for event qr_queue_system.TransactionsTodayEvent
DELIMITER //
CREATE EVENT `TransactionsTodayEvent` ON SCHEDULE EVERY 5 SECOND STARTS '2025-05-01 11:58:29' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
	CALL `TransactionsToday`();
END//
DELIMITER ;

-- Dumping structure for table qr_queue_system.user_logs
CREATE TABLE IF NOT EXISTS `user_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `comment` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`log_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table qr_queue_system.user_logs: ~0 rows (approximately)
DELETE FROM `user_logs`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
