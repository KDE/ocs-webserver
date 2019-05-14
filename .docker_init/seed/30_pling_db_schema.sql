CREATE DATABASE  IF NOT EXISTS `pling` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `pling`;
-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: 46.101.167.14    Database: pling
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
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `activity_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT 'Log action of this memeber',
  `project_id` int(11) DEFAULT NULL,
  `object_id` int(11) NOT NULL COMMENT 'Key to the action (add comment, pling, ...)',
  `object_ref` varchar(45) NOT NULL COMMENT 'Refferenz to the object table (plings, project, project_comment,...)',
  `object_title` varchar(90) DEFAULT NULL COMMENT 'Title to show',
  `object_text` varchar(150) DEFAULT NULL COMMENT 'Short text of this object (first 150 characters)',
  `object_img` varchar(255) DEFAULT NULL,
  `activity_type_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Wich ENGINE of activity: create, update,delete.',
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`activity_log_id`),
  KEY `member_id` (`member_id`),
  KEY `project_id` (`project_id`),
  KEY `object_id` (`object_id`),
  KEY `activity_log_id` (`activity_log_id`,`member_id`,`project_id`,`object_id`),
  KEY `idx_time` (`member_id`,`time`)
) ENGINE=InnoDB AUTO_INCREMENT=551620 DEFAULT CHARSET=utf8 COMMENT='Log all actions of a user. Wen can then generate a newsfeed ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `activity_log_types`
--

