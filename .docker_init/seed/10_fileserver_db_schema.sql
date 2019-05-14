CREATE DATABASE  IF NOT EXISTS `ppload` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `ppload`;
-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: 46.101.167.14    Database: ppload
-- ------------------------------------------------------
-- Server version	5.5.57-0ubuntu0.14.04.1-log

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
-- Table structure for table `ppload_collections`
--

DROP TABLE IF EXISTS `ppload_collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_collections` (
  `id` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `files` int(11) NOT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text,
  `category` varchar(64) DEFAULT NULL,
  `tags` text,
  `version` varchar(64) DEFAULT NULL,
  `content_id` varchar(255) DEFAULT NULL,
  `content_page` varchar(255) DEFAULT NULL,
  `downloaded_timestamp` datetime DEFAULT NULL,
  `downloaded_ip` varchar(39) DEFAULT NULL,
  `downloaded_count` int(11) DEFAULT NULL,
  `created_timestamp` datetime DEFAULT NULL,
  `created_ip` varchar(39) DEFAULT NULL,
  `updated_timestamp` datetime DEFAULT NULL,
  `updated_ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_collections_downloaded`
--

DROP TABLE IF EXISTS `ppload_collections_downloaded`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_collections_downloaded` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `downloaded_timestamp` datetime DEFAULT NULL,
  `downloaded_ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_collections_downloaded_sub`
--

DROP TABLE IF EXISTS `ppload_collections_downloaded_sub`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_collections_downloaded_sub` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `downloaded_timestamp` datetime DEFAULT NULL,
  `downloaded_ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_collections_sub`
--

DROP TABLE IF EXISTS `ppload_collections_sub`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_collections_sub` (
  `id` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `files` int(11) NOT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text,
  `category` varchar(64) DEFAULT NULL,
  `tags` text,
  `version` varchar(64) DEFAULT NULL,
  `content_id` varchar(255) DEFAULT NULL,
  `content_page` varchar(255) DEFAULT NULL,
  `downloaded_timestamp` datetime DEFAULT NULL,
  `downloaded_ip` varchar(39) DEFAULT NULL,
  `downloaded_count` int(11) DEFAULT NULL,
  `created_timestamp` datetime DEFAULT NULL,
  `created_ip` varchar(39) DEFAULT NULL,
  `updated_timestamp` datetime DEFAULT NULL,
  `updated_ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_favorites`
--

DROP TABLE IF EXISTS `ppload_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_favorites` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `file_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_files`
--

DROP TABLE IF EXISTS `ppload_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_files` (
  `id` int(11) NOT NULL,
  `origin_id` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text,
  `category` varchar(64) DEFAULT NULL,
  `tags` text,
  `version` varchar(64) DEFAULT NULL,
  `ocs_compatible` int(1) NOT NULL DEFAULT '1',
  `content_id` varchar(255) DEFAULT NULL,
  `content_page` varchar(255) DEFAULT NULL,
  `downloaded_timestamp` datetime DEFAULT NULL,
  `downloaded_ip` varchar(39) DEFAULT NULL,
  `downloaded_count` int(11) DEFAULT NULL,
  `created_timestamp` datetime DEFAULT NULL,
  `created_ip` varchar(39) DEFAULT NULL,
  `updated_timestamp` datetime DEFAULT NULL,
  `updated_ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_collection_id` (`collection_id`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_files_downloaded`
--

DROP TABLE IF EXISTS `ppload_files_downloaded`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_files_downloaded` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `downloaded_timestamp` datetime DEFAULT NULL,
  `downloaded_ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_time` (`downloaded_timestamp`),
  KEY `idx_collectionid` (`collection_id`),
  KEY `idx_ownerid` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER ppload.ppload_files_downloaded_AFTER_INSERT AFTER INSERT ON ppload.ppload_files_downloaded FOR EACH ROW BEGIN

	#insert also into table stat_downloads_24h

	INSERT INTO `pling`.`stat_downloads_24h` (	

		id,

		client_id,

		owner_id,

		collection_id,

		file_id,

		user_id,

		referer,

		downloaded_timestamp,

		downloaded_ip

	) values (

		new.id,

		new.client_id,

		new.owner_id,

		new.collection_id,

		new.file_id,

		new.user_id,

		new.referer,

		new.downloaded_timestamp,

		new.downloaded_ip

	);

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `ppload_files_downloaded_sub`
--

DROP TABLE IF EXISTS `ppload_files_downloaded_sub`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_files_downloaded_sub` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `downloaded_timestamp` datetime DEFAULT NULL,
  `downloaded_ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_time` (`downloaded_timestamp`),
  KEY `idx_collectionid` (`collection_id`),
  KEY `idx_ownerid` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_files_sub`
--

DROP TABLE IF EXISTS `ppload_files_sub`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_files_sub` (
  `id` int(11) NOT NULL,
  `origin_id` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text,
  `category` varchar(64) DEFAULT NULL,
  `tags` text,
  `version` varchar(64) DEFAULT NULL,
  `ocs_compatible` int(1) NOT NULL DEFAULT '1',
  `content_id` varchar(255) DEFAULT NULL,
  `content_page` varchar(255) DEFAULT NULL,
  `downloaded_timestamp` datetime DEFAULT NULL,
  `downloaded_ip` varchar(39) DEFAULT NULL,
  `downloaded_count` int(11) DEFAULT NULL,
  `created_timestamp` datetime DEFAULT NULL,
  `created_ip` varchar(39) DEFAULT NULL,
  `updated_timestamp` datetime DEFAULT NULL,
  `updated_ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_collection_id` (`collection_id`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_media`
--

DROP TABLE IF EXISTS `ppload_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_media` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `genre` varchar(64) DEFAULT NULL,
  `track` varchar(5) DEFAULT NULL,
  `creationdate` int(4) DEFAULT NULL,
  `bitrate` int(11) DEFAULT NULL,
  `playtime_seconds` int(11) DEFAULT NULL,
  `playtime_string` varchar(8) DEFAULT NULL,
  `played_timestamp` datetime DEFAULT NULL,
  `played_ip` varchar(39) DEFAULT NULL,
  `played_count` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_media_albums`
--

DROP TABLE IF EXISTS `ppload_media_albums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_media_albums` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_media_artists`
--

DROP TABLE IF EXISTS `ppload_media_artists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_media_artists` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_media_played`
--

DROP TABLE IF EXISTS `ppload_media_played`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_media_played` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `played_timestamp` datetime DEFAULT NULL,
  `played_ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppload_profiles`
--

DROP TABLE IF EXISTS `ppload_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppload_profiles` (
  `id` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `client_id` int(11) NOT NULL,
  `owner_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `homepage` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `idx_owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_collection_download`
--

DROP TABLE IF EXISTS `stat_collection_download`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_collection_download` (
  `collection_id` int(11) NOT NULL,
  `amount` bigint(21) NOT NULL DEFAULT '0',
  KEY `idx_collection_id` (`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'ppload'
--

--
-- Dumping routines for database 'ppload'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-04-26 15:39:22
