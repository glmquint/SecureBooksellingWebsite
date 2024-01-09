CREATE DATABASE  IF NOT EXISTS `securebooksellingdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `securebooksellingdb`;
-- MySQL dump 10.13  Distrib 8.0.18, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: securebooksellingdb
-- ------------------------------------------------------
-- Server version	8.2.0

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
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `books` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL DEFAULT 'Unnamed book',
  `price` int NOT NULL,
  `author` varchar(45) DEFAULT 'Unknwon',
  `available` int DEFAULT '0',
  `synopsis` text,
  `cover_path` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `title_UNIQUE` (`title`),
  CONSTRAINT `CHK_Book_Avail` CHECK ((`available` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `books`
--

LOCK TABLES `books` WRITE;
/*!40000 ALTER TABLE `books` DISABLE KEYS */;
INSERT INTO `books` VALUES (1,'A Tale of Two Cities',1099,'Charles Dickens',100,'A Tale of Two Cities is a historical novel published in 1859 by Charles Dickens, set in London and Paris before and during the French Revolution. The novel tells the story of the French Doctor Manette, his 18-year-long imprisonment in the Bastille in Paris, and his release to live in London with his daughter Lucie whom he had never met. The story is set against the conditions that led up to the French Revolution and the Reign of Terror.','images/A Tale of Two Cities-cover.png'),(2,'The Little Prince',836,'Antoine de Saint-Exup√©rie',100,'The Little Prince (French: Le Petit Prince, pronounced [l…ô p(…ô)ti p Å…õÃÉs]) is a novella written and illustrated by French aristocrat, writer, and military pilot Antoine de Saint-Exup√©ry. It was first published in English and French in the United States by Reynal & Hitchcock in April 1943 and was published posthumously in France following liberation; Saint-Exup√©ry\'s works had been banned by the Vichy Regime. The story follows a young prince who visits various planets, including Earth, and addresses themes of loneliness, friendship, love, and loss. Despite its style as a children\'s book, The Little Prince makes observations about life, adults, and human nature.','images/The Little Prince-cover.png'),(3,'Harry Potter and the Philosopher\'s Stone',1200,'J.K. Rowling',99,'Harry Potter and the Philosopher\'s Stone is a fantasy novel written by British author J. K. Rowling. The first novel in the Harry Potter series and Rowling\'s debut novel, it follows Harry Potter, a young wizard who discovers his magical heritage on his eleventh birthday, when he receives a letter of acceptance to Hogwarts School of Witchcraft and Wizardry. Harry makes close friends and a few enemies during his first year at the school and with the help of his friends, Ron Weasley and Hermione Granger, he faces an attempted comeback by the dark wizard Lord Voldemort, who killed Harry\'s parents, but failed to kill Harry when he was just 15 months old.','images/Harry Potter and the Philosopher\'s Stone-cover.png'),(4,'And Then There Were None',1150,'Agatha Christie',98,'And Then There Were None is a mystery novel by the English writer Agatha Christie, who described it as the most difficult of her books to write.[2] It was first published in the United Kingdom by the Collins Crime Club on 6 November 1939, as Ten Little Niggers,[3] after an 1869 minstrel song that serves as a major plot element.[4][5] The US edition was released in January 1940 with the title And Then There Were None, taken from the last five words of the song.[6] Successive American reprints and adaptations use that title, though American Pocket Books paperbacks used the title Ten Little Indians between 1964 and 1986. UK editions continued to use the original title until 1985','images/And Then There Were None-cover.png'),(5,'Dream of the Red Chamber',1510,'Cao Xueqin',100,'Dream of the Red Chamber (Honglou Meng) or The Story of the Stone (Shitou Ji) is a Chinese novel composed by Cao Xueqin in the mid-18th century. One of the Four Great Classical Novels of Chinese literature, it is known for its psychological scope, and its observation of the worldview, aesthetics, lifestyles, and social relations of 18th-century China','images/Dream of the Red Chamber-cover.png'),(6,'The Hobbit',990,'J.R.R. Tolkien',100,'The Hobbit, or There and Back Again is a children\'s fantasy novel by English author J. R. R. Tolkien. It was published in 1937 to wide critical acclaim, being nominated for the Carnegie Medal and awarded a prize from the New York Herald Tribune for best juvenile fiction. The book is recognized as a classic in children\'s literature, and is one of the best-selling books of all time with over 100 million copies sold.','images/The Hobbit-cover.png'),(7,'The Alchemist',1000,'Paulo Coelho',2,'The Alchemist (Portuguese: O Alquimista) is a novel by Brazilian author Paulo Coelho which was first published in 1988. Originally written in Portuguese, it became a widely translated international bestseller. The story follows the shepherd boy Santiago in his journey across northern Africa to the pyramids of Egypt after he dreams of finding a treasure there','images/The Alchemist-cover.png');
/*!40000 ALTER TABLE `books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carts` (
  `id` int NOT NULL,
  `book` int NOT NULL,
  `quantity` int NOT NULL,
  PRIMARY KEY (`id`,`book`),
  KEY `book_id_fk_idx` (`book`),
  CONSTRAINT `book_id_fk` FOREIGN KEY (`book`) REFERENCES `books` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
INSERT INTO `carts` VALUES (234255,1,3),(250033,3,1),(250033,4,1),(260870,3,1),(398642,7,4),(525373,4,2);
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` varchar(45) NOT NULL,
  `user` int NOT NULL,
  `cart` int NOT NULL,
  `address` varchar(45) NOT NULL,
  `total_price` int NOT NULL,
  `status` varchar(45) NOT NULL,
  PRIMARY KEY (`user`,`cart`),
  KEY `cart_fk_idx` (`cart`),
  CONSTRAINT `cart_fk` FOREIGN KEY (`cart`) REFERENCES `carts` (`id`),
  CONSTRAINT `user_fk` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES ('193356',4,260870,'asdf',1200,'in transit'),('496178',4,398642,'asdf',4000,'waiting for restock'),('119633',4,525373,'asdf',2300,'in transit'),('473357',6,234255,'a',3297,'in transit'),('936090',6,250033,'a',2350,'in transit');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchases` (
  `buyer` int NOT NULL,
  `book` int NOT NULL,
  PRIMARY KEY (`buyer`,`book`),
  KEY `book_fk_idx` (`book`),
  CONSTRAINT `book_fk` FOREIGN KEY (`book`) REFERENCES `books` (`id`),
  CONSTRAINT `client_fk` FOREIGN KEY (`buyer`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES (6,1),(4,3),(6,3),(4,4),(6,4),(4,7);
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reset_token`
--