DROP TABLE IF EXISTS `activity_log_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log_types` (
  `activity_log_type_id` int(11) NOT NULL,
  `type_text` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`activity_log_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Type of activities';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category_tag`
--

DROP TABLE IF EXISTS `category_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_tag` (
  `category_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category_tag_group`
--

DROP TABLE IF EXISTS `category_tag_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_tag_group` (
  `category_id` int(11) NOT NULL,
  `tag_group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `collection_projects`
--

DROP TABLE IF EXISTS `collection_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_projects` (
  `collection_project_id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `order` int(11) DEFAULT NULL,
  `active` int(1) unsigned DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`collection_project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1658 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comment_types`
--

DROP TABLE IF EXISTS `comment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment_types` (
  `comment_type_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  KEY `pk` (`comment_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_target_id` int(11) NOT NULL,
  `comment_member_id` int(11) NOT NULL,
  `comment_parent_id` int(11) DEFAULT NULL,
  `comment_type` int(11) DEFAULT '0',
  `comment_pling_id` int(11) DEFAULT NULL,
  `comment_text` text,
  `comment_active` int(1) DEFAULT '1',
  `comment_created_at` datetime DEFAULT NULL,
  `comment_deleted_at` datetime DEFAULT NULL,
  `source_id` int(11) DEFAULT '0',
  `source_pk` int(11) DEFAULT NULL,
  PRIMARY KEY (`comment_id`),
  UNIQUE KEY `uk_hive_pk` (`source_pk`,`source_id`),
  KEY `idx_target` (`comment_target_id`),
  KEY `idx_created` (`comment_created_at`),
  KEY `idx_parent` (`comment_parent_id`),
  KEY `idx_pling` (`comment_pling_id`),
  KEY `idx_type_active` (`comment_type`,`comment_active`),
  KEY `idx_member` (`comment_member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1470786 DEFAULT CHARSET=utf8;
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
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER `comment_created` BEFORE INSERT ON `comments` FOR EACH ROW

  BEGIN

    IF NEW.comment_created_at IS NULL THEN

		SET NEW.comment_created_at = NOW();

	END IF;

	

	IF NEW.comment_type = 0 THEN

	

		UPDATE project p

		SET p.count_comments = (p.count_comments+1)

		WHERE p.project_id = NEW.comment_target_id;

		

	END IF;

  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `comment_update` BEFORE UPDATE ON `comments` FOR EACH ROW

  BEGIN



	IF NEW.comment_active = 0 AND OLD.comment_active = 1 THEN

	

		UPDATE project p

		SET p.count_comments = (p.count_comments-1)

		WHERE p.project_id = NEW.comment_target_id;

		

		SET NEW.comment_deleted_at = NOW();

		

	END IF;

	

	IF NEW.comment_active = 1 AND OLD.comment_active = 0 THEN

	

		UPDATE project p

		SET p.count_comments = (p.count_comments+1)

		WHERE p.project_id = NEW.comment_target_id;

		

		SET NEW.comment_deleted_at = null;

		

	END IF;

	

  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `config_store`
--

DROP TABLE IF EXISTS `config_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_store` (
  `store_id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  `config_id_name` varchar(45) NOT NULL,
  `mapping_id_name` varchar(45) DEFAULT NULL,
  `order` int(11) DEFAULT '0',
  `default` int(1) DEFAULT '0',
  `is_client` int(1) DEFAULT '0',
  `google_id` varchar(45) DEFAULT NULL,
  `piwik_id` int(11) DEFAULT '1',
  `package_type` varchar(45) DEFAULT NULL COMMENT '1-n package_type_ids',
  `cross_domain_login` int(1) NOT NULL DEFAULT '0',
  `is_show_title` int(1) DEFAULT '1',
  `is_show_home` int(1) DEFAULT '0',
  `is_show_git_projects` int(1) DEFAULT '1' COMMENT 'Should the latest Git-Projects-Section been shown?',
  `is_show_blog_news` int(1) DEFAULT '1',
  `is_show_forum_news` int(1) DEFAULT '1',
  `layout_home` varchar(45) DEFAULT NULL,
  `layout_explore` varchar(45) DEFAULT NULL,
  `layout_pagedetail` varchar(45) DEFAULT NULL,
  `layout` varchar(45) DEFAULT NULL,
  `render_view_postfix` varchar(45) DEFAULT NULL,
  `stay_in_context` int(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `config_store_BEFORE_INSERT` BEFORE INSERT ON `config_store` FOR EACH ROW BEGIN

    IF NEW.created_at IS NULL THEN

      SET NEW.created_at = NOW();

    END IF;

  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `config_store_category`
--

DROP TABLE IF EXISTS `config_store_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_store_category` (
  `store_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `project_category_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`store_category_id`),
  KEY `project_category_id_idx` (`project_category_id`),
  KEY `fk_store_id_idx` (`store_id`),
  CONSTRAINT `fk_project_category_id` FOREIGN KEY (`project_category_id`) REFERENCES `project_category` (`project_category_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=753 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `config_store_category_BEFORE_INSERT` BEFORE INSERT ON `config_store_category` FOR EACH ROW BEGIN

	IF NEW.created_at IS NULL THEN

		SET NEW.created_at = NOW();

	END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `config_store_tag`
--

DROP TABLE IF EXISTS `config_store_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_store_tag` (
  `config_store_tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `is_active` int(1) unsigned NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`config_store_tag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
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
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `config_store_tag_before_insert` BEFORE INSERT ON `config_store_tag` FOR EACH ROW BEGIN

	 IF NEW.created_at IS NULL THEN

      SET NEW.created_at = NOW();

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `config_store_tag_group`
--

DROP TABLE IF EXISTS `config_store_tag_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_store_tag_group` (
  `config_store_taggroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `tag_group_id` int(11) NOT NULL,
  `is_active` int(1) unsigned NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`config_store_taggroup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content` (
  `content_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url_name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_active` int(1) NOT NULL DEFAULT '0',
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT '0000-00-00 00:00:00',
  `changed_at` datetime DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT '0000-00-00 00:00:00',
  `show_changed_date` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`),
  UNIQUE KEY `url_name` (`url_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `git_group`
--

DROP TABLE IF EXISTS `git_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `git_group` (
  `git_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `group_full_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`git_group_id`),
  KEY `git_group__group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `git_group_user`
--

DROP TABLE IF EXISTS `git_group_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `git_group_user` (
  `git_group_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `group_access` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`git_group_user_id`),
  KEY `git_group_user__email` (`user_email`),
  KEY `git_group_user__group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mail_template`
--

DROP TABLE IF EXISTS `mail_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_template` (
  `mail_template_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `text` text NOT NULL,
  `created_at` datetime NOT NULL,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`mail_template_id`),
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mem_bio_20190412`
--

DROP TABLE IF EXISTS `mem_bio_20190412`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mem_bio_20190412` (
  `member_id` int(10) NOT NULL DEFAULT '0',
  `biography` text CHARACTER SET utf8,
  `clean_bio` longtext CHARACTER SET utf8
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member` (
  `member_id` int(10) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `password_type` int(1) NOT NULL DEFAULT '0' COMMENT 'Type:  0 = MD5 (OCS), 1 = SHA (Hive)',
  `roleId` int(11) NOT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT 'default-profile.png',
  `avatar_type_id` int(11) NOT NULL DEFAULT '1',
  `type` int(1) NOT NULL DEFAULT '0' COMMENT 'Type: 0 = Member, 1 = group',
  `is_active` int(1) NOT NULL DEFAULT '0',
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  `mail_checked` int(1) NOT NULL DEFAULT '0',
  `agb` int(1) NOT NULL DEFAULT '0',
  `newsletter` int(1) NOT NULL DEFAULT '0',
  `login_method` varchar(45) NOT NULL DEFAULT 'local' COMMENT 'local (registered on pling), facebook, twitter',
  `firstname` varchar(200) DEFAULT NULL,
  `lastname` varchar(200) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `zip` varchar(5) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `last_online` datetime DEFAULT NULL,
  `biography` text,
  `paypal_mail` varchar(255) DEFAULT NULL,
  `paypal_valid_status` mediumint(9) DEFAULT NULL,
  `wallet_address` varchar(255) DEFAULT NULL,
  `dwolla_id` varchar(45) DEFAULT NULL,
  `main_project_id` int(10) DEFAULT NULL COMMENT 'Die ID des .me-Projekts',
  `profile_image_url` varchar(355) DEFAULT '/images/system/default-profile.png' COMMENT 'URL to the profile-image',
  `profile_image_url_bg` varchar(355) DEFAULT NULL,
  `profile_img_src` varchar(45) DEFAULT 'local' COMMENT 'social,gravatar,local',
  `social_username` varchar(50) DEFAULT NULL COMMENT 'Username on facebook/twitter. Used to generate profile-img-url.',
  `social_user_id` varchar(50) DEFAULT NULL COMMENT 'ID from twitter, facebook,...',
  `gravatar_email` varchar(45) DEFAULT NULL COMMENT 'email, wich is connected to gravatar.',
  `facebook_username` varchar(45) DEFAULT NULL,
  `twitter_username` varchar(45) DEFAULT NULL,
  `link_facebook` varchar(300) DEFAULT NULL COMMENT 'Link to facebook',
  `link_twitter` varchar(300) DEFAULT NULL COMMENT 'Link to twitter',
  `link_website` varchar(300) DEFAULT NULL COMMENT 'Link to homepage',
  `link_google` varchar(300) DEFAULT NULL COMMENT 'Link to google',
  `link_github` varchar(300) DEFAULT NULL,
  `validated_at` datetime DEFAULT NULL,
  `validated` int(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `source_id` int(11) DEFAULT '0' COMMENT '0 = local, 1 = hive01',
  `source_pk` int(11) DEFAULT NULL COMMENT 'pk on the source',
  `pling_excluded` int(1) NOT NULL DEFAULT '0',
  `password_old` varchar(255) DEFAULT NULL,
  `password_type_old` int(1) DEFAULT NULL,
  `username_old` varchar(255) DEFAULT NULL,
  `mail_old` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`member_id`),
  KEY `uuid` (`uuid`),
  KEY `idx_created` (`created_at`),
  KEY `idx_login` (`mail`,`username`,`password`,`is_active`,`is_deleted`,`login_method`),
  KEY `idx_mem_search` (`member_id`,`username`,`is_deleted`,`mail_checked`),
  KEY `idx_source` (`source_id`,`source_pk`),
  KEY `idx_username` (`username`),
  KEY `idx_id_active` (`member_id`,`is_active`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=533980 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `member_created` BEFORE INSERT ON `member` FOR EACH ROW BEGIN

	IF NEW.created_at IS NULL THEN

		SET NEW.created_at = NOW();

	END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `member_BEFORE_UPDATE` BEFORE UPDATE ON `member` FOR EACH ROW BEGIN

	 SET NEW.changed_at = NOW();



    IF NEW.username <> OLD.username THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'username', OLD.username,NEW.username);

    END IF;

    IF NEW.mail <> OLD.mail THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'mail', OLD.mail,NEW.mail);

    END IF;

    IF NEW.password <> OLD.password THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'password', OLD.password,NEW.password);

    END IF;

    IF NEW.is_active <> OLD.is_active THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'is_active', OLD.is_active,NEW.is_active);

    END IF;

    IF NEW.is_deleted <> OLD.is_deleted THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'is_deleted', OLD.is_deleted,NEW.is_deleted);

    END IF;

    IF NEW.mail_checked <> OLD.mail_checked THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'mail_checked', OLD.mail_checked,NEW.mail_checked);

    END IF;

    IF NEW.newsletter <> OLD.newsletter THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'newsletter', OLD.newsletter,NEW.newsletter);

    END IF;

    IF NEW.firstname <> OLD.firstname THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'firstname', OLD.firstname,NEW.firstname);

    END IF;

    IF NEW.lastname <> OLD.lastname THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'lastname', OLD.lastname,NEW.lastname);

    END IF;

    IF NEW.street <> OLD.street THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'street', OLD.street,NEW.street);

    END IF;

    IF NEW.zip <> OLD.zip THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'zip', OLD.zip,NEW.zip);

    END IF;

    IF NEW.city <> OLD.city THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'city', OLD.city,NEW.city);

    END IF;

    IF NEW.country <> OLD.country THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'country', OLD.country,NEW.country);

    END IF;

    IF NEW.biography <> OLD.biography THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'biography', OLD.biography,NEW.biography);

    END IF;

    IF NEW.paypal_mail <> OLD.paypal_mail THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'paypal_mail', OLD.paypal_mail,NEW.paypal_mail);

    END IF;

    IF NEW.paypal_valid_status <> OLD.paypal_valid_status THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'paypal_valid_status', OLD.paypal_valid_status,NEW.paypal_valid_status);

    END IF;

    IF NEW.wallet_address <> OLD.wallet_address THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'wallet_address', OLD.wallet_address,NEW.wallet_address);

    END IF;

    IF NEW.dwolla_id <> OLD.dwolla_id THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'dwolla_id', OLD.dwolla_id,NEW.dwolla_id);

    END IF;

    IF NEW.profile_image_url <> OLD.profile_image_url THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'profile_image_url', OLD.profile_image_url,NEW.profile_image_url);

    END IF;

    IF NEW.social_username <> OLD.social_username THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'social_username', OLD.social_username,NEW.social_username);

    END IF;

    IF NEW.social_user_id <> OLD.social_user_id THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'social_user_id', OLD.social_user_id,NEW.social_user_id);

    END IF;

    IF NEW.gravatar_email <> OLD.gravatar_email THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'gravatar_email', OLD.gravatar_email,NEW.gravatar_email);

    END IF;

    IF NEW.facebook_username <> OLD.facebook_username THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'facebook_username', OLD.facebook_username,NEW.facebook_username);

    END IF;

    IF NEW.twitter_username <> OLD.twitter_username THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'twitter_username', OLD.twitter_username,NEW.twitter_username);

    END IF;

    IF NEW.link_facebook <> OLD.link_facebook THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'link_facebook', OLD.link_facebook,NEW.link_facebook);

    END IF;

    IF NEW.link_twitter <> OLD.link_twitter THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'link_twitter', OLD.link_twitter,NEW.link_twitter);

    END IF;

    IF NEW.link_website <> OLD.link_website THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'link_website', OLD.link_website,NEW.link_website);

    END IF;

    IF NEW.link_google <> OLD.link_google THEN  

        INSERT INTO member_log (member_id, field, old_value, new_value) VALUES(OLD.member_id, 'link_google', OLD.link_google,NEW.link_google);

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `member_avatar_type`
--

DROP TABLE IF EXISTS `member_avatar_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_avatar_type` (
  `member_avatar_type_id` int(11) NOT NULL,
  `title` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`member_avatar_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_deactivation_log`
--

DROP TABLE IF EXISTS `member_deactivation_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_deactivation_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `deactivation_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Id of the deactivation',
  `object_type_id` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Member was deactivated from this user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` int(11) DEFAULT '0' COMMENT 'Is the user undeleted -> is_deleted = 1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `object_data` mediumtext,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23270 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_deactivation_object_types`
--

DROP TABLE IF EXISTS `member_deactivation_object_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_deactivation_object_types` (
  `object_type_id` int(11) NOT NULL DEFAULT '0',
  `object_system` varchar(50) DEFAULT NULL,
  `object_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`object_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_dl_plings`
--

DROP TABLE IF EXISTS `member_dl_plings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_dl_plings` (
  `yearmonth` int(6) DEFAULT NULL,
  `project_id` int(11) NOT NULL DEFAULT '0',
  `project_category_id` int(11) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `paypal_mail` varchar(255) DEFAULT NULL,
  `num_downloads` bigint(21) NOT NULL DEFAULT '0',
  `dl_pling_factor` decimal(3,2) NOT NULL DEFAULT '0.00',
  `probably_payout_amount` decimal(25,4) DEFAULT NULL,
  `num_plings` bigint(20) DEFAULT NULL,
  `is_license_missing` int(1) unsigned DEFAULT '0',
  `is_source_missing` int(1) unsigned DEFAULT '0',
  `is_pling_excluded` int(1) unsigned DEFAULT '0',
  `is_member_pling_excluded` int(1) unsigned DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  UNIQUE KEY `uk_month_proj` (`yearmonth`,`member_id`,`project_id`),
  KEY `idx_yearmonth` (`yearmonth`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER member_dl_plings_BEFORE_INSERT BEFORE INSERT ON member_dl_plings FOR EACH ROW

BEGIN

IF NEW.created_at IS NULL THEN



  SET NEW.created_at = NOW();



END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `member_dl_plings_last_month`
--

DROP TABLE IF EXISTS `member_dl_plings_last_month`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_dl_plings_last_month` (
  `yearmonth` varchar(6) CHARACTER SET utf8mb4 DEFAULT NULL,
  `project_id` int(11) NOT NULL DEFAULT '0',
  `project_category_id` int(11) NOT NULL DEFAULT '0',
  `member_id` varchar(255) CHARACTER SET utf8 NOT NULL,
  `mail` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `paypal_mail` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `num_downloads` bigint(21) NOT NULL DEFAULT '0',
  `dl_pling_factor` double unsigned DEFAULT '1',
  `amount` double DEFAULT NULL,
  `num_plings` bigint(22) DEFAULT NULL,
  `is_license_missing` int(0) NOT NULL DEFAULT '0',
  `is_source_missing` int(0) DEFAULT NULL,
  `is_pling_excluded` int(1) NOT NULL DEFAULT '0' COMMENT 'Project was excluded from pling payout',
  `is_member_pling_excluded` int(1) NOT NULL DEFAULT '0',
  `created_at` binary(0) DEFAULT NULL,
  `updated_at` binary(0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_dl_plings_lastmonth`
--

DROP TABLE IF EXISTS `member_dl_plings_lastmonth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_dl_plings_lastmonth` (
  `yearmonth` int(6) DEFAULT NULL,
  `project_id` int(11) NOT NULL DEFAULT '0',
  `project_category_id` int(11) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `paypal_mail` varchar(255) DEFAULT NULL,
  `num_downloads` bigint(21) NOT NULL DEFAULT '0',
  `dl_pling_factor` decimal(3,2) NOT NULL DEFAULT '0.00',
  `probably_payout_amount` decimal(25,4) DEFAULT NULL,
  `num_plings` bigint(20) DEFAULT NULL,
  `is_license_missing` int(1) unsigned DEFAULT '0',
  `is_source_missing` int(1) unsigned DEFAULT '0',
  `is_pling_excluded` int(1) unsigned DEFAULT '0',
  `is_member_pling_excluded` int(1) unsigned DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  UNIQUE KEY `uk_month_proj` (`yearmonth`,`member_id`,`project_id`),
  KEY `idx_yearmonth` (`yearmonth`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_download_history`
--

DROP TABLE IF EXISTS `member_download_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_download_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(255) NOT NULL,
  `project_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `downloaded_timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_time` (`downloaded_timestamp`),
  KEY `idx_projectid` (`project_id`),
  KEY `idx_memberid` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=140498 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_email`
--

DROP TABLE IF EXISTS `member_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_email` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_member_id` int(11) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `email_primary` int(1) DEFAULT '0',
  `email_deleted` int(1) DEFAULT '0',
  `email_created` datetime DEFAULT NULL,
  `email_checked` datetime DEFAULT NULL,
  `email_verification_value` varchar(255) DEFAULT NULL,
  `email_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`email_id`),
  KEY `idx_address` (`email_address`),
  KEY `idx_member` (`email_member_id`),
  KEY `idx_verification` (`email_verification_value`),
  KEY `idx_hash` (`email_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=479768 DEFAULT CHARSET=latin1;
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
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER pling.member_email_BEFORE_INSERT BEFORE INSERT ON member_email FOR EACH ROW

BEGIN

IF NEW.email_created IS NULL THEN



  SET NEW.email_created = NOW();



END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `member_external_id`
--

DROP TABLE IF EXISTS `member_external_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_external_id` (
  `external_id` varchar(255) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `is_deleted` int(1) DEFAULT NULL,
  `gitlab_user_id` int(1) DEFAULT NULL,
  PRIMARY KEY (`external_id`),
  KEY `idx_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `member_external_id_BEFORE_INSERT` BEFORE INSERT ON `member_external_id` FOR EACH ROW
  BEGIN
    IF NEW.created_at IS NULL THEN
      SET NEW.created_at = NOW();
    END IF;
  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `member_follower`
--

DROP TABLE IF EXISTS `member_follower`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_follower` (
  `member_follower_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `follower_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`member_follower_id`),
  KEY `follower_id` (`follower_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_log`
--

DROP TABLE IF EXISTS `member_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT '0',
  `field` varchar(50) NOT NULL DEFAULT '0',
  `old_value` varchar(255) NOT NULL DEFAULT '0',
  `new_value` varchar(255) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=201391 DEFAULT CHARSET=latin1 COMMENT='Logs all changes on table member (no inserts, only updates)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `member_login`
--

DROP TABLE IF EXISTS `member_login`;
/*!50001 DROP VIEW IF EXISTS `member_login`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `member_login` AS SELECT 
 1 AS `_id`,
 1 AS `username`,
 1 AS `usernameNormalized`,
 1 AS `email`,
 1 AS `password`,
 1 AS `avatarUrl`,
 1 AS `biography`,
 1 AS `admin`,
 1 AS `lastUpdateTime`,
 1 AS `creationTime`,
 1 AS `emailVerified`,
 1 AS `disabled`,
 1 AS `ocs_user_id`,
 1 AS `is_hive`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `member_matrix_data`
--

DROP TABLE IF EXISTS `member_matrix_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_matrix_data` (
  `user_id` text NOT NULL,
  `created_at` bigint(20) DEFAULT NULL,
  `is_imported` int(1) DEFAULT NULL,
  `imported_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`(100))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_password_types`
--

DROP TABLE IF EXISTS `member_password_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_password_types` (
  `password_type_id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`password_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_payout`
--

DROP TABLE IF EXISTS `member_payout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_payout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `yearmonth` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `mail` varchar(50) DEFAULT NULL,
  `paypal_mail` varchar(50) DEFAULT NULL,
  `num_downloads` int(11) DEFAULT NULL,
  `num_points` int(11) DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0=new,1=start request,10=processed,100=completed,999=error',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `timestamp_masspay_start` timestamp NULL DEFAULT NULL,
  `timestamp_masspay_last_ipn` timestamp NULL DEFAULT NULL,
  `last_paypal_ipn` text,
  `last_paypal_status` text,
  `payment_reference_key` varchar(255) DEFAULT NULL COMMENT 'uniquely identifies the request',
  `payment_transaction_id` varchar(255) DEFAULT NULL COMMENT 'uniquely identify caller (developer, facilliator, marketplace) transaction',
  `payment_raw_message` varchar(2000) DEFAULT NULL COMMENT 'the raw text message ',
  `payment_raw_error` varchar(2000) DEFAULT NULL,
  `payment_status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_PAYOUT` (`yearmonth`,`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43290 DEFAULT CHARSET=latin1 COMMENT='Table for our monthly payouts';
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
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER member_payout_BEFORE_INSERT BEFORE INSERT ON member_payout FOR EACH ROW

BEGIN

IF NEW.created_at IS NULL THEN



  SET NEW.created_at = NOW();



END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `member_paypal`
--

DROP TABLE IF EXISTS `member_paypal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_paypal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `paypal_address` varchar(150) DEFAULT NULL,
  `is_active` int(1) unsigned DEFAULT '1',
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `currency` varchar(150) DEFAULT NULL,
  `country_code` varchar(150) DEFAULT NULL,
  `last_payment_status` varchar(150) DEFAULT NULL,
  `last_payment_amount` double DEFAULT NULL,
  `last_transaction_id` varchar(50) DEFAULT NULL,
  `last_transaction_event_code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_paypal_address` (`paypal_address`)
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_ref`
--

DROP TABLE IF EXISTS `member_ref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_ref` (
  `member_ref_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`member_ref_id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8 COMMENT='Wich items are interresting for a user. Used for the newsfee';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_role`
--

DROP TABLE IF EXISTS `member_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_role` (
  `member_role_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `shortname` varchar(50) NOT NULL,
  `is_active` int(1) NOT NULL DEFAULT '0',
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `changed_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`member_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_score`
--

DROP TABLE IF EXISTS `member_score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_score` (
  `member_score_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL DEFAULT '0',
  `score` int(10) NOT NULL DEFAULT '0',
  `count_product` int(11) DEFAULT '0',
  `count_pling` int(11) DEFAULT '0',
  `count_like` int(11) DEFAULT '0',
  `count_comment` int(11) DEFAULT '0',
  `count_years_membership` int(11) DEFAULT '0',
  `count_report_product_spam` int(11) DEFAULT '0',
  `count_report_product_fraud` int(11) DEFAULT '0',
  `count_report_comment` int(11) DEFAULT '0',
  `count_report_member` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`member_score_id`),
  KEY `idx_member` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=122769 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_score_factors`
--

DROP TABLE IF EXISTS `member_score_factors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_score_factors` (
  `factor_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `descrption` varchar(255) DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`factor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_setting_group`
--

DROP TABLE IF EXISTS `member_setting_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_setting_group` (
  `member_setting_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  PRIMARY KEY (`member_setting_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_setting_item`
--

DROP TABLE IF EXISTS `member_setting_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_setting_item` (
  `member_setting_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  `member_setting_group_id` int(11) NOT NULL,
  PRIMARY KEY (`member_setting_item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_setting_value`
--

DROP TABLE IF EXISTS `member_setting_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_setting_value` (
  `member_setting_value_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_setting_item_id` int(11) NOT NULL,
  `value` varchar(100) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`member_setting_value_id`)
) ENGINE=InnoDB AUTO_INCREMENT=515 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_token`
--

DROP TABLE IF EXISTS `member_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_token` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `token_member_id` int(11) NOT NULL,
  `token_provider_name` varchar(45) NOT NULL,
  `token_value` varchar(45) NOT NULL,
  `token_provider_username` varchar(45) DEFAULT NULL,
  `token_fingerprint` varchar(45) DEFAULT NULL,
  `token_created` datetime DEFAULT NULL,
  `token_changed` datetime DEFAULT NULL,
  `token_deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`token_id`),
  KEY `idx_token` (`token_member_id`,`token_provider_name`,`token_value`)
) ENGINE=InnoDB AUTO_INCREMENT=9034 DEFAULT CHARSET=latin1;
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
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `member_token_before_insert` BEFORE INSERT ON `member_token` FOR EACH ROW BEGIN

	IF NEW.token_created IS NULL THEN

      SET NEW.token_created = NOW();

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `package_types`
--

DROP TABLE IF EXISTS `package_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_types` (
  `package_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `order` int(11) DEFAULT NULL,
  `is_active` int(1) DEFAULT '1',
  PRIMARY KEY (`package_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paypal_ipn`
--

DROP TABLE IF EXISTS `paypal_ipn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paypal_ipn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `txn_type` varchar(50) DEFAULT NULL,
  `ipn_track_id` varchar(50) DEFAULT NULL,
  `txn_id` varchar(50) DEFAULT NULL,
  `payer_email` varchar(50) DEFAULT NULL,
  `payer_id` varchar(50) DEFAULT NULL,
  `auth_amount` varchar(50) DEFAULT NULL,
  `mc_currency` varchar(50) DEFAULT NULL,
  `mc_fee` varchar(50) DEFAULT NULL,
  `mc_gross` varchar(50) DEFAULT NULL,
  `memo` varchar(50) DEFAULT NULL,
  `payer_status` varchar(50) DEFAULT NULL,
  `payment_date` varchar(50) DEFAULT NULL,
  `payment_fee` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `pending_reason` varchar(50) DEFAULT NULL,
  `reason_code` varchar(50) DEFAULT NULL,
  `custom` varchar(50) DEFAULT NULL,
  `raw` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4256 DEFAULT CHARSET=latin1 COMMENT='Save all PayPal IPNs here';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paypal_valid_status`
--

DROP TABLE IF EXISTS `paypal_valid_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paypal_valid_status` (
  `id` int(11) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `description` text,
  `color` varchar(50) DEFAULT NULL,
  `is_active` int(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plings`
--

DROP TABLE IF EXISTS `plings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT 'pling-Owner',
  `project_id` int(11) DEFAULT NULL COMMENT 'Witch project was plinged',
  `status_id` int(11) DEFAULT '0' COMMENT 'Stati des pling: 0 = inactive, 1 = active (plinged), 2 = payed successfull, 99 = deleted',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation-time',
  `pling_time` timestamp NULL DEFAULT NULL COMMENT 'When was a project plinged?',
  `active_time` timestamp NULL DEFAULT NULL COMMENT 'When did paypal say, that this pling was payed successfull',
  `delete_time` timestamp NULL DEFAULT NULL,
  `amount` double(10,2) DEFAULT '0.00' COMMENT 'Amount of money',
  `comment` varchar(140) DEFAULT NULL COMMENT 'Comment from the plinger',
  `payment_provider` varchar(45) DEFAULT NULL,
  `payment_reference_key` varchar(255) DEFAULT NULL COMMENT 'uniquely identifies the request',
  `payment_transaction_id` varchar(255) DEFAULT NULL COMMENT 'uniquely identify caller (developer, facilliator, marketplace) transaction',
  `payment_raw_message` varchar(2000) DEFAULT NULL COMMENT 'the raw text message ',
  `payment_raw_error` varchar(2000) DEFAULT NULL,
  `payment_status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `status_id` (`status_id`),
  KEY `member_id` (`member_id`),
  KEY `PLINGS_IX_01` (`status_id`,`project_id`,`member_id`,`active_time`,`amount`)
) ENGINE=InnoDB AUTO_INCREMENT=1028 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT '0',
  `content_type` varchar(255) NOT NULL DEFAULT 'text',
  `project_category_id` int(11) NOT NULL DEFAULT '0',
  `hive_category_id` int(11) NOT NULL DEFAULT '0',
  `is_active` int(1) NOT NULL DEFAULT '0',
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `uuid` varchar(255) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL COMMENT 'ParentId',
  `type_id` int(11) DEFAULT '0' COMMENT '0 = DummyProject, 1 = Project, 2 = Update',
  `title` varchar(100) DEFAULT NULL,
  `description` text,
  `version` varchar(50) DEFAULT NULL,
  `project_license_id` int(11) DEFAULT NULL,
  `image_big` varchar(255) DEFAULT NULL,
  `image_small` varchar(255) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `content_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `major_updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL COMMENT 'Member_id of the creator. Importent for groups.',
  `facebook_code` text,
  `twitter_code` text,
  `google_code` text,
  `source_url` text,
  `link_1` text,
  `embed_code` text,
  `ppload_collection_id` varchar(255) DEFAULT NULL,
  `validated` int(1) DEFAULT '0',
  `validated_at` datetime DEFAULT NULL,
  `featured` int(1) DEFAULT '0',
  `approved` int(1) DEFAULT '0',
  `ghns_excluded` int(1) DEFAULT '0',
  `spam_checked` int(1) NOT NULL DEFAULT '0',
  `pling_excluded` int(1) NOT NULL DEFAULT '0' COMMENT 'Project was excluded from pling payout',
  `amount` int(11) DEFAULT NULL,
  `amount_period` varchar(45) DEFAULT NULL,
  `claimable` int(1) DEFAULT NULL,
  `claimed_by_member` int(11) DEFAULT NULL,
  `count_likes` int(11) DEFAULT '0',
  `count_dislikes` int(11) DEFAULT '0',
  `count_comments` int(11) DEFAULT '0',
  `count_downloads_hive` int(11) DEFAULT '0',
  `is_gitlab_project` int(1) NOT NULL DEFAULT '0',
  `gitlab_project_id` int(11) DEFAULT NULL,
  `show_gitlab_project_issues` int(1) NOT NULL DEFAULT '0',
  `use_gitlab_project_readme` int(1) NOT NULL DEFAULT '0',
  `user_category` text,
  `source_id` int(11) DEFAULT '0',
  `source_pk` int(11) DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`project_id`),
  UNIQUE KEY `uk_source` (`source_id`,`source_pk`,`source_type`),
  KEY `idx_project_cat_id` (`project_category_id`),
  KEY `idx_uuid` (`uuid`),
  KEY `idx_member_id` (`member_id`),
  KEY `idx_pid` (`pid`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_title` (`title`),
  KEY `idx_source` (`source_id`,`source_pk`,`source_type`),
  KEY `idx_status` (`status`,`ppload_collection_id`,`project_category_id`,`project_id`),
  KEY `idx_type_status` (`type_id`,`status`,`project_category_id`,`project_id`),
  KEY `idx_src_status` (`status`,`source_pk`,`source_type`),
  KEY `idx_git` (`is_gitlab_project`,`gitlab_project_id`),
  KEY `idx_ppload_id` (`status`,`ppload_collection_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1302468 DEFAULT CHARSET=utf8;
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
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `project_created` BEFORE INSERT ON `project` FOR EACH ROW BEGIN

	IF NEW.created_at IS NULL THEN

		SET NEW.created_at = NOW();

	END IF;

	SET NEW.changed_at = NOW();

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `report_spam_AFTER_INSERT` AFTER INSERT ON `project` FOR EACH ROW BEGIN

	IF NEW.title like '%http://%' or NEW.title like '%https://%' THEN

		INSERT INTO reports_project (project_id,reported_by,is_deleted, created_at) VALUES (NEW.project_id, 0, 0, NOW());

	END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `trg_project_major_updated_at` BEFORE UPDATE ON `project` FOR EACH ROW
  BEGIN   
     if NEW.changed_at <> OLD.changed_at and DATEDIFF(NEW.changed_at, OLD.major_updated_at)>7 THEN
		SET NEW.major_updated_at = NEW.changed_at;
	END IF;
  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `project_category`
--

DROP TABLE IF EXISTS `project_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_category` (
  `project_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `is_active` int(1) NOT NULL DEFAULT '0',
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  `xdg_type` varchar(50) DEFAULT NULL,
  `name_legacy` varchar(50) DEFAULT NULL,
  `orderPos` int(11) DEFAULT NULL,
  `dl_pling_factor` double unsigned DEFAULT '1',
  `show_description` int(1) NOT NULL DEFAULT '0',
  `source_required` int(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`project_category_id`),
  KEY `idxLeft` (`project_category_id`,`lft`),
  KEY `idxRight` (`project_category_id`,`rgt`),
  KEY `idxPrimaryRgtLft` (`project_category_id`,`rgt`,`lft`,`is_active`,`is_deleted`),
  KEY `idxActive` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=576 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `pling`.`project_category_BEFORE_INSERT` BEFORE INSERT ON `project_category` FOR EACH ROW
BEGIN
	IF NEW.created_at IS NULL THEN
		SET NEW.created_at = NOW();
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `pling`.`project_category_BEFORE_UPDATE` BEFORE UPDATE ON `project_category` FOR EACH ROW
BEGIN
	SET NEW.changed_at = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `project_cc_license`
--

DROP TABLE IF EXISTS `project_cc_license`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_cc_license` (
  `license_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `by` int(1) DEFAULT NULL,
  `nc` int(1) DEFAULT NULL,
  `nd` int(1) DEFAULT NULL,
  `sa` int(1) DEFAULT NULL,
  PRIMARY KEY (`license_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_clone`
--

DROP TABLE IF EXISTS `project_clone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_clone` (
  `project_clone_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `project_id_parent` int(11) DEFAULT NULL COMMENT 'Project Id of the clone on opendesktop',
  `external_link` varchar(255) DEFAULT NULL COMMENT 'External Link to the original project',
  `member_id` int(11) DEFAULT NULL COMMENT 'Who send the report',
  `text` text,
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  `is_valid` int(1) NOT NULL DEFAULT '0' COMMENT 'Admin can mark a report as valid',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `changed_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`project_clone_id`),
  KEY `idxReport` (`project_id`,`member_id`,`is_deleted`,`created_at`),
  KEY `idx_project_clone_project_id` (`project_id`),
  KEY `idx_project_clone_project_id_parent` (`project_id_parent`)
) ENGINE=InnoDB AUTO_INCREMENT=374 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_follower`
--

DROP TABLE IF EXISTS `project_follower`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_follower` (
  `project_follower_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `source_id` int(1) unsigned DEFAULT '0',
  `source_pk` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`project_follower_id`),
  KEY `FIND_FOLLOWER` (`project_id`,`member_id`),
  KEY `uk_project_member` (`project_id`,`member_id`),
  KEY `idx_member_id` (`member_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=77089 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_gallery_picture`
--

DROP TABLE IF EXISTS `project_gallery_picture`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_gallery_picture` (
  `project_id` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  `picture_src` varchar(255) NOT NULL,
  PRIMARY KEY (`project_id`,`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_license`
--

DROP TABLE IF EXISTS `project_license`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_license` (
  `project_license_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`project_license_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_moderation`
--

DROP TABLE IF EXISTS `project_moderation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_moderation` (
  `project_moderation_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_moderation_type_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `value` int(1) NOT NULL,
  `created_by` int(11) NOT NULL,
  `note` text NOT NULL,
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  `is_valid` int(1) NOT NULL DEFAULT '0' COMMENT 'Admin can mark a report as valid',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_moderation_id`),
  KEY `project_moderation_type_id` (`project_moderation_type_id`),
  KEY `idx_project_moderation_project_id` (`project_id`),
  CONSTRAINT `project_moderation_ibfk_1` FOREIGN KEY (`project_moderation_type_id`) REFERENCES `project_moderation_type` (`project_moderation_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_moderation_type`
--

DROP TABLE IF EXISTS `project_moderation_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_moderation_type` (
  `project_moderation_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `tag_id` int(11) DEFAULT NULL COMMENT 'if exist insert/remove project tag_id relation',
  PRIMARY KEY (`project_moderation_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_package_type`
--

DROP TABLE IF EXISTS `project_package_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_package_type` (
  `project_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `package_type_id` int(11) NOT NULL,
  PRIMARY KEY (`project_id`,`file_id`),
  KEY `idx_type_id` (`package_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_plings`
--

DROP TABLE IF EXISTS `project_plings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_plings` (
  `project_plings_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` int(1) DEFAULT '1',
  `deactive_at` date DEFAULT NULL,
  `is_deleted` int(1) DEFAULT '0',
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`project_plings_id`),
  KEY `idx_project_plings_member_id` (`member_id`),
  KEY `idx_project_plings_project_id` (`project_id`),
  KEY `idx_project_plings_project_id_member_id` (`project_id`,`member_id`),
  KEY `idx_project_plings_is_deleted` (`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=198934 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_rating`
--

DROP TABLE IF EXISTS `project_rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_rating` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `user_like` int(1) DEFAULT '0',
  `user_dislike` int(1) DEFAULT '0',
  `comment_id` int(11) DEFAULT '0' COMMENT 'review for rating',
  `rating_active` int(1) DEFAULT '1' COMMENT 'active = 1, deleted = 0',
  `source_id` int(1) DEFAULT '0',
  `source_pk` int(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rating_id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_member_id` (`member_id`) USING BTREE,
  KEY `idx_project_rating_comment_id` (`comment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=535339 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_updates`
--

DROP TABLE IF EXISTS `project_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_updates` (
  `project_update_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL DEFAULT '0',
  `public` int(1) NOT NULL DEFAULT '0',
  `title` varchar(200) DEFAULT NULL,
  `text` text,
  `created_at` datetime DEFAULT '0000-00-00 00:00:00',
  `changed_at` datetime DEFAULT '0000-00-00 00:00:00',
  `source_id` int(11) DEFAULT '0',
  `source_pk` int(11) DEFAULT NULL,
  PRIMARY KEY (`project_update_id`)
) ENGINE=MyISAM AUTO_INCREMENT=48278 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_widget`
--

DROP TABLE IF EXISTS `project_widget`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_widget` (
  `widget_id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `config` text,
  PRIMARY KEY (`widget_id`),
  KEY `idxPROJECT` (`project_id`),
  KEY `idxUUID` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_widget_default`
--

DROP TABLE IF EXISTS `project_widget_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_widget_default` (
  `widget_id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `config` text,
  PRIMARY KEY (`widget_id`),
  KEY `idxPROJECT` (`project_id`),
  KEY `idxUuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue` (
  `queue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `queue_name` varchar(100) NOT NULL,
  `timeout` smallint(5) unsigned NOT NULL DEFAULT '30',
  PRIMARY KEY (`queue_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `queue_message`
--

DROP TABLE IF EXISTS `queue_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue_message` (
  `message_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue_id` int(10) unsigned NOT NULL,
  `handle` char(32) DEFAULT NULL,
  `body` text NOT NULL,
  `md5` char(32) NOT NULL,
  `timeout` decimal(14,4) unsigned DEFAULT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`message_id`),
  UNIQUE KEY `message_handle` (`handle`),
  KEY `message_queueid` (`queue_id`),
  CONSTRAINT `queue_message_ibfk_1` FOREIGN KEY (`queue_id`) REFERENCES `queue` (`queue_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19147 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reports_comment`
--

DROP TABLE IF EXISTS `reports_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports_comment` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `is_deleted` int(1) DEFAULT NULL,
  `is_active` int(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `user_ip` varchar(255) DEFAULT NULL,
  `user_ip2` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`report_id`),
  KEY `idxComment` (`comment_id`),
  KEY `idxMember` (`reported_by`)
) ENGINE=InnoDB AUTO_INCREMENT=435317 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `report_comment_created` BEFORE INSERT ON `reports_comment` FOR EACH ROW
  BEGIN
    IF NEW.created_at IS NULL THEN
      SET NEW.created_at = NOW();
    END IF;
  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `reports_member`
--

DROP TABLE IF EXISTS `reports_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports_member` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `is_deleted` int(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`report_id`),
  KEY `idxMemberId` (`member_id`),
  KEY `idxReportedBy` (`reported_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `reports_member_created` BEFORE INSERT ON `reports_member` FOR EACH ROW
  BEGIN
    IF NEW.created_at IS NULL THEN
      SET NEW.created_at = NOW();
    END IF;

  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `reports_project`
--

DROP TABLE IF EXISTS `reports_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports_project` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` int(1) NOT NULL DEFAULT '0' COMMENT '0 = spam, 1 = fraud',
  `project_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `text` text NOT NULL,
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  `is_valid` int(1) NOT NULL DEFAULT '0' COMMENT 'Admin can mark a report as valid',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`report_id`),
  KEY `idxReport` (`project_id`,`reported_by`,`is_deleted`,`created_at`),
  KEY `idx_reports_project_project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27974 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `report_project_created` BEFORE INSERT ON `reports_project` FOR EACH ROW
  BEGIN
    IF NEW.created_at IS NULL THEN
      SET NEW.created_at = NOW();
    END IF;
  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `remember_me_id` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `changed` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `idx_remember` (`member_id`,`remember_me_id`,`expiry`)
) ENGINE=InnoDB AUTO_INCREMENT=234859 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spam_keywords`
--

DROP TABLE IF EXISTS `spam_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spam_keywords` (
  `spam_key_id` int(11) NOT NULL AUTO_INCREMENT,
  `spam_key_word` varchar(45) NOT NULL,
  `spam_key_created_at` datetime DEFAULT NULL,
  `spam_key_is_deleted` int(1) DEFAULT '0',
  `spam_key_is_active` int(1) DEFAULT '1',
  PRIMARY KEY (`spam_key_id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `spam_keywords_BEFORE_INSERT` BEFORE INSERT ON `spam_keywords` FOR EACH ROW
  BEGIN
    IF NEW.spam_key_created_at IS NULL THEN
      SET NEW.spam_key_created_at = NOW();
    END IF;
  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `stat_downloads_quarter_year`
--

DROP TABLE IF EXISTS `stat_downloads_quarter_year`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_downloads_quarter_year` (
  `project_id` int(11) NOT NULL DEFAULT '0',
  `project_category_id` int(11) NOT NULL DEFAULT '0',
  `ppload_collection_id` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `amount` bigint(21) NOT NULL DEFAULT '0',
  `category_title` varchar(100) CHARACTER SET utf8 NOT NULL,
  KEY `idx_project_id` (`project_id`),
  KEY `idx_collection_id` (`ppload_collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `stg_member_payout_v`
--

DROP TABLE IF EXISTS `stg_member_payout_v`;
/*!50001 DROP VIEW IF EXISTS `stg_member_payout_v`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `stg_member_payout_v` AS SELECT 
 1 AS `id`,
 1 AS `yearmonth`,
 1 AS `member_id`,
 1 AS `mail`,
 1 AS `paypal_mail`,
 1 AS `num_downloads`,
 1 AS `num_points`,
 1 AS `amount`,
 1 AS `status`,
 1 AS `created_at`,
 1 AS `updated_at`,
 1 AS `timestamp_masspay_start`,
 1 AS `timestamp_masspay_last_ipn`,
 1 AS `last_paypal_ipn`,
 1 AS `last_paypal_status`,
 1 AS `payment_reference_key`,
 1 AS `payment_transaction_id`,
 1 AS `payment_raw_message`,
 1 AS `payment_raw_error`,
 1 AS `payment_status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `stg_project_v`
--

DROP TABLE IF EXISTS `stg_project_v`;
/*!50001 DROP VIEW IF EXISTS `stg_project_v`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `stg_project_v` AS SELECT 
 1 AS `project_id`,
 1 AS `member_id`,
 1 AS `content_type`,
 1 AS `project_category_id`,
 1 AS `hive_category_id`,
 1 AS `is_active`,
 1 AS `is_deleted`,
 1 AS `status`,
 1 AS `uuid`,
 1 AS `pid`,
 1 AS `type_id`,
 1 AS `title`,
 1 AS `description`,
 1 AS `version`,
 1 AS `project_license_id`,
 1 AS `image_big`,
 1 AS `image_small`,
 1 AS `start_date`,
 1 AS `content_url`,
 1 AS `created_at`,
 1 AS `changed_at`,
 1 AS `deleted_at`,
 1 AS `creator_id`,
 1 AS `facebook_code`,
 1 AS `twitter_code`,
 1 AS `google_code`,
 1 AS `source_url`,
 1 AS `link_1`,
 1 AS `embed_code`,
 1 AS `ppload_collection_id`,
 1 AS `validated`,
 1 AS `validated_at`,
 1 AS `featured`,
 1 AS `approved`,
 1 AS `ghns_excluded`,
 1 AS `spam_checked`,
 1 AS `pling_excluded`,
 1 AS `amount`,
 1 AS `amount_period`,
 1 AS `claimable`,
 1 AS `claimed_by_member`,
 1 AS `count_likes`,
 1 AS `count_dislikes`,
 1 AS `count_comments`,
 1 AS `count_downloads_hive`,
 1 AS `source_id`,
 1 AS `source_pk`,
 1 AS `source_type`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `support`
--

DROP TABLE IF EXISTS `support`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT 'Supporter',
  `status_id` int(11) DEFAULT '0' COMMENT 'Stati der donation: 0 = inactive, 1 = active (donated), 2 = payed successfull, 99 = deleted',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation-time',
  `type_id` int(1) unsigned DEFAULT '0' COMMENT '0 = onetime payment, 1 = subsscription signup, 2 = subsscription payment',
  `subscription_id` varchar(255) DEFAULT NULL,
  `donation_time` timestamp NULL DEFAULT NULL COMMENT 'When was a project plinged?',
  `active_time` timestamp NULL DEFAULT NULL COMMENT 'When did paypal say, that this donation was payed successfull',
  `delete_time` timestamp NULL DEFAULT NULL,
  `amount` double(10,2) DEFAULT '0.00' COMMENT 'Amount of money',
  `tier` double(10,2) COMMENT '0.99, 2,5,10,null',
  `period` varchar(50) DEFAULT NULL,
  `period_frequency` varchar(50) DEFAULT NULL,
  `comment` varchar(140) DEFAULT NULL COMMENT 'Comment from the supporter',
  `payment_provider` varchar(45) DEFAULT NULL,
  `payment_reference_key` varchar(255) DEFAULT NULL COMMENT 'uniquely identifies the request',
  `payment_transaction_id` varchar(255) DEFAULT NULL COMMENT 'uniquely identify caller (developer, facilliator, marketplace) transaction',
  `payment_raw_message` varchar(2000) DEFAULT NULL COMMENT 'the raw text message ',
  `payment_raw_error` varchar(2000) DEFAULT NULL,
  `payment_status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status_id` (`status_id`),
  KEY `member_id` (`member_id`),
  KEY `DONATION_IX_01` (`status_id`,`member_id`,`active_time`,`amount`)
) ENGINE=InnoDB AUTO_INCREMENT=284 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suspicion_log`
--

DROP TABLE IF EXISTS `suspicion_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suspicion_log` (
  `suspicion_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `http_referer` varchar(255) DEFAULT NULL,
  `http_origin` varchar(255) DEFAULT NULL,
  `client_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `suspicious` int(1) DEFAULT '0',
  PRIMARY KEY (`suspicion_id`),
  KEY `idxProject` (`project_id`),
  KEY `idxMember` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12518 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(45) NOT NULL,
  `tag_fullname` varchar(100) DEFAULT NULL,
  `tag_description` text,
  `is_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `idx_name` (`tag_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4564 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag_group`
--

DROP TABLE IF EXISTS `tag_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(45) NOT NULL,
  `group_display_name` varchar(255) NOT NULL,
  `group_legacy_name` varchar(45) NOT NULL,
  `is_multi_select` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Is this Tag-Group a multiselect Dropdown?',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag_group_item`
--

DROP TABLE IF EXISTS `tag_group_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_group_item` (
  `tag_group_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_group_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`tag_group_item_id`),
  KEY `tag_group_idx` (`tag_group_id`),
  KEY `tag_idx` (`tag_id`),
  CONSTRAINT `tag` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`tag_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `tag_group` FOREIGN KEY (`tag_group_id`) REFERENCES `tag_group` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4702 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag_object`
--

DROP TABLE IF EXISTS `tag_object`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_object` (
  `tag_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `tag_type_id` int(11) NOT NULL,
  `tag_group_id` int(11) DEFAULT NULL,
  `tag_object_id` int(11) NOT NULL,
  `tag_parent_object_id` int(11) DEFAULT NULL,
  `tag_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tag_changed` datetime DEFAULT NULL,
  `is_deleted` int(1) DEFAULT '0',
  PRIMARY KEY (`tag_item_id`),
  KEY `tags_idx` (`tag_id`),
  KEY `tag_object` (`tag_object_id`,`tag_created`,`tag_changed`,`tag_type_id`),
  KEY `ix_tag_object_1` (`tag_type_id`,`tag_group_id`,`tag_object_id`) USING BTREE,
  KEY `idx_tag_files` (`tag_group_id`,`tag_parent_object_id`,`is_deleted`,`tag_id`),
  KEY `idx_tag_object_tag_object_id` (`tag_object_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1047773 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER */ /*!50003 TRIGGER `tag_object_BEFORE_INSERT` BEFORE INSERT ON `tag_object` FOR EACH ROW
  BEGIN
    IF NEW.tag_changed IS NULL THEN
      SET NEW.tag_changed = NOW();
    END IF;
  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tag_type`
--

DROP TABLE IF EXISTS `tag_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_type` (
  `tag_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_type_name` varchar(45) NOT NULL,
  PRIMARY KEY (`tag_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_project_tagids`
--

DROP TABLE IF EXISTS `stat_project_tagids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_project_tagids` (
  `tag_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) DEFAULT NULL,
  KEY `idx_tag_id` (`tag_id`),
  KEY `idx_project_id` (`project_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_projects`
--

DROP TABLE IF EXISTS `stat_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_projects` (
  `project_id` int(11) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL DEFAULT '0',
  `content_type` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT 'text',
  `project_category_id` int(11) NOT NULL DEFAULT '0',
  `hive_category_id` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `uuid` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `pid` int(11) DEFAULT NULL COMMENT 'ParentId',
  `type_id` int(11) DEFAULT '0' COMMENT '0 = DummyProject, 1 = Project, 2 = Update',
  `title` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `description` text CHARACTER SET utf8,
  `version` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `project_license_id` int(11) DEFAULT NULL,
  `image_big` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `image_small` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `content_url` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `major_updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL COMMENT 'Member_id of the creator. Importent for groups.',
  `facebook_code` text CHARACTER SET utf8,
  `source_url` text CHARACTER SET utf8,
  `twitter_code` text CHARACTER SET utf8,
  `google_code` text CHARACTER SET utf8,
  `link_1` text CHARACTER SET utf8,
  `embed_code` text CHARACTER SET utf8,
  `ppload_collection_id` bigint(21) unsigned DEFAULT NULL,
  `validated` int(1) DEFAULT '0',
  `validated_at` datetime DEFAULT NULL,
  `featured` int(1) DEFAULT '0',
  `ghns_excluded` int(1) DEFAULT '0',
  `amount` int(11) DEFAULT NULL,
  `amount_period` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `claimable` int(1) DEFAULT NULL,
  `claimed_by_member` int(11) DEFAULT NULL,
  `count_likes` int(11) DEFAULT '0',
  `count_dislikes` int(11) DEFAULT '0',
  `count_comments` int(11) DEFAULT '0',
  `count_downloads_hive` int(11) DEFAULT '0',
  `source_id` int(11) DEFAULT '0',
  `source_pk` int(11) DEFAULT NULL,
  `source_type` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `project_validated` int(1) DEFAULT '0',
  `project_uuid` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `project_status` int(11) NOT NULL DEFAULT '0',
  `project_created_at` datetime DEFAULT NULL,
  `project_changed_at` datetime DEFAULT NULL,
  `laplace_score` int(11) DEFAULT NULL,
  `member_type` int(1) NOT NULL DEFAULT '0' COMMENT 'Type: 0 = Member, 1 = group',
  `project_member_id` int(10) NOT NULL DEFAULT '0',
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `profile_image_url` varchar(355) CHARACTER SET utf8 DEFAULT '/images/system/default-profile.png' COMMENT 'URL to the profile-image',
  `city` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `member_created_at` datetime DEFAULT NULL,
  `paypal_mail` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `cat_title` varchar(100) CHARACTER SET utf8 NOT NULL,
  `cat_xdg_type` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `cat_name_legacy` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `cat_show_description` int(1) NOT NULL DEFAULT '0',
  `amount_received` double(19,2) DEFAULT NULL,
  `count_plings` bigint(21) DEFAULT '0',
  `count_plingers` bigint(21) DEFAULT '0',
  `latest_pling` timestamp NULL DEFAULT NULL COMMENT 'When did paypal say, that this pling was payed successfull',
  `amount_reports` bigint(21) DEFAULT '0',
  `package_types` text CHARACTER SET utf8mb4,
  `package_names` text,
  `tags` text,
  `tag_ids` text CHARACTER SET utf8mb4,
  `count_downloads_quarter` bigint(21) DEFAULT '0',
  `project_license_title` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`project_id`),
  KEY `idx_ppload` (`ppload_collection_id`),
  KEY `idx_cat` (`project_category_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_source_url` (`source_url`(50))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_store_prod_count`
--

DROP TABLE IF EXISTS `stat_store_prod_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_store_prod_count` (
  `project_category_id` int(11) NOT NULL,
  `tag_id` varchar(255) DEFAULT NULL,
  `count_product` int(11) DEFAULT NULL,
  `stores` varchar(255) DEFAULT NULL,
  KEY `idx_tag` (`project_category_id`,`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_cat_prod_count`
--

DROP TABLE IF EXISTS `stat_cat_prod_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_cat_prod_count` (
  `project_category_id` int(11) NOT NULL,
  `tag_id` varchar(255) DEFAULT NULL,
  `count_product` int(11) DEFAULT NULL,
  KEY `idx_tag` (`project_category_id`,`tag_id`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_cat_prod_count_w_spam`
--

DROP TABLE IF EXISTS `stat_cat_prod_count_w_spam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_cat_prod_count_w_spam` (
  `project_category_id` int(11) NOT NULL,
  `tag_id` int(11) DEFAULT NULL,
  `count_product` int(11) DEFAULT NULL,
  KEY `idx_tag` (`project_category_id`,`tag_id`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_cat_tree`
--

DROP TABLE IF EXISTS `stat_cat_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_cat_tree` (
  `project_category_id` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `name_legacy` varchar(50) DEFAULT NULL,
  `is_active` int(1) DEFAULT NULL,
  `orderPos` int(11) DEFAULT NULL,
  `xdg_type` varchar(50) DEFAULT NULL,
  `dl_pling_factor` double unsigned DEFAULT '1',
  `show_description` int(1) NOT NULL DEFAULT '0',
  `depth` int(11) NOT NULL,
  `ancestor_id_path` varchar(100) DEFAULT NULL,
  `ancestor_path` varchar(256) DEFAULT NULL,
  `ancestor_path_legacy` varchar(256) DEFAULT NULL,
  `parent_active` text CHARACTER SET utf8,
  PRIMARY KEY (`project_category_id`,`lft`,`rgt`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_page_views`
--

DROP TABLE IF EXISTS `stat_page_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_page_views` (
  `stat_page_views_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `project_id` int(11) NOT NULL COMMENT 'ID of the project',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of the view',
  `ip` varchar(45) NOT NULL COMMENT 'User-IP',
  `member_id` int(11) DEFAULT NULL COMMENT 'ID of the member, if possible',
  PRIMARY KEY (`stat_page_views_id`),
  KEY `project_id` (`project_id`),
  KEY `idx_created` (`created_at`,`project_id`),
  KEY `idx_member` (`member_id`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=182722481 DEFAULT CHARSET=utf8 COMMENT='Counter of project-page views';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_page_views_mv`
--

DROP TABLE IF EXISTS `stat_page_views_mv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_page_views_mv` (
  `project_id` int(11) NOT NULL COMMENT 'ID of the project',
  `count_views` bigint(21) NOT NULL DEFAULT '0',
  `count_visitor` bigint(21) NOT NULL DEFAULT '0',
  `last_view` timestamp NULL DEFAULT NULL COMMENT 'Timestamp of the view',
  KEY `idx_project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_page_views_today_mv`
--

DROP TABLE IF EXISTS `stat_page_views_today_mv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_page_views_today_mv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL COMMENT 'ID of the project',
  `count_views` int(11) DEFAULT '0',
  `count_visitor` int(11) DEFAULT '0',
  `last_view` datetime DEFAULT NULL COMMENT 'Timestamp of the view',
  PRIMARY KEY (`id`),
  KEY `idx_project` (`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=44754 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_page_views_48h`
--

DROP TABLE IF EXISTS `stat_page_views_48h`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_page_views_48h` (
  `stat_page_views_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `project_id` int(11) NOT NULL COMMENT 'ID of the project',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of the view',
  `ip` varchar(45) NOT NULL COMMENT 'User-IP',
  `member_id` int(11) DEFAULT NULL COMMENT 'ID of the member, if possible',
  PRIMARY KEY (`stat_page_views_id`),
  KEY `project_id` (`project_id`),
  KEY `idx_created` (`created_at`,`project_id`),
  KEY `idx_member` (`member_id`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=183004927 DEFAULT CHARSET=utf8 COMMENT='Counter of project-page views';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_projects_source_url`
--

DROP TABLE IF EXISTS `stat_projects_source_url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_projects_source_url` (
  `project_id` int(11) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL DEFAULT '0',
  `source_url` longtext CHARACTER SET utf8,
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`project_id`),
  KEY `idx_proj` (`project_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_source_url` (`source_url`(50))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `v_category`
--

DROP TABLE IF EXISTS `v_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `v_category` (
  `v_category_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `project_category_id` int(11) DEFAULT '0',
  `v_parent_id` int(11) NOT NULL DEFAULT '-1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `changed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`v_category_id`,`v_parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table for virtual categories mapping';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `v_config_store_category`
--

DROP TABLE IF EXISTS `v_config_store_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `v_config_store_category` (
  `store_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `v_category_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`store_category_id`),
  KEY `project_category_id_idx` (`v_category_id`),
  KEY `fk_store_id_idx` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `v_ppload_files_downloaded`
--

DROP TABLE IF EXISTS `v_ppload_files_downloaded`;
/*!50001 DROP VIEW IF EXISTS `v_ppload_files_downloaded`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_ppload_files_downloaded` AS SELECT 
 1 AS `id`,
 1 AS `client_id`,
 1 AS `owner_id`,
 1 AS `collection_id`,
 1 AS `file_id`,
 1 AS `user_id`,
 1 AS `downloaded_timestamp`,
 1 AS `downloaded_ip`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_reported_projects`
--

DROP TABLE IF EXISTS `view_reported_projects`;
/*!50001 DROP VIEW IF EXISTS `view_reported_projects`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `view_reported_projects` AS SELECT 
 1 AS `project_id`,
 1 AS `amount_reports`,
 1 AS `latest_report`*/;
SET character_set_client = @saved_cs_client;

--
-- Dumping views for database 'pling'
--

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
-- Temporary view structure for view `stat_plings`
--

DROP TABLE IF EXISTS `stat_plings`;
/*!50001 DROP VIEW IF EXISTS `stat_plings`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `stat_plings` AS SELECT 
 1 AS `project_id`,
 1 AS `amount_received`,
 1 AS `count_plings`,
 1 AS `count_plingers`,
 1 AS `latest_pling`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `stat_plings`
--

/*!50001 DROP VIEW IF EXISTS `stat_plings`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=TEMPTABLE */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY DEFINER */
/*!50001 VIEW `stat_plings` AS select `plings`.`project_id` AS `project_id`,sum(`plings`.`amount`) AS `amount_received`,count(1) AS `count_plings`,count(distinct `plings`.`member_id`) AS `count_plingers`,max(`plings`.`active_time`) AS `latest_pling` from `plings` where (`plings`.`status_id` = 2) group by `plings`.`project_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

--
-- Dumping events for database 'pling'
--
/*!50106 SET @save_time_zone= @@TIME_ZONE */ ;
/*!50106 DROP EVENT IF EXISTS `e_generate_page_views_48h` */;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_page_views_48h` ON SCHEDULE EVERY 1 DAY STARTS '2018-11-20 05:00:00' ON COMPLETION PRESERVE ENABLE COMMENT 'Delete old page_view data from table stat_page_views_48h' DO DELETE FROM stat_page_views_48h WHERE created_at <= subdate(now(), 2) */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_page_views_today` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8 */ ;;
/*!50003 SET character_set_results = utf8 */ ;;
/*!50003 SET collation_connection  = utf8_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_page_views_today` ON SCHEDULE EVERY 30 MINUTE STARTS '2017-06-30 05:00:00' ON COMPLETION PRESERVE ENABLE COMMENT 'Regenerates page views counter for projects on every hour' DO CALL generate_stat_views_today() */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_stat_cat_prod_count` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_stat_cat_prod_count` ON SCHEDULE EVERY 5 MINUTE STARTS '2017-08-11 05:00:00' ON COMPLETION PRESERVE ENABLE COMMENT 'Regenerates generate_stat_cat_prod_count table' DO CALL generate_stat_cat_prod_count() */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_stat_cat_tree` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8 */ ;;
/*!50003 SET character_set_results = utf8 */ ;;
/*!50003 SET collation_connection  = utf8_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_stat_cat_tree` ON SCHEDULE EVERY 60 MINUTE STARTS '2017-08-11 05:00:00' ON COMPLETION PRESERVE ENABLE COMMENT 'Regenerates stat_projects table' DO CALL generate_stat_cat_tree() */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_stat_cnt_projects_catid_memberid` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_stat_cnt_projects_catid_memberid` ON SCHEDULE EVERY 1 DAY STARTS '2019-01-15 03:30:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN

	TRUNCATE TABLE stat_cnt_projects_catid_memberid;



	INSERT INTO stat_cnt_projects_catid_memberid

	select project_category_id, member_id,count(1) as cnt from project pp 

	where  pp.status = 100 and pp.type_id = 1 

	group by project_category_id,member_id;

	

END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_stat_downloads_24h` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_stat_downloads_24h` ON SCHEDULE EVERY 1 DAY STARTS '2018-11-30 01:00:00' ON COMPLETION PRESERVE ENABLE COMMENT 'Save download data for the last 24h into table stat_downloads_24' DO TRUNCATE TABLE stat_downloads_24h */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_stat_projects` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8 */ ;;
/*!50003 SET character_set_results = utf8 */ ;;
/*!50003 SET collation_connection  = utf8_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_stat_projects` ON SCHEDULE EVERY 5 MINUTE STARTS '2017-08-08 05:00:00' ON COMPLETION PRESERVE ENABLE COMMENT 'Regenerates stat_projects table' DO CALL generate_stat_project() */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_stat_projects_source_url` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_stat_projects_source_url` ON SCHEDULE EVERY 5 MINUTE STARTS '2018-11-19 11:57:15' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN



	create table stat_projects_source_url_tmp 

	(PRIMARY KEY `primary` (`project_id`)

			,INDEX `idx_proj` (`project_id`)

			,INDEX `idx_member` (`member_id`) 

			,INDEX `idx_source_url` (`source_url`(50))

	)

   ENGINE MyISAM

	as

	select p.project_id, p.member_id,TRIM(TRAILING '/' FROM p.source_url) as source_url, p.created_at, p.changed_at from stat_projects p

	where p.source_url is not null 

	and p.source_url<>'' 

	and p.status=100

	;

	rename table stat_projects_source_url to stat_projects_source_url_old;

	rename table stat_projects_source_url_tmp to stat_projects_source_url;

	drop table stat_projects_source_url_old;



END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_stat_project_ids` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_stat_project_ids` ON SCHEDULE EVERY 15 MINUTE STARTS '2019-01-23 15:43:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN

   CALL generate_stat_project_ids();

END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_stat_store_prod_count` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_stat_store_prod_count` ON SCHEDULE EVERY 10 MINUTE STARTS '2019-02-05 00:20:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN

	CALL generate_stat_store_prod_count();

END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_stta_file_tags` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_stta_file_tags` ON SCHEDULE EVERY 30 MINUTE STARTS '2019-02-04 16:35:24' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN



	DROP TABLE IF EXISTS stat_file_tags;

	CREATE TABLE stat_file_tags AS

	

	#SELECT GROUP_CONCAT(tag.tag_name) AS tags, GROUP_CONCAT(tag.tag_id) AS tag_ids,tgo.tag_object_id AS file_id, tgo.tag_parent_object_id As project_id

	#FROM tag_object AS tgo

	#JOIN tag ON tag.tag_id = tgo.tag_id

	#WHERE tag_type_id = 3 #file

	#AND tgo.is_deleted = 0

	#GROUP BY tgo.tag_object_id;

	SELECT GROUP_CONCAT(tag.tag_name) AS tags, GROUP_CONCAT(tag.tag_id) AS tag_ids,f.id AS file_id, tgo.tag_parent_object_id As project_id

	FROM ppload.ppload_files AS f

	LEFT JOIN tag_object AS tgo ON tgo.tag_object_id = f.id

	LEFT JOIN tag ON tag.tag_id = tgo.tag_id

	WHERE f.active = 1

	AND tag_type_id = 3 #file

	AND tgo.is_deleted = 0

	GROUP BY tgo.tag_object_id;

	

	ALTER TABLE `stat_file_tags`

		ADD INDEX `idx_proj_id` (`project_id`);	



END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_tmp_cat_tag_proj_init` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_tmp_cat_tag_proj_init` ON SCHEDULE EVERY 1 DAY STARTS '2019-02-19 17:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN

	call generate_tmp_cat_tag_proj_init();

END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_update_member_dl_plings_current_month` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_update_member_dl_plings_current_month` ON SCHEDULE EVERY 1 DAY STARTS '2018-06-07 01:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN



	#Generate tmp table for active projects

	DROP TABLE IF EXISTS tmp_project_for_member_dl_plings;

	CREATE TABLE tmp_project_for_member_dl_plings AS

	select * from project p where p.ppload_collection_id is not null and p.type_id = 1 and p.`status` = 100;

	

	#ppload_collection_id from char to int

	ALTER TABLE `tmp_project_for_member_dl_plings`

	CHANGE COLUMN `ppload_collection_id` `ppload_collection_id` INT NULL DEFAULT NULL COLLATE 'utf8_general_ci' AFTER `embed_code`;



	#add index

	ALTER TABLE `tmp_project_for_member_dl_plings` ADD INDEX `idx_ppload` (`ppload_collection_id`);

	ALTER TABLE `tmp_project_for_member_dl_plings` ADD INDEX `idx_pk` (`project_id`);



	#fill tmp member_dl_plings table

	DROP TABLE IF EXISTS tmp_member_dl_plings;



	CREATE TABLE tmp_member_dl_plings LIKE member_dl_plings;

		

	INSERT INTO tmp_member_dl_plings

	(SELECT * FROM stat_member_dl_curent_month);

		

	#delete plings from actual month

	DELETE FROM member_dl_plings

	WHERE yearmonth = (DATE_FORMAT(NOW(),'%Y%m'));

		

	#insert ping for this month from tmp member_dl_plings table

	INSERT INTO member_dl_plings

	(SELECT * FROM tmp_member_dl_plings);

	

	#remove tmp member_dl_plings table

	DROP TABLE tmp_member_dl_plings;



END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_update_member_dl_plings_last_month` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_update_member_dl_plings_last_month` ON SCHEDULE EVERY 1 MONTH STARTS '2017-12-01 01:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN

	

	DELETE FROM member_dl_plings

	WHERE yearmonth = (DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m'));



	INSERT INTO member_dl_plings

	(SELECT * FROM stat_member_dl_last_month);





END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `e_generate_stat_files_downloaded` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=CURRENT_USER */ /*!50106 EVENT `e_generate_stat_files_downloaded` ON SCHEDULE EVERY 1 DAY STARTS '2019-05-01 00:00:00' ON COMPLETION PRESERVE ENABLE COMMENT 'Regenerates ppload.stat_ppload_files_downloaded table' DO BEGIN

	call generate_stat_files_downloaded();

END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;

DELIMITER ;
/*!50106 SET TIME_ZONE= @save_time_zone */ ;

--
-- Dumping routines for database 'pling'
--
/*!50003 DROP FUNCTION IF EXISTS `alex_score` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  FUNCTION `alex_score`(upvotes INT, downvotes INT, count_upvotes INT, count_downvotes INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE score INT(10);
    SET score = (round(sqrt( upvotes*count_upvotes / ((upvotes + downvotes) * (count_upvotes + count_downvotes))  ),2) * 100);
	RETURN score;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `a_score` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  FUNCTION `a_score`(upvotes INT, downvotes INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE score INT(10);
    SET score = (round(sqrt(  (upvotes + 6) / (upvotes + downvotes + 12)  ),2) * 100);
	RETURN score;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `laplace_score` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  FUNCTION `laplace_score`(upvotes INT, downvotes INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE score INT(10);
    SET score = (round(((upvotes + 6) / ((upvotes + downvotes) + 12)),2) * 100);
	RETURN score;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `SPLIT_STRING` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  FUNCTION `SPLIT_STRING`( s VARCHAR(1024) , del CHAR(1) , i INT) RETURNS varchar(1024) CHARSET latin1
    DETERMINISTIC
BEGIN



        DECLARE n INT ;



        -- get max number of items

        SET n = LENGTH(s) - LENGTH(REPLACE(s, del, '')) + 1;



        IF i > n THEN

            RETURN NULL ;

        ELSE

            RETURN SUBSTRING_INDEX(SUBSTRING_INDEX(s, del, i) , del , -1 ) ;        

        END IF;



    END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `wilson_score` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  FUNCTION `wilson_score`(upvotes INT, downvotes INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE score INT(11);
	SET score = (round((((upvotes + 1.9208) / (upvotes + downvotes) - 
                   1.96 * SQRT((upvotes * downvotes) / (upvotes + downvotes) + 0.9604) / 
                          (upvotes + downvotes)) / (1 + 3.8416 / (upvotes + downvotes))),2) * 100);
	RETURN score;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `create_stat_ranking_categroy` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `create_stat_ranking_categroy`(IN `project_category_id` INT)
BEGIN





	IF(project_category_id = 0 || project_category_id IS NULL) THEN

	

		#ALL

		DELETE FROM stat_ranking_category WHERE project_category_id = 0;

	

		SET @i=0;

		insert into stat_ranking_category (

			SELECT null,0,project_id, title, (round(((p.count_likes + 6) / ((p.count_likes + p.count_dislikes) + 12)),2) * 100) as score, @i:=@i+1 AS rank 

			 FROM project p

			 WHERE p.status = 100

			 ORDER BY (round(((p.count_likes + 6) / ((p.count_likes + p.count_dislikes) + 12)),2) * 100) DESC

		);

	ELSE

		#CATEGORY

		DELETE FROM stat_ranking_category WHERE project_category_id = project_category_id;

	

		SET @i=0;

		insert into stat_ranking_category (

			SELECT null,project_category_id,project_id, title, (round(((p.count_likes + 6) / ((p.count_likes + p.count_dislikes) + 12)),2) * 100) as score, @i:=@i+1 AS rank 

			 FROM project p

			 WHERE p.status = 100

			 AND p.project_category_id = project_category_id

			 ORDER BY (round(((p.count_likes + 6) / ((p.count_likes + p.count_dislikes) + 12)),2) * 100) DESC

		);

	

	END IF;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `debug_msg` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `debug_msg`(enabled INTEGER, msg VARCHAR(255))
BEGIN

  IF enabled THEN BEGIN

    select concat("** ", msg) AS '** DEBUG:';

    #select concat("** ", msg) as log into outfile '/tmp/result.txt';

  END; END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `fetchCatTreeForStore` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `fetchCatTreeForStore`(

	IN `STORE_ID` int(11)







)
BEGIN

    DROP TABLE IF EXISTS `tmp_store_cat`;

    CREATE TEMPORARY TABLE `tmp_store_cat`

    (INDEX `idx_cat_id` (`project_category_id`) )

      ENGINE MEMORY

      AS

        SELECT `csc`.`store_id`, `csc`.`project_category_id`, `csc`.`order`, `pc`.`title`, `pc`.`lft`, `pc`.`rgt`

        FROM `config_store_category` AS `csc`

          JOIN `project_category` AS `pc` ON `pc`.`project_category_id` = `csc`.`project_category_id`

        WHERE `csc`.`store_id` = STORE_ID

        GROUP BY `csc`.`store_category_id`

        ORDER BY `csc`.`order`, `pc`.`title`

    ;



    SET @NEW_ORDER := 0;



    UPDATE `tmp_store_cat` SET `order` = (@NEW_ORDER := @NEW_ORDER + 10);



    SELECT `sct`.`lft`, `sct`.`rgt`, `sct`.`project_category_id` AS `id`, `sct`.`title`, `scpc`.`count_product` AS `product_count`, `sct`.`xdg_type`, `sct`.`name_legacy`, if(`sct`.`rgt`-`sct`.`lft` = 1, 0, 1) AS `has_children`, (SELECT `project_category_id` FROM `stat_cat_tree` AS `sct2` WHERE `sct2`.`lft` < `sct`.`lft` AND `sct2`.`rgt` > `sct`.`rgt` ORDER BY `sct2`.`rgt` - `sct`.`rgt` LIMIT 1) AS `parent_id`

    FROM `tmp_store_cat` AS `cfc`

      JOIN `stat_cat_tree` AS `sct` ON find_in_set(`cfc`.`project_category_id`, `sct`.`ancestor_id_path`)

      #JOIN `stat_cat_prod_count` AS `scpc` ON `sct`.`project_category_id` = `scpc`.`project_category_id` AND `scpc`.`tag_id` is null

      JOIN `stat_store_prod_count` AS `scpc` ON `sct`.`project_category_id` = `scpc`.`project_category_id` AND `scpc`.`tag_id` is null

    WHERE cfc.store_id = STORE_ID

    ORDER BY cfc.`order`, sct.lft;

  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `fetchCatTreeWithPackage` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `fetchCatTreeWithPackage`(IN STORE_ID int(11), IN PACKAGE_TYPE int(11))
BEGIN
	DROP TABLE IF EXISTS `tmp_store_cat`;
	CREATE TEMPORARY TABLE `tmp_store_cat`
	(INDEX `idx_cat_id` (`project_category_id`) )
	ENGINE MEMORY
	 AS
		SELECT `csc`.`store_id`, `csc`.`project_category_id`, `csc`.`order`, `pc`.`title`, `pc`.`lft`, `pc`.`rgt`
		FROM `config_store_category` AS `csc`
		JOIN `project_category` AS `pc` ON `pc`.`project_category_id` = `csc`.`project_category_id`
		WHERE `csc`.`store_id` = STORE_ID
		GROUP BY `csc`.`store_category_id`
		ORDER BY `csc`.`order`, `pc`.`title`
	;

	SET @NEW_ORDER := 0;

	UPDATE `tmp_store_cat` SET `order` = (@NEW_ORDER := @NEW_ORDER + 10);
	
	SELECT `sct`.`lft`, `sct`.`rgt`, `sct`.`project_category_id` AS `id`, `sct`.`title`, `scpc`.`count_product` AS `product_count`, `sct`.`xdg_type`, `sct`.`name_legacy`, if(`sct`.`rgt`-`sct`.`lft` = 1, 0, 1) AS `has_children`, (SELECT `project_category_id` FROM `stat_cat_tree` AS `sct2` WHERE `sct2`.`lft` < `sct`.`lft` AND `sct2`.`rgt` > `sct`.`rgt` ORDER BY `sct2`.`rgt` - `sct`.`rgt` LIMIT 1) AS `parent_id` 
	FROM `tmp_store_cat` AS `cfc`
	JOIN `stat_cat_tree` AS `sct` ON find_in_set(`cfc`.`project_category_id`, `sct`.`ancestor_id_path`)
	JOIN `stat_cat_prod_count` AS `scpc` ON `sct`.`project_category_id` = `scpc`.`project_category_id` AND `scpc`.`package_type_id` = PACKAGE_TYPE
	WHERE `cfc`.`store_id` = STORE_ID
	ORDER BY `cfc`.`order`, `sct`.`lft`;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `fetchCatTreeWithTags` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `fetchCatTreeWithTags`(IN STORE_ID int(11), IN TAGS VARCHAR(255))
BEGIN
    DROP TABLE IF EXISTS `tmp_store_cat_tags`;
    CREATE TEMPORARY TABLE `tmp_store_cat_tags`
    (INDEX `idx_cat_id` (`project_category_id`) )
      ENGINE MEMORY
      AS
        SELECT `csc`.`store_id`, `csc`.`project_category_id`, `csc`.`order`, `pc`.`title`, `pc`.`lft`, `pc`.`rgt`
        FROM `config_store_category` AS `csc`
          JOIN `project_category` AS `pc` ON `pc`.`project_category_id` = `csc`.`project_category_id`
        WHERE `csc`.`store_id` = STORE_ID
        GROUP BY `csc`.`store_category_id`
        ORDER BY `csc`.`order`, `pc`.`title`
    ;

    SET @NEW_ORDER := 0;

    UPDATE `tmp_store_cat_tags` SET `order` = (@NEW_ORDER := @NEW_ORDER + 10);

    SELECT `sct`.`lft`, `sct`.`rgt`, `sct`.`project_category_id` AS `id`, `sct`.`title`, `scpc`.`count_product` AS `product_count`, `sct`.`xdg_type`, `sct`.`name_legacy`, if(`sct`.`rgt`-`sct`.`lft` = 1, 0, 1) AS `has_children`, (SELECT `project_category_id` FROM `stat_cat_tree` AS `sct2` WHERE `sct2`.`lft` < `sct`.`lft` AND `sct2`.`rgt` > `sct`.`rgt` ORDER BY `sct2`.`rgt` - `sct`.`rgt` LIMIT 1) AS `parent_id`
    FROM `tmp_store_cat_tags` AS `cfc`
      JOIN `stat_cat_tree` AS `sct` ON find_in_set(`cfc`.`project_category_id`, `sct`.`ancestor_id_path`)
      JOIN `stat_cat_prod_count` AS `scpc` ON `sct`.`project_category_id` = `scpc`.`project_category_id` AND `scpc`.`tag_id` = TAGS
    WHERE `cfc`.`store_id` = STORE_ID
    ORDER BY `cfc`.`order`, `sct`.`lft`;
  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `fetchCatTreeWithTagsForStore` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `fetchCatTreeWithTagsForStore`(

	IN `STORE_ID` INT(11),

	IN `TAGS` VARCHAR(255)



)
BEGIN

    DROP TABLE IF EXISTS `tmp_store_cat_tags`;

    CREATE TEMPORARY TABLE `tmp_store_cat_tags`

    (

      INDEX `idx_cat_id` (`project_category_id`)

    )

      ENGINE MEMORY

      AS

        SELECT

          `csc`.`store_id`,

          `csc`.`project_category_id`,

          `csc`.`order`,

          `pc`.`title`,

          `pc`.`lft`,

          `pc`.`rgt`

        FROM `config_store_category` AS `csc`

          JOIN `project_category` AS `pc` ON `pc`.`project_category_id` = `csc`.`project_category_id`

        WHERE `csc`.`store_id` = STORE_ID

        GROUP BY `csc`.`store_category_id`

        ORDER BY `csc`.`order`, `pc`.`title`;



    SET @`NEW_ORDER` := 0;



    UPDATE `tmp_store_cat_tags`

    SET `order` = (@`NEW_ORDER` := @`NEW_ORDER` + 10);



    SELECT

      `sct`.`lft`,

      `sct`.`rgt`,

      `sct`.`project_category_id`             AS `id`,

      `sct`.`title`,

      `scpc`.`count_product`                  AS `product_count`,

      `sct`.`xdg_type`,

      `sct`.`name_legacy`,

      if(`sct`.`rgt` - `sct`.`lft` = 1, 0, 1) AS `has_children`,

      (SELECT `project_category_id`

       FROM `stat_cat_tree` AS `sct2`

       WHERE `sct2`.`lft` < `sct`.`lft` AND `sct2`.`rgt` > `sct`.`rgt`

       ORDER BY `sct2`.`rgt` - `sct`.`rgt`

       LIMIT 1)                               AS `parent_id`

    FROM `tmp_store_cat_tags` AS `cfc`

      JOIN `stat_cat_tree` AS `sct` ON find_in_set(`cfc`.`project_category_id`, `sct`.`ancestor_id_path`)

      #LEFT JOIN `stat_cat_prod_count` AS `scpc` ON `sct`.`project_category_id` = `scpc`.`project_category_id` AND `scpc`.`tag_id` = TAGS

      JOIN `stat_store_prod_count` AS `scpc` ON `sct`.`project_category_id` = `scpc`.`project_category_id` AND `scpc`.`stores` = STORE_ID

    WHERE `cfc`.`store_id` = STORE_ID

    ORDER BY `cfc`.`order`, `sct`.`lft`;

  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_member_score` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_member_score`()
BEGIN



	truncate table member_score;



	#init

	insert into member_score 

	(

		select 

			null as member_score_id,

			m.member_id,

			0 as score,

			(	

				select 

					count(1) as anz 

				from project p 

				where p.member_id = m.member_id 

				and p.type_id = 1 

				and p.`status` = 100

			) as count_product,

			0 as count_pling,

			0 as count_like,

			0 as count_comment,

			YEAR(now()) - YEAR(m.created_at) as count_years_membership,

			0 as count_report_product_spam,

			0 as count_report_product_fraud,

			0 as count_report_comment,

			0 as count_report_member,

			now() as created_at

		

		from member m

		where m.is_active = 1

		and m.is_deleted = 0

	);

	

	

	#count_pling

	update member_score m

	join (	

				select 

					count(1) as anz, p.member_id

				from project p

				join project_plings pl on pl.project_id = p.project_id

				where pl.is_active = 1 

				and pl.is_deleted = 0

				and p.`status` = 100

				and p.type_id = 1 

				and pl.member_id <> 497632 #cc: 376856 , live: 497632

				group by p.member_id

			) count_pling on count_pling.member_id = m.member_id

	set m.count_pling = count_pling.anz

	where count_pling.anz > 0;

	

	

	

	

	

	#count_like

	update member_score m

	join (	

				select 

					count(1) as anz, p.member_id

				from project p

				join project_follower pf on pf.project_id = p.project_id

				where p.`status` = 100

				and p.type_id = 1 

				group by p.member_id

			) count_like on count_like.member_id = m.member_id

	set m.count_like = count_like.anz

	where count_like.anz > 0;

	

	

	#count_comment

	update member_score m

	join (	

				select 

					count(1) as anz, c.comment_member_id as member_id

				from comments c

				where c.comment_type = 0

				and c.comment_active = 1

				group by c.comment_member_id

			) count_comment on count_comment.member_id = m.member_id

	set m.count_comment = count_comment.anz

	where count_comment.anz > 0;

	

	

	#count_report_product_spam

	update member_score m

	join (	

				select count(1) as anz, pro.member_id from reports_project p

				inner join project pro on pro.project_id = p.project_id

				where p.is_deleted = 0

				and p.report_type = 0

				and pro.`status` = 100

				group by pro.member_id

			) count_report_product_spam on count_report_product_spam.member_id = m.member_id

	set m.count_report_product_spam = count_report_product_spam.anz

	where count_report_product_spam.anz > 0;

	

	

	#count_report_product_fraud

	update member_score m

	join (	

				select count(1) as anz, pro.member_id from reports_project p

				inner join project pro on pro.project_id = p.project_id

				where p.is_deleted = 0

				and p.report_type = 1

				and pro.`status` = 100

				group by pro.member_id

			) count_report_product_fraud on count_report_product_fraud.member_id = m.member_id

	set m.count_report_product_fraud = count_report_product_fraud.anz

	where count_report_product_fraud.anz > 0;

	

	#score

	update member_score m

	join (select value from member_score_factors f where f.factor_id = 1) as factor_prod

	join (select value from member_score_factors f where f.factor_id = 2) as factor_pling

	join (select value from member_score_factors f where f.factor_id = 3) as factor_like

	join (select value from member_score_factors f where f.factor_id = 4) as factor_comment

	join (select value from member_score_factors f where f.factor_id = 5) as factor_year

	join (select value from member_score_factors f where f.factor_id = 6) as factor_report_prod_spam

	join (select value from member_score_factors f where f.factor_id = 7) as factor_report_prod_fraud

	set m.score = (m.count_product*factor_prod.value) 

					+ (m.count_pling*factor_pling.value)  

					+ (m.count_like*factor_like.value) 

					+ (m.count_comment*factor_comment.value) 

					+ (m.count_years_membership*factor_year.value)  

					+ (m.count_report_product_spam*factor_report_prod_spam.value)  

					+ (m.count_report_product_fraud*factor_report_prod_fraud.value)  

	;







END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_stat_cat_prod_count` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_stat_cat_prod_count`()
BEGIN



    DROP TABLE IF EXISTS tmp_stat_cat_prod_count;

    CREATE TABLE tmp_stat_cat_prod_count

    (

      `project_category_id` int(11) NOT NULL,

      `tag_id` VARCHAR(255) NULL,

      `count_product` int(11) NULL,

      INDEX `idx_tag` (`project_category_id`,`tag_id`)

    )

      ENGINE Memory

      AS

        SELECT

          sct2.project_category_id,

          NULL as tag_id,

          count(distinct p.project_id) as count_product

        FROM stat_cat_tree as sct1

          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt

          LEFT JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id

        WHERE p.amount_reports is null

        GROUP BY sct2.project_category_id

        

        UNION

        



        SELECT

          sct2.project_category_id,

          tg.tag_ids as tag_id,

          count(distinct p.project_id) as count_product

        FROM stat_cat_tree as sct1

          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt

          JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id

          JOIN (select cs.store_id, GROUP_CONCAT(ct.tag_id ORDER BY ct.tag_id) as tag_ids from config_store cs

				join config_store_tag ct on ct.store_id = cs.store_id and ct.is_active = 1

				group by cs.store_id) tg 

			 JOIN (

			 

				 SELECT DISTINCT project_id,tag_ids 

				 FROM stat_project_tagids 

				 JOIN (select cs.store_id, GROUP_CONCAT(ct.tag_id ORDER BY ct.tag_id) as tag_ids from config_store cs

					join config_store_tag ct on ct.store_id = cs.store_id and ct.is_active = 1

					group by cs.store_id) tg WHERE tag_id in (tg.tag_ids)

					

				

			  ) AS store_tags ON p.project_id = store_tags.project_id AND store_tags.tag_ids = tg.tag_ids

          JOIN tag_object AS ppt ON 

			   (ppt.tag_parent_object_id = p.project_id AND ppt.tag_type_id = 3) AND ppt.is_deleted = 0

          JOIN ppload.ppload_files AS files ON files.id = ppt.tag_object_id AND files.active = 1

        WHERE p.amount_reports is null

        GROUP BY sct2.lft, tg.tag_ids

        

        UNION

        

        

        SELECT

          sct2.project_category_id,

          tg.tag_ids as tag_id,

          count(distinct p.project_id) as count_product

        FROM stat_cat_tree as sct1

          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt

          JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id

          JOIN (select cs.store_id, GROUP_CONCAT(ct.tag_id ORDER BY ct.tag_id) as tag_ids from config_store cs

				join config_store_tag ct on ct.store_id = cs.store_id and ct.is_active = 1

				group by cs.store_id) tg 

			 JOIN (

			 

				 SELECT DISTINCT project_id,tag_ids 

				 FROM stat_project_tagids 

				 JOIN (select cs.store_id, GROUP_CONCAT(ct.tag_id ORDER BY ct.tag_id) as tag_ids from config_store cs

					join config_store_tag ct on ct.store_id = cs.store_id and ct.is_active = 1

					group by cs.store_id) tg WHERE tag_id in (tg.tag_ids)

					

				

			  ) AS store_tags ON p.project_id = store_tags.project_id AND store_tags.tag_ids = tg.tag_ids

          JOIN tag_object AS ppt ON 

			   (ppt.tag_object_id = p.project_id) AND ppt.is_deleted = 0

          JOIN ppload.ppload_files AS files ON files.id = ppt.tag_object_id AND files.active = 1

        WHERE p.amount_reports is null

        GROUP BY sct2.lft, tg.tag_ids

    ;



    IF EXISTS(SELECT table_name

              FROM INFORMATION_SCHEMA.TABLES

              WHERE table_schema = DATABASE()

                    AND table_name = 'stat_cat_prod_count')



    THEN



      RENAME TABLE stat_cat_prod_count TO old_stat_cat_prod_count, tmp_stat_cat_prod_count TO stat_cat_prod_count;



    ELSE



      RENAME TABLE tmp_stat_cat_prod_count TO stat_cat_prod_count;



    END IF;





    DROP TABLE IF EXISTS old_stat_cat_prod_count;



  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_stat_cat_prod_count_w_spam` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_stat_cat_prod_count_w_spam`()
BEGIN



    DROP TABLE IF EXISTS `tmp_stat_cat_prod_count_w_spam`;

    CREATE TABLE tmp_stat_cat_prod_count_w_spam

    (

      `project_category_id` int(11) NOT NULL,

      `tag_id` int(11) NULL,

      `count_product` int(11) NULL,

      INDEX `idx_tag` (`project_category_id`,`tag_id`)

    )

      ENGINE Memory

      AS

        SELECT

          sct2.project_category_id,

          NULL as tag_id,

          count(distinct p.project_id) as count_product

        FROM stat_cat_tree as sct1

          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt

          LEFT JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id

        GROUP BY sct2.project_category_id



        UNION



        SELECT

          sct2.project_category_id,

          tg.tag_ids as tag_id,

          count(distinct p.project_id) as count_product

        FROM stat_cat_tree as sct1

          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt

          JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id

          JOIN (select cs.store_id, GROUP_CONCAT(ct.tag_id) as tag_ids from config_store cs

				join config_store_tag ct on ct.store_id = cs.store_id and ct.is_active = 1

				group by cs.store_id) tg 

			 JOIN (

			 

				 SELECT DISTINCT project_id,tag_ids 

				 FROM stat_project_tagids 

				 JOIN (select cs.store_id, GROUP_CONCAT(ct.tag_id) as tag_ids from config_store cs

					join config_store_tag ct on ct.store_id = cs.store_id and ct.is_active = 1

					group by cs.store_id) tg WHERE tag_id in (tg.tag_ids)

					

				

			  ) AS store_tags ON p.project_id = store_tags.project_id AND store_tags.tag_ids = tg.tag_ids

          JOIN tag_object AS ppt ON 

			   (ppt.tag_parent_object_id = p.project_id AND ppt.tag_type_id = 3) AND ppt.is_deleted = 0

          JOIN ppload.ppload_files AS files ON files.id = ppt.tag_object_id AND files.active = 1

        

        GROUP BY sct2.lft, tg.tag_ids

        

        

        UNION



        SELECT

          sct2.project_category_id,

          tg.tag_ids as tag_id,

          count(distinct p.project_id) as count_product

        FROM stat_cat_tree as sct1

          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt

          JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id

          JOIN (select cs.store_id, GROUP_CONCAT(ct.tag_id) as tag_ids from config_store cs

				join config_store_tag ct on ct.store_id = cs.store_id and ct.is_active = 1

				group by cs.store_id) tg 

			 JOIN (

			 

				 SELECT DISTINCT project_id,tag_ids 

				 FROM stat_project_tagids 

				 JOIN (select cs.store_id, GROUP_CONCAT(ct.tag_id) as tag_ids from config_store cs

					join config_store_tag ct on ct.store_id = cs.store_id and ct.is_active = 1

					group by cs.store_id) tg WHERE tag_id in (tg.tag_ids)

					

				

			  ) AS store_tags ON p.project_id = store_tags.project_id AND store_tags.tag_ids = tg.tag_ids

          JOIN tag_object AS ppt ON 

			   (ppt.tag_object_id = p.project_id) AND ppt.is_deleted = 0

          JOIN ppload.ppload_files AS files ON files.id = ppt.tag_object_id AND files.active = 1

        

        GROUP BY sct2.lft, tg.tag_ids

    ;



    IF EXISTS(SELECT table_name

              FROM INFORMATION_SCHEMA.TABLES

              WHERE table_schema = DATABASE()

                    AND table_name = 'stat_cat_prod_count_w_spam')



    THEN



      RENAME TABLE stat_cat_prod_count_w_spam TO old_stat_cat_prod_count_w_spam, tmp_stat_cat_prod_count_w_spam TO stat_cat_prod_count_w_spam;



    ELSE



      RENAME TABLE tmp_stat_cat_prod_count_w_spam TO stat_cat_prod_count_w_spam;



    END IF;





    DROP TABLE IF EXISTS old_stat_cat_prod_count_w_spam;



  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_stat_cat_tree` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_stat_cat_tree`()
BEGIN

    DROP TABLE IF EXISTS tmp_stat_cat_tree;
    CREATE TABLE tmp_stat_cat_tree
    (
      `project_category_id` int(11) NOT NULL,
      `lft` int(11) NOT NULL,
      `rgt` int(11) NOT NULL,
      `title` varchar(100) NOT NULL,
      `name_legacy` varchar(50) NULL,
      `is_active` int(1),
      `orderPos` int(11) NULL,
      `xdg_type` varchar(50) NULL,
      `dl_pling_factor` double unsigned DEFAULT '1',
      `show_description` int(1) NOT NULL DEFAULT '0',
      `depth` int(11) NOT NULL,
      `ancestor_id_path` varchar(100),
      `ancestor_path` varchar(256),
      `ancestor_path_legacy` varchar(256),
      PRIMARY KEY `primary` (project_category_id, lft, rgt)
    )
      AS
        SELECT
          pc.project_category_id,
          pc.lft,
          pc.rgt,
          pc.title,
          pc.name_legacy,
          pc.is_active,
          pc.orderPos,
          pc.xdg_type,
          pc.dl_pling_factor,
          pc.show_description,
          count(pc.lft) - 1                                        AS depth,
          GROUP_CONCAT(pc2.project_category_id ORDER BY pc2.lft)   AS ancestor_id_path,
          GROUP_CONCAT(pc2.title ORDER BY pc2.lft SEPARATOR ' | ') AS ancestor_path,
          GROUP_CONCAT(IF(LENGTH(TRIM(pc2.name_legacy))>0,pc2.name_legacy,pc2.title) ORDER BY pc2.lft SEPARATOR ' | ') AS ancestor_path_legacy,
		  GROUP_CONCAT(pc2.is_active ORDER BY pc2.lft) AS parent_active
        FROM project_category AS pc, project_category AS pc2
        WHERE (pc.lft BETWEEN pc2.lft AND pc2.rgt) -- AND pc.is_active = 1 and pc2.is_active = 1
        GROUP BY pc.lft -- HAVING depth >= 1
        HAVING NOT FIND_IN_SET('0', parent_active)
        ORDER BY pc.lft, pc.orderPos
  ;

    IF EXISTS(SELECT table_name
              FROM INFORMATION_SCHEMA.TABLES
              WHERE table_schema = DATABASE()
                    AND table_name = 'stat_cat_tree')

    THEN

      RENAME TABLE stat_cat_tree TO old_stat_cat_tree, tmp_stat_cat_tree TO stat_cat_tree;

    ELSE

      RENAME TABLE tmp_stat_cat_tree TO stat_cat_tree;

    END IF;


    DROP TABLE IF EXISTS old_stat_cat_tree;

  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_stat_downloads_24h` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_stat_downloads_24h`()
BEGIN



	DECLARE exit handler for sqlexception

	  BEGIN

	    -- ERROR

	  ROLLBACK;

	END;



	START TRANSACTION;



    DROP TABLE IF EXISTS `temp_stat_downloads_24h`;

    

    CREATE TABLE `temp_stat_downloads_24h` (

      `anz`            BIGINT,

      `collection_id`  INT(11) NOT NULL,

      `project_id`     INT(11)  NOT NULL,

      INDEX `idx_project` (`project_id` ASC)

    )

      ENGINE = MyISAM

    AS

      select count(1) as anz, f.collection_id, p.project_id from ppload.ppload_files_downloaded f

		join project p on p.ppload_collection_id = f.collection_id AND p.`status` = 100

		where f.downloaded_timestamp >= subdate(now(), 1)

		group by f.collection_id, p.project_id;



    ALTER TABLE `stat_downloads_24h`

      RENAME TO  `old_stat_downloads_24h` ;



    ALTER TABLE `temp_stat_downloads_24h`

      RENAME TO  `stat_downloads_24h` ;



    DROP TABLE `old_stat_downloads_24h`;

    

    COMMIT;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_stat_project` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_stat_project`()
BEGIN

    DROP TABLE IF EXISTS tmp_reported_projects;

    CREATE TEMPORARY TABLE tmp_reported_projects

    (PRIMARY KEY `primary` (project_id) )

      AS

        SELECT

          `reports_project`.`project_id` AS `project_id`,

          COUNT(`reports_project`.`project_id`) AS `amount_reports`,

          MAX(`reports_project`.`created_at`) AS `latest_report`

        FROM

          `reports_project`

        WHERE

          (`reports_project`.`is_deleted` = 0 AND `reports_project`.`report_type` = 0)

        GROUP BY `reports_project`.`project_id`

    ;



    DROP TABLE IF EXISTS tmp_project_package_types;

    CREATE TEMPORARY TABLE tmp_project_package_types

    (PRIMARY KEY `primary` (project_id))

      ENGINE MyISAM

      AS

        SELECT

          tag_object.tag_parent_object_id as project_id,

          GROUP_CONCAT(DISTINCT tag_object.tag_id) AS package_type_id_list,

          GROUP_CONCAT(DISTINCT tag.tag_fullname) AS `package_name_list`

        FROM

          tag_object

          JOIN

          tag ON tag_object.tag_id = tag.tag_id

          JOIN

          ppload.ppload_files files ON files.id = tag_object.tag_object_id

        WHERE

        	 tag_object.tag_group_id = 8

          AND tag_object.is_deleted = 0

          AND files.active = 1

        GROUP BY tag_object.tag_parent_object_id

    ;



    DROP TABLE IF EXISTS tmp_project_tags;

    CREATE TEMPORARY TABLE tmp_project_tags

    (PRIMARY KEY `primary` (tag_project_id))

      ENGINE MyISAM

      AS

         SELECT 

             GROUP_CONCAT(tag_name) AS tag_names, 

			    GROUP_CONCAT(tag_id) AS tag_ids, 

				 tag_project_id

			FROM (        

				select 

				    distinct tag.tag_name, 

				    tag.tag_id, 

					 tgo.tag_object_id AS tag_project_id        

				FROM tag_object AS tgo

				JOIN tag ON tag.tag_id = tgo.tag_id

				WHERE tag_type_id = 1 #project     

				AND tgo.is_deleted = 0   

				UNION ALL        

				select 

				    distinct tag.tag_name, 

				    tag.tag_ID, 

					 tgo.tag_parent_object_id AS tag_project_id        

				FROM tag_object AS tgo

				JOIN tag ON tag.tag_id = tgo.tag_id

				JOIN ppload.ppload_files files ON files.id = tgo.tag_object_id

				WHERE tag_type_id = 3 #file

				AND files.active = 1

				AND tgo.is_deleted = 0

		) A

		GROUP BY tag_project_id

		ORDER BY tag_project_id;



    DROP TABLE IF EXISTS tmp_stat_projects;

    CREATE TABLE tmp_stat_projects

    (PRIMARY KEY `primary` (`project_id`), INDEX `idx_ppload` (`ppload_collection_id`), INDEX `idx_cat` (`project_category_id`),INDEX `idx_member` (`member_id`),INDEX `idx_source_url` (`source_url`(50)))

      ENGINE MyISAM

      AS

        SELECT

          `project`.`project_id` AS `project_id`,

          `project`.`member_id` AS `member_id`,

          `project`.`content_type` AS `content_type`,

          `project`.`project_category_id` AS `project_category_id`,

          `project`.`hive_category_id` AS `hive_category_id`,

          `project`.`status` AS `status`,

          `project`.`uuid` AS `uuid`,

          `project`.`pid` AS `pid`,

          `project`.`type_id` AS `type_id`,

          `project`.`title` AS `title`,

          `project`.`description` AS `description`,

          `project`.`version` AS `version`,

          `project`.`project_license_id` AS `project_license_id`,

          `project`.`image_big` AS `image_big`,

          `project`.`image_small` AS `image_small`,

          `project`.`start_date` AS `start_date`,

          `project`.`content_url` AS `content_url`,

          `project`.`created_at` AS `created_at`,

          `project`.`changed_at` AS `changed_at`,

          `project`.`major_updated_at` AS `major_updated_at`,

          `project`.`deleted_at` AS `deleted_at`,

          `project`.`creator_id` AS `creator_id`,

          `project`.`facebook_code` AS `facebook_code`,

          `project`.`source_url` AS `source_url`,

          `project`.`twitter_code` AS `twitter_code`,

          `project`.`google_code` AS `google_code`,

          `project`.`link_1` AS `link_1`,

          `project`.`embed_code` AS `embed_code`,

          CAST(`project`.`ppload_collection_id` AS UNSIGNED) AS `ppload_collection_id`,

          `project`.`validated` AS `validated`,

          `project`.`validated_at` AS `validated_at`,

          `project`.`featured` AS `featured`,

          `project`.`ghns_excluded` AS `ghns_excluded`,

          `project`.`amount` AS `amount`,

          `project`.`amount_period` AS `amount_period`,

          `project`.`claimable` AS `claimable`,

          `project`.`claimed_by_member` AS `claimed_by_member`,

          `project`.`count_likes` AS `count_likes`,

          `project`.`count_dislikes` AS `count_dislikes`,

          `project`.`count_comments` AS `count_comments`,

          `project`.`count_downloads_hive` AS `count_downloads_hive`,

          `project`.`source_id` AS `source_id`,

          `project`.`source_pk` AS `source_pk`,

          `project`.`source_type` AS `source_type`,

          `project`.`validated` AS `project_validated`,

          `project`.`uuid` AS `project_uuid`,

          `project`.`status` AS `project_status`,

          `project`.`created_at` AS `project_created_at`,

          `project`.`changed_at` AS `project_changed_at`,

          laplace_score(`project`.`count_likes`, `project`.`count_dislikes`) AS `laplace_score`,

          `member`.`type` AS `member_type`,

          `member`.`member_id` AS `project_member_id`,

          `member`.`username` AS `username`,

          `member`.`profile_image_url` AS `profile_image_url`,

          `member`.`city` AS `city`,

          `member`.`country` AS `country`,

          `member`.`created_at` AS `member_created_at`,

          `member`.`paypal_mail` AS `paypal_mail`,

          `project_category`.`title` AS `cat_title`,

          `project_category`.`xdg_type` AS `cat_xdg_type`,

          `project_category`.`name_legacy` AS `cat_name_legacy`,

          `project_category`.`show_description` AS `cat_show_description`,

          `stat_plings`.`amount_received` AS `amount_received`,

          `stat_plings`.`count_plings` AS `count_plings`,

          `stat_plings`.`count_plingers` AS `count_plingers`,

          `stat_plings`.`latest_pling` AS `latest_pling`,

          `trp`.`amount_reports` AS `amount_reports`,

          `tppt`.`package_type_id_list` AS `package_types`,

          `tppt`.`package_name_list` AS `package_names`,

          `t`.`tag_names` AS `tags`,

          `t`.`tag_ids` AS `tag_ids`,

          `sdqy`.amount AS count_downloads_quarter,

          `project_license`.title AS project_license_title

        FROM

          `project`

          JOIN `member` ON `member`.`member_id` = `project`.`member_id`

          JOIN `project_category` ON `project`.`project_category_id` = `project_category`.`project_category_id`

          LEFT JOIN `stat_plings` ON `stat_plings`.`project_id` = `project`.`project_id`

          LEFT JOIN `tmp_reported_projects` AS trp ON `trp`.`project_id` = `project`.`project_id`

          LEFT JOIN `tmp_project_package_types` AS tppt ON tppt.project_id = `project`.project_id

          LEFT JOIN `tmp_project_tags` AS t ON t.`tag_project_id` = project.`project_id`

          LEFT JOIN `stat_downloads_quarter_year` AS sdqy ON sdqy.project_id = project.project_id

          LEFT JOIN `project_license` ON project_license.project_license_id = project.project_license_id

        WHERE

          `member`.`is_deleted` = 0

          AND `member`.`is_active` = 1

          AND (`project`.`type_id` = 1 OR `project`.`type_id` = 3)

          AND `project`.`status` = 100

          AND `project_category`.`is_active` = 1

    ;

    

    RENAME TABLE stat_projects TO old_stat_projects, tmp_stat_projects TO stat_projects;



    DROP TABLE IF EXISTS old_stat_projects;

  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_stat_project_ids` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_stat_project_ids`()
BEGIN

	DROP TABLE IF EXISTS tmp_stat_project_tagids;

	CREATE TABLE tmp_stat_project_tagids

	(INDEX `idx_tag_id` (`tag_id`),INDEX `idx_project_id` (`project_id`))

	ENGINE MyISAM

	AS

	

	select distinct tag_id, project_id from (

	

		select distinct tag.tag_id, tgo.tag_object_id AS project_id        

		FROM tag_object AS tgo

		JOIN tag ON tag.tag_id = tgo.tag_id

		WHERE tag_type_id = 1 #project 

		AND tgo.is_deleted = 0       

		UNION ALL        

		select distinct tag.tag_id, tgo.tag_parent_object_id AS project_id        

		FROM tag_object AS tgo

		JOIN tag ON tag.tag_id = tgo.tag_id

		JOIN ppload.ppload_files files ON files.id = tgo.tag_object_id

		WHERE tag_type_id = 3 #file

		AND files.active = 1

		AND tgo.is_deleted = 0

	) A

	;

	RENAME TABLE stat_project_tagids TO old_stat_project_tagids, tmp_stat_project_tagids TO stat_project_tagids;

	DROP TABLE IF EXISTS old_stat_project_tagids;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_stat_store_prod_count` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_stat_store_prod_count`()
BEGIN

		      

DECLARE v_finished INTEGER DEFAULT 0;

DECLARE v_store_id varchar(255) DEFAULT "";

DECLARE v_store_tag_ids varchar(255) DEFAULT "";



declare idx,prev_idx int;

declare v_id varchar(10);

 

-- declare cursor for employee email

DECLARE store_cursor CURSOR FOR 

 SELECT * FROM tmp_stat_store_tagids;

 

-- declare NOT FOUND handler

DECLARE CONTINUE HANDLER 

FOR NOT FOUND SET v_finished = 1;





DROP TABLE IF EXISTS `tmp_stat_store_tagids`;

CREATE TEMPORARY TABLE `tmp_stat_store_tagids`

AS

SELECT

 `cs`.`store_id`,

 GROUP_CONCAT(`ct`.`tag_id`

              ORDER BY `ct`.`tag_id`) AS `tag_ids`

FROM

 `config_store` `cs`

JOIN

 `config_store_tag` `ct` ON `ct`.`store_id` = `cs`.`store_id`

AND `ct`.`is_active` = 1

#WHERE `cs`.`store_id` = 7

GROUP BY `cs`.`store_id`;





DROP TABLE IF EXISTS `tmp_stat_store_prod_count`;

 CREATE TABLE `tmp_stat_store_prod_count`

 (

   `project_category_id` INT(11)      NOT NULL,

   `tag_id`              VARCHAR(255) NULL,

   `count_product`       INT(11)      NULL,

   `stores`              VARCHAR(255) NULL,

   INDEX `idx_tag` (`project_category_id`, `tag_id`)

 )

   ENGINE MyISAM

   AS

     SELECT

          sct2.project_category_id,

          NULL as tag_id,

          count(distinct p.project_id) as count_product,

          NULL as stores

        FROM stat_cat_tree as sct1

          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt

          LEFT JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id

        WHERE p.amount_reports is null

        GROUP BY sct2.project_category_id;

        



OPEN store_cursor;



get_store: LOOP

 

 FETCH store_cursor INTO v_store_id, v_store_tag_ids;

 

 IF v_finished = 1 THEN 

 LEAVE get_store;

 END IF;

 

 -- build email list

 

 

  SET @sql = '

      INSERT INTO tmp_stat_store_prod_count

        SELECT

          sct2.project_category_id,

          tg.tag_ids as tag_id,

          count(distinct p.project_id) as count_product,

          tg.store_id

        FROM stat_cat_tree as sct1

          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt

          JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id

          JOIN tmp_stat_store_tagids tg

          

        WHERE p.amount_reports is null

        ';

   SET @sql = CONCAT(@sql,' AND tg.store_id = ', v_store_id, ' ');     

   SET @sql = CONCAT(@sql,' AND (1=1 ');  

	

	set idx := locate(',',v_store_tag_ids,1);

	

	if LENGTH(v_store_tag_ids) > 0 then

	

		if idx > 0 then

			set prev_idx := 1;

			WHILE idx > 0 DO

			 set v_id := substr(v_store_tag_ids,prev_idx,idx-prev_idx);

			 SET @sql = CONCAT(@sql,' AND FIND_IN_SET(', v_id, ', p.tag_ids) ');

			 set prev_idx := idx+1;

			 set idx := locate(',',v_store_tag_ids,prev_idx);

			 

			 if idx = 0 then

			 	set v_id := substr(v_store_tag_ids,prev_idx);

			 	SET @sql = CONCAT(@sql,' AND FIND_IN_SET(', v_id, ', p.tag_ids) ');

			 end if;

			 

			 

			END WHILE;   

		else 

		

			SET @sql = CONCAT(@sql,' AND FIND_IN_SET(', v_store_tag_ids, ', p.tag_ids) ');

		

		end if;

	end if;

        

    SET @sql = CONCAT(@sql,') ');     

    SET @sql = CONCAT(@sql,'GROUP BY sct2.lft, tg.tag_ids,tg.store_id');

    

    #select @sql;

    

    PREPARE stmt FROM @sql;

    EXECUTE stmt;

    DEALLOCATE PREPARE stmt;

 

 END LOOP get_store;



CLOSE store_cursor;





 IF EXISTS(SELECT `table_name`

              FROM `INFORMATION_SCHEMA`.`TABLES`

              WHERE `table_schema` = DATABASE()

                    AND `table_name` = 'stat_store_prod_count')



    THEN



      RENAME TABLE

          `stat_store_prod_count` TO `old_stat_store_prod_count`,

          `tmp_stat_store_prod_count` TO `stat_store_prod_count`;



    ELSE



      RENAME TABLE

          `tmp_stat_store_prod_count` TO `stat_store_prod_count`;



    END IF;





    DROP TABLE IF EXISTS `old_stat_store_prod_count`;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_stat_views_today` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_stat_views_today`()
BEGIN

    DROP TABLE IF EXISTS `temp_stat_views_today`;

    CREATE TABLE `temp_stat_views_today` (
      `id`            INT      NOT NULL AUTO_INCREMENT,
      `project_id`    INT(11)  NOT NULL,
      `count_views`   INT(11)  NULL     DEFAULT 0,
      `count_visitor` INT(11)  NULL     DEFAULT 0,
      `last_view`     DATETIME NULL     DEFAULT NULL,
      PRIMARY KEY (`id`),
      INDEX `idx_project` (`project_id` ASC)
    )
      ENGINE = MyISAM
    AS
      SELECT
        project_id,
        COUNT(*)                               AS count_views,
        COUNT(DISTINCT `stat_page_views`.`ip`) AS `count_visitor`,
        MAX(`stat_page_views`.`created_at`)    AS `last_view`
      FROM stat_page_views
      WHERE (stat_page_views.`created_at`
      BETWEEN DATE_FORMAT(NOW(), '%Y-%m-%d 00:00') AND DATE_FORMAT(NOW(), '%Y-%m-%d 23:59')
      )
      GROUP BY project_id;

    IF EXISTS(SELECT table_name
              FROM INFORMATION_SCHEMA.TABLES
              WHERE table_schema = DATABASE()
                    AND table_name = 'stat_page_views_today_mv')

    THEN

      ALTER TABLE `stat_page_views_today_mv`
        RENAME TO `old_stat_views_today_mv`;

    END IF;

    ALTER TABLE `temp_stat_views_today`
      RENAME TO `stat_page_views_today_mv`;

    DROP TABLE IF EXISTS `old_stat_views_today_mv`;

  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_tmp_cat_tag_proj` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_tmp_cat_tag_proj`()
BEGIN



	TRUNCATE table tmp_cat_tag_proj;	

	

	INSERT INTO tmp_cat_tag_proj

	

		select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = t.project_category_id

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100

	; 

	

	INSERT IGNORE INTO tmp_cat_tag_proj

		#ebene 1

		select p.project_id, c.category_id as project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = (SPLIT_STRING(t.ancestor_id_path, ',', 1))

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100;

		

	INSERT IGNORE INTO tmp_cat_tag_proj

		#ebene 2

		select p.project_id, c.category_id as project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = (SPLIT_STRING(t.ancestor_id_path, ',', 2))

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100;

		

	INSERT IGNORE INTO tmp_cat_tag_proj

		#ebene 3

		select p.project_id, c.category_id as project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = (SPLIT_STRING(t.ancestor_id_path, ',', 3))

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100;



	INSERT IGNORE INTO tmp_cat_tag_proj

		#ebene 4

		select p.project_id, c.category_id as project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = (SPLIT_STRING(t.ancestor_id_path, ',', 4))

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100;

		

	INSERT IGNORE INTO tmp_cat_tag_proj

		#ebene 5

		select p.project_id, c.category_id as project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = (SPLIT_STRING(t.ancestor_id_path, ',', 5))

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100;		



	INSERT IGNORE INTO tmp_cat_tag_proj

		#ebene 6

		select p.project_id, c.category_id as project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = (SPLIT_STRING(t.ancestor_id_path, ',', 6))

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100;		

		

	INSERT IGNORE INTO tmp_cat_tag_proj

		#ebene 7

		select p.project_id, c.category_id as project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = (SPLIT_STRING(t.ancestor_id_path, ',', 7))

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100;		

		

	INSERT IGNORE INTO tmp_cat_tag_proj

		#ebene 8

		select p.project_id, c.category_id as project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = (SPLIT_STRING(t.ancestor_id_path, ',', 8))

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100;						



	INSERT IGNORE INTO tmp_cat_tag_proj

		#ebene 9

		select p.project_id, c.category_id as project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = (SPLIT_STRING(t.ancestor_id_path, ',', 9))

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100;		

		

	INSERT IGNORE INTO tmp_cat_tag_proj

		#ebene 10

		select p.project_id, c.category_id as project_category_id, c.tag_id, t.ancestor_id_path from project p

		join stat_cat_tree t on t.project_category_id = p.project_category_id

		join category_tag c on c.category_id = (SPLIT_STRING(t.ancestor_id_path, ',', 10))

		join tag ta on ta.tag_id = c.tag_id

		WHERE p.`status` = 100;	

		

		

	DELETE FROM tag_object

	WHERE tag_group_id = 6;

	

	INSERT INTO tag_object

	SELECT DISTINCT null AS tag_item_id, p.tag_id, 1 AS tag_type_id, 6 AS tag_group_id,p.project_id AS tag_object_id,NOW() AS tag_created, null AS tag_changed 

	FROM tmp_cat_tag_proj p

	;	

		

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_tmp_cat_tag_proj_init` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `generate_tmp_cat_tag_proj_init`()
BEGIN

    TRUNCATE table tmp_project_system_tag;  

    truncate table stat_cat_tree_hierachie;
    insert into stat_cat_tree_hierachie 
    select 
    t.project_category_id,
    t.ancestor_id_path,
    SPLIT_STRING(t.ancestor_id_path, ',', 1) as catid1, -- root no category tags ignore
    SPLIT_STRING(t.ancestor_id_path, ',', 2) as catid2,
    SPLIT_STRING(t.ancestor_id_path, ',', 3) as catid3,
    SPLIT_STRING(t.ancestor_id_path, ',', 4) as catid4,
    SPLIT_STRING(t.ancestor_id_path, ',', 5) as catid5,     
    SPLIT_STRING(t.ancestor_id_path, ',', 6) as catid6,
    now() as created_at            
    from stat_cat_tree t;

    INSERT INTO tmp_project_system_tag
    select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p
    join stat_cat_tree_hierachie t on t.project_category_id = p.project_category_id
    join category_tag c on c.category_id = t.catid2    
    WHERE p.`status` = 100  
    ; 

    INSERT INTO tmp_project_system_tag
    select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p
    join stat_cat_tree_hierachie t on t.project_category_id = p.project_category_id
    join category_tag c on c.category_id = t.catid3    
    WHERE p.`status` = 100  
    ; 

    INSERT INTO tmp_project_system_tag
    select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p
    join stat_cat_tree_hierachie t on t.project_category_id = p.project_category_id
    join category_tag c on c.category_id = t.catid4    
    WHERE p.`status` = 100  
    ; 

    INSERT INTO tmp_project_system_tag
    select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p
    join stat_cat_tree_hierachie t on t.project_category_id = p.project_category_id
    join category_tag c on c.category_id = t.catid5    
    WHERE p.`status` = 100  
    ;

    INSERT INTO tmp_project_system_tag
    select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p
    join stat_cat_tree_hierachie t on t.project_category_id = p.project_category_id
    join category_tag c on c.category_id = t.catid6    
    WHERE p.`status` = 100  
    ;  
  
    
    DROP TABLE IF EXISTS tmp_tag_object_to_delete;
    CREATE TEMPORARY TABLE tmp_tag_object_to_delete    
    (PRIMARY KEY `primary` (tag_item_id))
      ENGINE MyISAM
      AS
        SELECT 
        o.tag_item_id
    FROM 
        tag_object o
        LEFT JOIN tmp_project_system_tag t on t.project_id = o.tag_object_id and t.tag_id = o.tag_id 
    WHERE 
        o.tag_group_id = 6 and o.is_deleted = 0 and t.project_id is null
    ;
    
    /*DELETE SYSTEM TAGS -- 12155 TO DELETE*/

    update tag_object  set is_deleted = 1 , tag_changed = now()
    where tag_item_id in
    (
      SELECT 
        o.tag_item_id
      FROM 
        tmp_tag_object_to_delete o
    );
   




    DROP TABLE IF EXISTS tmp_tag_object_to_insert;
    CREATE TEMPORARY TABLE tmp_tag_object_to_insert    
    /*(INDEX (project_id,project_category_id,tag_id))*/
      ENGINE MyISAM
      AS
        SELECT 
        t.*
      FROM 
        tmp_project_system_tag t
        LEFT JOIN tag_object o on t.project_id = o.tag_object_id and t.tag_id = o.tag_id and o.tag_group_id = 6
      WHERE 
        o.tag_item_id is null
    ;
	

    INSERT INTO tag_object
    SELECT null AS tag_item_id, p.tag_id, 1 AS tag_type_id, 6 AS tag_group_id,p.project_id AS tag_object_id,null as tag_parenet_object_id,NOW() AS tag_created, null AS tag_changed, 0 as is_deleted
    FROM (
      select DISTINCT * from tmp_tag_object_to_insert
    ) p;
	
     
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `merge_members` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `merge_members`(

	IN `from_member_id` INT,

	IN `to_member_id` INT

)
    COMMENT 'Merge of 2 members into 1'
BEGIN



	DECLARE exit handler for sqlexception

	  BEGIN

	    -- ERROR

	  ROLLBACK;

	END;



	START TRANSACTION;



	#Update table member

	UPDATE member m

	SET m.is_active = 0

	,m.is_deleted = 1

	,m.deleted_at = NOW()

	WHERE m.member_id = from_member_id;

	

	#Update table member_email

	UPDATE member_email me

	SET me.email_deleted = 1

	WHERE me.email_member_id = from_member_id;

	

	#Update table project

	INSERT INTO merge_member_log

	(

	    SELECT null,'project', project_id, member_id, to_member_id 

	    FROM project p WHERE p.member_id = from_member_id AND p.type_id = 1

	);

	

	UPDATE project p

	SET p.member_id = to_member_id

	WHERE p.member_id = from_member_id

	AND p.type_id = 1;

	

	#Update table comments

	INSERT INTO merge_member_log

	(

	    SELECT null, 'comments', comment_id, comment_member_id, to_member_id 

	    FROM comments c WHERE c.comment_member_id = from_member_id

	);

	

	UPDATE comments c

	SET c.comment_member_id = to_member_id

	WHERE c.comment_member_id = from_member_id;

	

	#Update table project_follower

	INSERT INTO merge_member_log

	(

	    SELECT null, 'project_follower', project_follower_id, member_id, to_member_id 

	    FROM project_follower f WHERE f.member_id = from_member_id

	);

	

	UPDATE project_follower f

	SET f.member_id = to_member_id

	WHERE f.member_id = from_member_id;

	

	#Update table project_rating

	INSERT INTO merge_member_log

	(

	    SELECT null, 'project_rating', rating_id, member_id, to_member_id 

	    FROM project_rating r WHERE r.member_id = from_member_id

	);

	

	UPDATE project_rating r

	SET r.member_id = to_member_id

	WHERE r.member_id = from_member_id;

	

	#Update table project_plings

	INSERT INTO merge_member_log

	(

	    SELECT null, 'project_plings', project_plings_id, member_id, to_member_id 

	    FROM project_plings r WHERE r.member_id = from_member_id

	);

	

	UPDATE project_plings r

	SET r.member_id = to_member_id

	WHERE r.member_id = from_member_id;

	

	

	#Update ppload

	

	IF (SELECT count(1) FROM ppload.ppload_collections pc WHERE pc.owner_id = from_member_id) > 0



    THEN



    	#Update ppload_collections

		INSERT INTO merge_member_log

		(

		    SELECT null, 'ppload_collections', pc.id, pc.owner_id, to_member_id 

		    FROM ppload.ppload_collections pc WHERE pc.owner_id = from_member_id

		);

		

		UPDATE ppload.ppload_collections pc

		SET pc.owner_id = to_member_id

		WHERE pc.owner_id = from_member_id;

		

		#Update ppload_files

		INSERT INTO merge_member_log

		(

		    SELECT null, 'ppload_files', pc.id, pc.owner_id, to_member_id 

		    FROM ppload.ppload_files pc WHERE pc.owner_id = from_member_id

		);

		

		UPDATE ppload.ppload_files pf

		SET pf.owner_id = to_member_id

		WHERE pf.owner_id = from_member_id;

	

		#Update ppload_files_downloaded?

		/*INSERT INTO merge_member_log

		(

		    SELECT null, 'ppload_files_downloaded', pc.id, pc.owner_id, to_member_id 

		    FROM ppload.ppload_files_downloaded pc WHERE pc.owner_id = from_member_id

		);*/

		

		UPDATE ppload.ppload_files_downloaded pfd

		SET pfd.owner_id = to_member_id

		WHERE pfd.owner_id = from_member_id;

	

		/**

		#Update ppload_profiles

		INSERT INTO merge_member_log

		(

		    SELECT null, 'ppload_profiles', pc.id, pc.owner_id, to_member_id 

		    FROM ppload_profiles pc WHERE pc.owner_id = from_member_id

		);

		

		UPDATE ppload.ppload_profiles pp

		SET pp.owner_id = to_member_id

		WHERE pp.owner_id = from_member_id;

		**/  	



    END IF;

	

	

	

	

	#Write a log entry

	INSERT INTO `activity_log` (`member_id`, `object_id`, `object_ref`, `object_title`, `object_text`, `activity_type_id`, `time`) VALUES ('22', from_member_id, 'member', 'call merge_members', CONCAT('merge member ', from_member_id,' into member ',to_member_id), '321', NOW());

	

	

	COMMIT;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `solr_query_deleted_pk` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `solr_query_deleted_pk`(IN lastIndexed VARCHAR(255))
BEGIN
SELECT project_id
FROM project
  JOIN member ON member.member_id = project.member_id
  JOIN project_category AS pc ON pc.project_category_id = project.project_category_id
WHERE 
	CONVERT_TZ(project.deleted_at,'+00:00','+04:00') > lastIndexed 
 OR CONVERT_TZ(member.deleted_at,'+00:00','+04:00') > lastIndexed 
 OR (CONVERT_TZ(project.changed_at,'+00:00','+04:00') > lastIndexed AND project.status < 100);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `solr_query_deleted_pk_new` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `solr_query_deleted_pk_new`(IN lastIndexed VARCHAR(255))
BEGIN
  SELECT project_id
  FROM project   
  WHERE 
   project.`type_id` = 1
   and(
     project.deleted_at > timestamp(DATE_SUB(lastIndexed, INTERVAL 1 DAY))     
     OR (project.changed_at > timestamp(DATE_SUB(lastIndexed, INTERVAL 1 DAY)) AND project.status < 100)
   );

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `solr_query_delta` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `solr_query_delta`(IN lastIndexed varchar(255))
BEGIN
    SELECT DISTINCT project_id
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN project_category AS pc ON pc.project_category_id = project.project_category_id
      LEFT JOIN tag_object AS tgo ON tgo.tag_object_id = project.project_id AND tgo.tag_type_id = 1
    WHERE (project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND pc.`is_active` = 1 AND project.changed_at > lastIndexed)
          OR (project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND pc.`is_active` = 1 AND (tgo.tag_created > lastIndexed OR tgo.tag_changed > lastIndexed))
    ;
  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `solr_query_delta_import` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `solr_query_delta_import`(IN projectID INT(11))
BEGIN
  SET sql_log_bin = 0;

  DROP TABLE IF EXISTS tmp_user_tags;
  CREATE TEMPORARY TABLE tmp_user_tags
  (PRIMARY KEY `primary` (tag_project_id))
    AS
      SELECT GROUP_CONCAT(tag.tag_name) AS tag_names, tgo.tag_object_id AS tag_project_id
      FROM tag_object AS tgo
        JOIN tag ON tag.tag_id = tgo.tag_id
      WHERE tag_type_id = 1 AND tag_group_id = 5
      GROUP BY tgo.tag_object_id
      ORDER BY tgo.tag_object_id;

  DROP TABLE IF EXISTS tmp_system_tags;
  CREATE TEMPORARY TABLE tmp_system_tags
  (PRIMARY KEY `primary` (tag_project_id))
    AS
      SELECT GROUP_CONCAT(tag.tag_name) AS tag_names, tgo.tag_object_id AS tag_project_id
      FROM tag_object AS tgo
        JOIN tag ON tag.tag_id = tgo.tag_id
      WHERE tag_type_id = 1 AND tag_group_id = 6
      GROUP BY tgo.tag_object_id
      ORDER BY tgo.tag_object_id;

  DROP TABLE IF EXISTS tmp_cat_tree;
  CREATE TEMPORARY TABLE tmp_cat_tree
  (PRIMARY KEY `primary` (project_category_id))
    AS
      SELECT
        pc.project_category_id,
        pc.title,
        pc.is_active,
        count(pc.lft)                                            AS depth,
        GROUP_CONCAT(pc2.project_category_id ORDER BY pc2.lft)   AS ancestor_id_path,
        GROUP_CONCAT(pc2.title ORDER BY pc2.lft SEPARATOR ' | ') AS ancestor_path
      FROM project_category AS pc, project_category AS pc2
      WHERE (pc.lft BETWEEN pc2.lft AND pc2.rgt)
      GROUP BY pc.lft
      ORDER BY pc.lft;

  DROP TABLE IF EXISTS tmp_cat_store;
  CREATE TEMPORARY TABLE tmp_cat_store
  (PRIMARY KEY `primary` (project_category_id))
    AS
      SELECT
        tct.project_category_id,
        tct.ancestor_id_path,
        tct.title,
        tct.is_active,
        group_concat(store_id) AS stores
      FROM tmp_cat_tree AS tct, config_store_category AS csc
      WHERE FIND_IN_SET(csc.project_category_id, tct.ancestor_id_path) > 0
      GROUP BY tct.project_category_id
      ORDER BY tct.project_category_id;

  DROP TABLE IF EXISTS solr_project_package_types;
  CREATE TEMPORARY TABLE solr_project_package_types
  (PRIMARY KEY `primary` (package_project_id))
    ENGINE MyISAM
    AS
      SELECT
        project_id as package_project_id,
        GROUP_CONCAT(DISTINCT project_package_type.package_type_id) AS package_type_id_list,
        GROUP_CONCAT(DISTINCT package_types.`name`) AS `package_name_list`
      FROM
        project_package_type
        JOIN
        package_types ON project_package_type.package_type_id = package_types.package_type_id
      WHERE
        package_types.is_active = 1
      GROUP BY project_id
  ;

  SELECT
    project_id,
    project.member_id           AS project_member_id,
    project.project_category_id AS project_category_id,
    project.title               AS project_title,
    description,
    image_small,
    member.username,
    member.firstname,
    member.lastname,
    tcs.title                   AS cat_title,
    `project`.`count_likes`     AS `count_likes`,
    `project`.`count_dislikes`  AS `count_dislikes`,
    laplace_score(project.count_likes, project.count_dislikes) AS `laplace_score`,
    DATE_FORMAT(project.created_at, '%Y-%m-%dT%TZ') AS created_at,
    DATE_FORMAT(project.changed_at, '%Y-%m-%dT%TZ') AS changed_at,
    tcs.stores,
    tcs.ancestor_id_path        AS `cat_id_ancestor_path`,
    sppt.package_type_id_list   AS `package_ids`,
    sppt.package_name_list      AS `package_names`,
    tu.tag_names                AS `tags`,
    tu.tag_names                AS `user_tags`,
    ts.tag_names                AS `system_tags`
  FROM project
    JOIN member ON member.member_id = project.member_id
    JOIN tmp_cat_store AS tcs ON project.project_category_id = tcs.project_category_id
    LEFT JOIN solr_project_package_types AS sppt ON sppt.package_project_id = project.project_id
    LEFT JOIN tmp_user_tags AS tu ON tu.tag_project_id = project.project_id
    LEFT JOIN tmp_system_tags AS ts ON ts.tag_project_id = project.project_id
    WHERE project.project_id = projectID;
  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `solr_query_delta_import_new` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `solr_query_delta_import_new`(IN projectID INT(11))
BEGIN

     DROP TABLE IF EXISTS tmp_cat_tree;
    CREATE TEMPORARY TABLE  tmp_cat_tree
    (PRIMARY KEY `primary` (project_category_id) )
     AS
      SELECT
        pc.project_category_id,
        pc.title,
        pc.is_active,
        count(pc.lft)                                            AS depth,
        GROUP_CONCAT(pc2.project_category_id ORDER BY pc2.lft)   AS ancestor_id_path,
        GROUP_CONCAT(pc2.title ORDER BY pc2.lft SEPARATOR ' | ') AS ancestor_path
      FROM project_category AS pc, project_category AS pc2
      WHERE (pc.lft BETWEEN pc2.lft AND pc2.rgt)
      GROUP BY pc.lft
      ORDER BY pc.lft;

    DROP TABLE IF EXISTS tmp_solr_cat_store;
    CREATE  TEMPORARY  TABLE  tmp_solr_cat_store 
    (PRIMARY KEY `primary` (project_category_id) )
    AS
      SELECT
        tct.project_category_id,
        tct.ancestor_id_path,
        tct.title,
        tct.is_active,
        group_concat(store_id) AS stores
      FROM tmp_cat_tree AS tct, config_store_category AS csc
      WHERE FIND_IN_SET(csc.project_category_id, tct.ancestor_id_path) > 0
      GROUP BY tct.project_category_id
      ORDER BY tct.project_category_id;

    SELECT   
      project_id,
      project.member_id           AS project_member_id,
      project.project_category_id AS project_category_id,
      project.title               AS project_title,
      description,
      image_small,
      member.username,
      member.firstname,
      member.lastname,
      tcs.title                   AS cat_title,
      `project`.`count_likes`     AS `count_likes`,
      `project`.`count_dislikes`  AS `count_dislikes`,
      laplace_score(project.count_likes, project.count_dislikes) AS `laplace_score`,
      DATE_FORMAT(project.created_at, '%Y-%m-%dT%TZ') AS created_at,
      DATE_FORMAT(project.changed_at, '%Y-%m-%dT%TZ') AS changed_at,
      tcs.stores,
      tcs.ancestor_id_path        AS `cat_id_ancestor_path`,  
        (
          
      SELECT GROUP_CONCAT(tag.tag_name) AS tag_names
      FROM tag_object , tag , ppload.ppload_files files   
      WHERE tag.tag_id = tag_object.tag_id and tag_object.tag_group_id = 8 and tag_object.tag_type_id = 3 and tag_object.is_deleted = 0 and tag_object.tag_parent_object_id = project.project_id
        and tag_object.tag_object_id = files.id and files.active = 1  
      ) as package_names , 
       (
        
      SELECT GROUP_CONCAT(tag.tag_name) AS tag_names
      FROM tag_object , tag  , ppload.ppload_files files      
      WHERE tag.tag_id = tag_object.tag_id and tag_object.tag_group_id = 9 and tag_object.tag_type_id = 3 and tag_object.is_deleted = 0 and tag_object.tag_parent_object_id = project.project_id
        and tag_object.tag_object_id = files.id and files.active = 1  
      ) as arch_names,    
       (        
              SELECT GROUP_CONCAT(tag.tag_name) AS tag_names
              FROM tag_object , tag  
              WHERE tag.tag_id = tag_object.tag_id and tag_object.tag_group_id = 7 and tag_object.tag_type_id = 1 and tag_object.is_deleted = 0 and tag_object.tag_object_id = project.project_id             
      ) as license_names,    
      (
      SELECT GROUP_CONCAT(tag.tag_name) AS tag_names
      FROM tag_object , tag      
      WHERE tag.tag_id = tag_object.tag_id and tag_object.tag_group_id in (5,6) and tag_object.tag_type_id = 1 and tag_object.is_deleted = 0 and tag_object.tag_object_id = project.project_id
      ) as tags          
    FROM project
      JOIN member ON member.member_id = project.member_id
      LEFT JOIN tmp_solr_cat_store AS tcs ON project.project_category_id = tcs.project_category_id          
   WHERE project_id = projectID and project.status=100 and member.`is_active` = 1;
   

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `solr_query_delta_new` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `solr_query_delta_new`(IN lastIndexed varchar(255))
BEGIN
  select distinct project_id
  from
  (
    SELECT project_id
    FROM project
    JOIN member ON member.member_id = project.member_id
    WHERE (project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND project.changed_at > timestamp(DATE_SUB(lastIndexed, INTERVAL 1 DAY)) )
    union 
    select distinct tag_object_id as project_id
    from tag_object
    where  tag_type_id = 1 and (tag_created >  timestamp(DATE_SUB(lastIndexed, INTERVAL 1 DAY))  or tag_changed > timestamp(DATE_SUB(lastIndexed, INTERVAL 1 DAY)) )
    union  select distinct tag_parent_object_id as project_id
    from tag_object
    where  tag_type_id in (8,9) and (tag_created >  timestamp(DATE_SUB(lastIndexed, INTERVAL 1 DAY))  or tag_changed > timestamp(DATE_SUB(lastIndexed, INTERVAL 1 DAY)) )    
    union
    select  project_id 
    from project 
    JOIN ppload.ppload_files files ON project.ppload_collection_id= files.collection_id AND files.updated_timestamp > timestamp(DATE_SUB(lastIndexed, INTERVAL 1 DAY)) 
    group by project_id     
  ) t ;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `solr_query_fullimport_prepare` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `solr_query_fullimport_prepare`()
BEGIN
   

    DROP TABLE IF EXISTS tmp_solr_cat_tree;
    CREATE TEMPORARY TABLE tmp_solr_cat_tree 
   (PRIMARY KEY `primary` (project_category_id) )
   AS
      SELECT
        pc.project_category_id,
        pc.title,
        pc.is_active,
        count(pc.lft)                                            AS depth,
        GROUP_CONCAT(pc2.project_category_id ORDER BY pc2.lft)   AS ancestor_id_path,
        GROUP_CONCAT(pc2.title ORDER BY pc2.lft SEPARATOR ' | ') AS ancestor_path
      FROM project_category AS pc, project_category AS pc2
      WHERE (pc.lft BETWEEN pc2.lft AND pc2.rgt)
      GROUP BY pc.lft
      ORDER BY pc.lft;

    DROP TABLE IF EXISTS tmp_solr_cat_store;
    CREATE TEMPORARY TABLE tmp_solr_cat_store 
   (PRIMARY KEY `primary` (project_category_id) )
   AS
      SELECT
        tct.project_category_id,
        tct.ancestor_id_path,
        tct.title,
        tct.is_active,
        group_concat(store_id) AS stores
      FROM tmp_solr_cat_tree AS tct, config_store_category AS csc
      WHERE FIND_IN_SET(csc.project_category_id, tct.ancestor_id_path) > 0
      GROUP BY tct.project_category_id
      ORDER BY tct.project_category_id;

      DROP TABLE IF EXISTS tmp_solr_project_tags;
      CREATE TEMPORARY TABLE tmp_solr_project_tags
      (PRIMARY KEY `primary` (tag_project_id) )
     AS
        SELECT GROUP_CONCAT(tag.tag_name) AS tag_names
                  , tgo.tag_object_id AS tag_project_id
        FROM tag_object AS tgo
          JOIN tag ON tag.tag_id = tgo.tag_id          
        WHERE tgo.tag_type_id = 1 and tgo.tag_group_id in (5,6) and tgo.is_deleted = 0
        GROUP BY tgo.tag_object_id;

      DROP TABLE IF EXISTS tmp_solr_project_license;
      CREATE TEMPORARY TABLE tmp_solr_project_license
      (PRIMARY KEY `primary` (license_project_id))
        ENGINE MyISAM
        AS
          SELECT 
      t.tag_object_id as license_project_id,
      GROUP_CONCAT(DISTINCT ta.tag_name) AS `license_name_list`
      FROM tag_object t 
      INNER JOIN tag ta on ta.tag_id = t.tag_id 
      WHERE t.tag_type_id = 1 and t.tag_group_id = 7  AND t.is_deleted = 0
      group by tag_object_id
      ;

    DROP TABLE IF EXISTS tmp_solr_project_package_types;
    CREATE TEMPORARY TABLE tmp_solr_project_package_types
    (PRIMARY KEY `primary` (package_project_id))
      ENGINE MyISAM
      AS
        SELECT 
    t.tag_parent_object_id as package_project_id,
    GROUP_CONCAT(DISTINCT ta.tag_id) AS package_type_id_list,
    GROUP_CONCAT(DISTINCT ta.tag_name) AS `package_name_list`
    FROM tag_object t 
    INNER JOIN tag ta on ta.tag_id = t.tag_id 
    JOIN ppload.ppload_files files ON files.id = t.tag_object_id  AND files.active = 1
    WHERE t.tag_type_id = 3 and t.tag_group_id = 8  AND t.is_deleted = 0
    group by tag_parent_object_id
    ;
    
     DROP TABLE IF EXISTS tmp_solr_project_arch_types;
    CREATE TEMPORARY TABLE tmp_solr_project_arch_types
    (PRIMARY KEY `primary` (arch_project_id))
      ENGINE MyISAM
      AS
        SELECT 
    t.tag_parent_object_id as arch_project_id,
    GROUP_CONCAT(DISTINCT ta.tag_id) AS arch_type_id_list,
    GROUP_CONCAT(DISTINCT ta.tag_name) AS `arch_name_list`
    FROM tag_object t 
    INNER JOIN tag ta on ta.tag_id = t.tag_id 
    JOIN ppload.ppload_files files ON files.id = t.tag_object_id  AND files.active = 1
    WHERE t.tag_type_id = 3 and t.tag_group_id = 9  AND t.is_deleted = 0
    group by tag_parent_object_id
    ;
  
    DROP TABLE IF EXISTS tmp_solr_query_fullimport; 
    create table tmp_solr_query_fullimport as
    
    SELECT   
      project_id,
      project.member_id           AS project_member_id,
      project.project_category_id AS project_category_id,
      project.title               AS project_title,
      description,
      image_small,
      member.username,
      member.firstname,
      member.lastname,
      tcs.title                   AS cat_title,
      `project`.`count_likes`     AS `count_likes`,
      `project`.`count_dislikes`  AS `count_dislikes`,
      laplace_score(project.count_likes, project.count_dislikes) AS `laplace_score`,
      DATE_FORMAT(project.created_at, '%Y-%m-%dT%TZ') AS created_at,
      DATE_FORMAT(project.changed_at, '%Y-%m-%dT%TZ') AS changed_at,
      tcs.stores,
      tcs.ancestor_id_path        AS `cat_id_ancestor_path`,      
      sppt.package_name_list      AS `package_names`,
      appt.arch_name_list         AS `arch_names`,
      c.license_name_list         AS `license_names`,
      t.tag_names                 AS `tags`
    FROM project
      JOIN member ON member.member_id = project.member_id
      LEFT JOIN tmp_solr_cat_store AS tcs ON project.project_category_id = tcs.project_category_id
      LEFT JOIN tmp_solr_project_package_types AS sppt ON sppt.package_project_id = project.project_id
      LEFT JOIN tmp_solr_project_arch_types AS appt ON appt.arch_project_id = project.project_id
      LEFT JOIN tmp_solr_project_license AS c ON c.license_project_id = project.project_id
      LEFT JOIN tmp_solr_project_tags AS t ON t.tag_project_id = project.project_id
            
    WHERE project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND tcs.`is_active` = 1;
    

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `solr_query_import` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `solr_query_import`()
BEGIN
	SET sql_log_bin = 0;
    
    DROP TABLE IF EXISTS tmp_user_tags;
    CREATE TEMPORARY TABLE tmp_user_tags
    (PRIMARY KEY `primary` (tag_project_id))
    AS
      SELECT GROUP_CONCAT(tag.tag_name) AS tag_names, tgo.tag_object_id AS tag_project_id
      FROM tag_object AS tgo
        JOIN tag ON tag.tag_id = tgo.tag_id
      WHERE tag_type_id = 1 AND tag_group_id = 5
      GROUP BY tgo.tag_object_id
      ORDER BY tgo.tag_object_id;

    DROP TABLE IF EXISTS tmp_system_tags;
    CREATE TEMPORARY TABLE tmp_system_tags 
    (PRIMARY KEY `primary` (tag_project_id))
    AS
      SELECT GROUP_CONCAT(tag.tag_name) AS tag_names, tgo.tag_object_id AS tag_project_id
      FROM tag_object AS tgo
        JOIN tag ON tag.tag_id = tgo.tag_id
      WHERE tag_type_id = 1 AND tag_group_id = 6
      GROUP BY tgo.tag_object_id
      ORDER BY tgo.tag_object_id;

    DROP TABLE IF EXISTS tmp_cat_tree;
    CREATE TEMPORARY TABLE tmp_cat_tree 
    (PRIMARY KEY `primary` (project_category_id))
    AS
      SELECT
        pc.project_category_id,
        pc.title,
        pc.is_active,
        count(pc.lft)                                            AS depth,
        GROUP_CONCAT(pc2.project_category_id ORDER BY pc2.lft)   AS ancestor_id_path,
        GROUP_CONCAT(pc2.title ORDER BY pc2.lft SEPARATOR ' | ') AS ancestor_path
      FROM project_category AS pc, project_category AS pc2
      WHERE (pc.lft BETWEEN pc2.lft AND pc2.rgt)
      GROUP BY pc.lft
      ORDER BY pc.lft;

    DROP TABLE IF EXISTS tmp_cat_store;
    CREATE TEMPORARY TABLE tmp_cat_store 
    (PRIMARY KEY `primary` (project_category_id))
    AS
      SELECT
        tct.project_category_id,
        tct.ancestor_id_path,
        tct.title,
        tct.is_active,
        group_concat(store_id) AS stores
      FROM tmp_cat_tree AS tct, config_store_category AS csc
      WHERE FIND_IN_SET(csc.project_category_id, tct.ancestor_id_path) > 0
      GROUP BY tct.project_category_id
      ORDER BY tct.project_category_id;

    DROP TABLE IF EXISTS solr_project_package_types;
    CREATE TEMPORARY TABLE solr_project_package_types
    (PRIMARY KEY `primary` (package_project_id))
      ENGINE MyISAM
      AS
        SELECT
          project_id as package_project_id,
          GROUP_CONCAT(DISTINCT project_package_type.package_type_id) AS package_type_id_list,
          GROUP_CONCAT(DISTINCT package_types.`name`) AS `package_name_list`
        FROM
          project_package_type
          JOIN
          package_types ON project_package_type.package_type_id = package_types.package_type_id
        WHERE
          package_types.is_active = 1
        GROUP BY project_id
    ;

    SELECT
      project_id,
      project.member_id           AS project_member_id,
      project.project_category_id AS project_category_id,
      project.title               AS project_title,
      description,
      image_small,
      member.username,
      member.firstname,
      member.lastname,
      tcs.title                   AS cat_title,
      `project`.`count_likes`     AS `count_likes`,
      `project`.`count_dislikes`  AS `count_dislikes`,
      laplace_score(project.count_likes, project.count_dislikes) AS `laplace_score`,
      DATE_FORMAT(project.created_at, '%Y-%m-%dT%TZ') AS created_at,
      DATE_FORMAT(project.changed_at, '%Y-%m-%dT%TZ') AS changed_at,
      tcs.stores,
      tcs.ancestor_id_path        AS `cat_id_ancestor_path`,
      sppt.package_type_id_list   AS `package_ids`,
      sppt.package_name_list      AS `package_names`,
      tu.tag_names                AS `tags`,
      tu.tag_names                AS `user_tags`,
      ts.tag_names                AS `system_tags`
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN tmp_cat_store AS tcs ON project.project_category_id = tcs.project_category_id
      LEFT JOIN solr_project_package_types AS sppt ON sppt.package_project_id = project.project_id
      LEFT JOIN tmp_user_tags AS tu ON tu.tag_project_id = project.project_id
      LEFT JOIN tmp_system_tags AS ts ON ts.tag_project_id = project.project_id
    WHERE project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND tcs.`is_active` = 1;
  END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `solr_query_import_new` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `solr_query_import_new`()
BEGIN
    select 
        project_id,
        project_member_id,
        project_category_id,
        project_title,
        description,
        image_small,
        username,
        firstname,
        lastname,
        cat_title,
        count_likes,
        count_dislikes,
        laplace_score,
        created_at,
        changed_at,
        stores,
        cat_id_ancestor_path,  
        package_names,
        arch_names,
        license_names,
        tags
    from tmp_solr_query_fullimport;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_member_bio` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `update_member_bio`()
BEGIN
  SET @cur_date = DATE_FORMAT(CURDATE(), '%Y%m%d');
  SET @str_sql = CONCAT('CREATE TABLE mem_bio_', @cur_date, ' AS SELECT member_id, biography, replace(biography,"\\\\","") AS clean_bio FROM member where biography regexp "[[.\\\\.]]";');
#  SELECT @str_sql;
  PREPARE stmt FROM @str_sql;
  EXECUTE stmt;
  
  SET @str_update = CONCAT("update member join mem_bio_",@cur_date, " as prj on prj.member_id = member.member_id set member.biography = prj.clean_bio;");
  PREPARE stmt FROM @str_update;
  EXECUTE stmt;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_project_description` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER  PROCEDURE `update_project_description`()
BEGIN
  SET @cur_date = DATE_FORMAT(CURDATE(), '%Y%m%d');
  SET @str_sql = CONCAT('CREATE TABLE prj_desc_', @cur_date, ' AS SELECT project_id, description, replace(description,"\\\\","") AS clean_description FROM project where description regexp "[[.\\\\.]]";');
#  SELECT @str_sql;
  PREPARE stmt FROM @str_sql;
  EXECUTE stmt;
  
  SET @str_update = CONCAT("update project join prj_desc_",@cur_date, " as prj on prj.project_id = project.project_id set project.description = prj.clean_description;");
  PREPARE stmt FROM @str_update;
  EXECUTE stmt;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

/*!50003 DROP PROCEDURE IF EXISTS `generate_stat_files_downloaded` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE PROCEDURE `generate_stat_files_downloaded`()
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

DROP TABLE IF EXISTS ppload.tmp_stat_ppload_files_downloaded;

CREATE TABLE ppload.tmp_stat_ppload_files_downloaded
(INDEX `idx_coll` (`collection_id`),INDEX `idx_file` (`file_id`))
   ENGINE MyISAM
   AS
    SELECT f.owner_id, f.collection_id, f.file_id, COUNT(1) AS count_dl FROM ppload.ppload_files_downloaded f
    WHERE f.downloaded_timestamp < DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00')
    GROUP BY f.collection_id, f.file_id
;
RENAME TABLE ppload.stat_ppload_files_downloaded TO ppload.old_stat_ppload_files_downloaded, ppload.tmp_stat_ppload_files_downloaded TO ppload.stat_ppload_files_downloaded;
DROP TABLE IF EXISTS ppload.old_stat_ppload_files_downloaded;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `member_login`
--

/*!50001 DROP VIEW IF EXISTS `member_login`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=TEMPTABLE */
/*!50013 DEFINER=CURRENT_USER  SQL SECURITY DEFINER */
/*!50001 VIEW `member_login` AS select substr(sha(`member`.`member_id`),1,20) AS `_id`,`member`.`username` AS `username`,`member`.`username` AS `usernameNormalized`,`member`.`mail` AS `email`,`member`.`password` AS `password`,if((locate('hive',`member`.`profile_image_url`) > 0),concat('https://cn.pling.com/img/',`member`.`profile_image_url`),`member`.`profile_image_url`) AS `avatarUrl`,`member`.`biography` AS `biography`,if((`member`.`roleId` = 100),'true','false') AS `admin`,coalesce(unix_timestamp(`member`.`changed_at`),0) AS `lastUpdateTime`,coalesce(unix_timestamp(`member`.`created_at`),0) AS `creationTime`,'true' AS `emailVerified`,'false' AS `disabled`,`member`.`member_id` AS `ocs_user_id`,if((`member`.`source_id` = 1),'true','false') AS `is_hive` from `member` where ((`member`.`is_active` = 1) and (`member`.`is_deleted` = 0) and (`member`.`mail_checked` = 1) and (`member`.`login_method` = 'local')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `stg_member_payout_v`
--

/*!50001 DROP VIEW IF EXISTS `stg_member_payout_v`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER  SQL SECURITY DEFINER */
/*!50001 VIEW `stg_member_payout_v` AS select `member_payout`.`id` AS `id`,`member_payout`.`yearmonth` AS `yearmonth`,`member_payout`.`member_id` AS `member_id`,`member_payout`.`mail` AS `mail`,`member_payout`.`paypal_mail` AS `paypal_mail`,`member_payout`.`num_downloads` AS `num_downloads`,`member_payout`.`num_points` AS `num_points`,`member_payout`.`amount` AS `amount`,`member_payout`.`status` AS `status`,`member_payout`.`created_at` AS `created_at`,(case when (`member_payout`.`updated_at` = '0000-00-00 00:00:00') then NULL else `member_payout`.`updated_at` end) AS `updated_at`,`member_payout`.`timestamp_masspay_start` AS `timestamp_masspay_start`,`member_payout`.`timestamp_masspay_last_ipn` AS `timestamp_masspay_last_ipn`,`member_payout`.`last_paypal_ipn` AS `last_paypal_ipn`,`member_payout`.`last_paypal_status` AS `last_paypal_status`,`member_payout`.`payment_reference_key` AS `payment_reference_key`,`member_payout`.`payment_transaction_id` AS `payment_transaction_id`,`member_payout`.`payment_raw_message` AS `payment_raw_message`,`member_payout`.`payment_raw_error` AS `payment_raw_error`,`member_payout`.`payment_status` AS `payment_status` from `member_payout` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `stg_project_v`
--

/*!50001 DROP VIEW IF EXISTS `stg_project_v`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER  SQL SECURITY DEFINER */
/*!50001 VIEW `stg_project_v` AS select `project_fed`.`project_id` AS `project_id`,`project_fed`.`member_id` AS `member_id`,`project_fed`.`content_type` AS `content_type`,`project_fed`.`project_category_id` AS `project_category_id`,`project_fed`.`hive_category_id` AS `hive_category_id`,`project_fed`.`is_active` AS `is_active`,`project_fed`.`is_deleted` AS `is_deleted`,`project_fed`.`status` AS `status`,`project_fed`.`uuid` AS `uuid`,`project_fed`.`pid` AS `pid`,`project_fed`.`type_id` AS `type_id`,`project_fed`.`title` AS `title`,`project_fed`.`description` AS `description`,`project_fed`.`version` AS `version`,`project_fed`.`project_license_id` AS `project_license_id`,`project_fed`.`image_big` AS `image_big`,`project_fed`.`image_small` AS `image_small`,(case when (`project_fed`.`start_date` = '0000-00-00 00:00:00') then NULL else `project_fed`.`start_date` end) AS `start_date`,`project_fed`.`content_url` AS `content_url`,(case when (`project_fed`.`created_at` = '0000-00-00 00:00:00') then NULL else `project_fed`.`created_at` end) AS `created_at`,(case when (`project_fed`.`changed_at` = '0000-00-00 00:00:00') then NULL else `project_fed`.`changed_at` end) AS `changed_at`,(case when (`project_fed`.`deleted_at` = '0000-00-00 00:00:00') then NULL else `project_fed`.`deleted_at` end) AS `deleted_at`,`project_fed`.`creator_id` AS `creator_id`,`project_fed`.`facebook_code` AS `facebook_code`,`project_fed`.`twitter_code` AS `twitter_code`,`project_fed`.`google_code` AS `google_code`,`project_fed`.`source_url` AS `source_url`,`project_fed`.`link_1` AS `link_1`,`project_fed`.`embed_code` AS `embed_code`,`project_fed`.`ppload_collection_id` AS `ppload_collection_id`,`project_fed`.`validated` AS `validated`,`project_fed`.`validated_at` AS `validated_at`,`project_fed`.`featured` AS `featured`,`project_fed`.`approved` AS `approved`,`project_fed`.`ghns_excluded` AS `ghns_excluded`,`project_fed`.`spam_checked` AS `spam_checked`,`project_fed`.`pling_excluded` AS `pling_excluded`,`project_fed`.`amount` AS `amount`,`project_fed`.`amount_period` AS `amount_period`,`project_fed`.`claimable` AS `claimable`,`project_fed`.`claimed_by_member` AS `claimed_by_member`,`project_fed`.`count_likes` AS `count_likes`,`project_fed`.`count_dislikes` AS `count_dislikes`,`project_fed`.`count_comments` AS `count_comments`,`project_fed`.`count_downloads_hive` AS `count_downloads_hive`,`project_fed`.`source_id` AS `source_id`,`project_fed`.`source_pk` AS `source_pk`,`project_fed`.`source_type` AS `source_type` from `project` `project_fed` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_ppload_files_downloaded`
--

/*!50001 DROP VIEW IF EXISTS `v_ppload_files_downloaded`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER  SQL SECURITY DEFINER */
/*!50001 VIEW `v_ppload_files_downloaded` AS select `ppload`.`ppload_files_downloaded`.`id` AS `id`,`ppload`.`ppload_files_downloaded`.`client_id` AS `client_id`,`ppload`.`ppload_files_downloaded`.`owner_id` AS `owner_id`,`ppload`.`ppload_files_downloaded`.`collection_id` AS `collection_id`,`ppload`.`ppload_files_downloaded`.`file_id` AS `file_id`,`ppload`.`ppload_files_downloaded`.`user_id` AS `user_id`,`ppload`.`ppload_files_downloaded`.`downloaded_timestamp` AS `downloaded_timestamp`,`ppload`.`ppload_files_downloaded`.`downloaded_ip` AS `downloaded_ip` from `ppload`.`ppload_files_downloaded` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_reported_projects`
--

/*!50001 DROP VIEW IF EXISTS `view_reported_projects`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER  SQL SECURITY DEFINER */
/*!50001 VIEW `view_reported_projects` AS select `reports_project`.`project_id` AS `project_id`,count(`reports_project`.`project_id`) AS `amount_reports`,max(`reports_project`.`created_at`) AS `latest_report` from `reports_project` where (`reports_project`.`is_deleted` = 0) group by `reports_project`.`project_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-04-26 15:38:03