DROP TABLE IF EXISTS `reset_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reset_token` (
  `token` binary(16) NOT NULL,
  `user_id` int NOT NULL,
  `expiration_date` datetime NOT NULL,
  PRIMARY KEY (`token`),
  KEY `user_fk_idx` (`user_id`),
  CONSTRAINT `user_reset_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reset_token`
--

LOCK TABLES `reset_token` WRITE;
/*!40000 ALTER TABLE `reset_token` DISABLE KEYS */;
INSERT INTO `reset_token` VALUES (_binary 'BdG\‘&\ÏmÄ•˘n¢ìñ',16,'2024-01-08 12:47:10'),(_binary 'meß{Åô™M:vµÇn\⁄C	',19,'2024-01-08 12:51:28'),(_binary '\ﬂ41≥¢ˇŸóTÄ\‚GÉçê',17,'2024-01-08 12:49:30'),(_binary '\‚TXh™dttòtÿÖ$æU\Ë',4,'2024-01-08 11:58:13'),(_binary '\ÎC∑ól∞únÑz≤ñD?i\…',18,'2024-01-08 12:50:18');
/*!40000 ALTER TABLE `reset_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(60) NOT NULL,
  `password` varchar(60) NOT NULL,
  `failed_login_attempts` int NOT NULL DEFAULT '0',
  `failed_login_time` datetime DEFAULT '1970-01-01 00:00:00',
  `active` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,'ghi0m@localhost.com','$2y$10$3BhgHBnlHGN7vMhgBEfwXuQjX8wqo7s478ZpMd0ZC7Oq4TuW4YqIW',0,'2024-01-08 11:18:46',1),(5,'fabi0@localhost.com','$2y$10$MdaisEyyuy6TwmSSo8ipDOue6T/dmrDgdHzoQjvkm75eXDFFkeXCu',0,'2023-12-18 16:59:58',1),(6,'giacom0@localhost.com','$2y$10$BUUPclzGBPrJSKILGyKh7.Z0ULvlcrM6MRrdIoDAxjfIDVnZZNuXm',0,'1970-01-01 00:00:00',1),(16,'glmquint@gmail.com','$2y$10$AqGnFOquYL.pH7poV5pV/e29/dA038dae3noIRwDeL92hMDpeL0A2',0,'1970-01-01 00:00:00',0),(17,'hide@localhost.com','$2y$10$mRNzab4nNZNmqQFN8rtwa.4hCLOVSfGTq8tHRxzrRtqoZL9cjwANe',0,'1970-01-01 00:00:00',0),(18,'asdfadfa@23452345','$2y$10$mmO1lVTgdMKwFJv/ai56LelszYYyeMmX2dehllyKZcwZOWG46GPwi',0,'1970-01-01 00:00:00',0),(19,'hide2@gmail.com','$2y$10$tcArnNn5pcWwhR2x1tRGguzqUDbClNEojGzh5vcQ8ziN5XI0OACP6',0,'1970-01-01 00:00:00',0);
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

-- Dump completed on 2024-01-09  9:42:01
