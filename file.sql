-- MySQL dump 10.16  Distrib 10.1.32-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: ocs_webserver
-- ------------------------------------------------------
-- Server version	10.1.32-MariaDB

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
  `activity_log_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL COMMENT 'Log action of this member',
  `project_id` int(11) DEFAULT NULL,
  `object_id` int(11) NOT NULL COMMENT 'Key to the action (add comment, pling, ...)',
  `object_ref` varchar(45) NOT NULL COMMENT 'Reference to the object table (plings, project, project_comment,...)',
  `object_title` varchar(90) DEFAULT NULL COMMENT 'Title to show',
  `object_text` varchar(150) DEFAULT NULL COMMENT 'Short text of this object (first 150 characters)',
  `object_img` varchar(255) DEFAULT NULL,
  `activity_type_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Which ENGINE of activity: create, update,delete.',
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`activity_log_id`),
  KEY `member_id` (`member_id`),
  KEY `project_id` (`project_id`),
  KEY `object_id` (`object_id`),
  KEY `activity_log_id` (`activity_log_id`,`member_id`,`project_id`,`object_id`),
  KEY `idx_time` (`member_id`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Log all actions of a user. Wen can then generate a news feed ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (0,25,NULL,25,'member_email',NULL,'user saved new primary mail address: e16fd708@opayq.com',NULL,402,'2018-05-25 18:03:24');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `activity_log_types`
--

LOCK TABLES `activity_log_types` WRITE;
/*!40000 ALTER TABLE `activity_log_types` DISABLE KEYS */;
INSERT INTO `activity_log_types` VALUES (0,'ProjectCreated'),(1,'ProjectUpdated'),(2,'ProjectDeleted'),(3,'ProjectStopped'),(4,'ProjectRestarted'),(7,'ProjectEdited'),(8,'ProjectPublished'),(9,'ProjectUnpublished'),(10,'ProjectItemCreated'),(11,'ProjectItemUpdated'),(12,'ProjectItemDeleted'),(13,'ProjectItemStopped'),(14,'ProjectItemRestarted'),(17,'ProjectItemEdited'),(18,'ProjectItemPublished'),(19,'ProjectItemUnpublished'),(20,'ProjectPlinged'),(21,'ProjectDisplinged'),(30,'ProjectItemPlinged'),(31,'ProjectItemDisplinged'),(40,'ProjectCommentCreated'),(41,'ProjectCommentUpdated'),(42,'ProjectCommentDeleted'),(43,'ProjectCommentReply'),(50,'ProjectFollowed'),(51,'ProjectUnfollowed'),(52,'ProjectShared'),(60,'ProjectRatedHigher'),(61,'ProjectRatedLower'),(70,'ProjectLicenseChanged'),(100,'MemberJoined'),(101,'MemberUpdated'),(102,'MemberDeleted'),(107,'MemberEdited'),(150,'MemberFollowed'),(151,'MemberUnfollowed'),(152,'MemberShared'),(200,'ProjectFilesCreated'),(210,'ProjectFilesUpdated'),(220,'ProjectFilesDeleted'),(302,'BackendLogin'),(304,'BackendLogout'),(310,'BackendProjectDelete'),(312,'BackendProjectFeature'),(314,'BackendProjectGHNSExcluded'),(316,'BackendProjectCatChanged'),(318,'BackendProjectPlingExcluded'),(319,'BackendUserPlingExcluded'),(320,'BackendUserDeleted'),(401,'MemberEmailConfirmed'),(402,'MemberEmailChanged'),(410,'MemberPaypalChanged'),(901,'MemberSetLastTimeActiveHive'),(902,'MemberSetLastTimeActivePling');
/*!40000 ALTER TABLE `activity_log_types` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `comment_types`
--

LOCK TABLES `comment_types` WRITE;
/*!40000 ALTER TABLE `comment_types` DISABLE KEYS */;
INSERT INTO `comment_types` VALUES (0,'project'),(99,'wrongly_imported_from_hive');
/*!40000 ALTER TABLE `comment_types` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `comment_created` BEFORE INSERT ON `comments` FOR EACH ROW

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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `comment_update` BEFORE UPDATE ON `comments` FOR EACH ROW

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
  `created_at` datetime DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_store`
--

LOCK TABLES `config_store` WRITE;
/*!40000 ALTER TABLE `config_store` DISABLE KEYS */;
INSERT INTO `config_store` VALUES (20,'share.krita.org','Krita-Addons','krita',NULL,20511,0,0,NULL,36,NULL,0,'2016-05-13 13:45:30',NULL,NULL),(22,'www.opendesktop.org','','opendesktop',NULL,1,1,0,'UA-78422931-1',1,NULL,0,'2016-05-23 05:57:08',NULL,NULL),(33,'store.kde.org','KDE Store','kde-store',NULL,30701,0,0,'UA-78422931-14',38,NULL,0,'2016-06-05 17:00:23',NULL,NULL),(34,'www.kde-look.org','kde-look.org','kde',NULL,1,0,0,'UA-78422931-15',24,NULL,0,'2016-06-20 05:50:35',NULL,NULL);
/*!40000 ALTER TABLE `config_store` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `config_store_BEFORE_INSERT` BEFORE INSERT ON `config_store` FOR EACH ROW BEGIN

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
) ENGINE=InnoDB AUTO_INCREMENT=664 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_store_category`
--

LOCK TABLES `config_store_category` WRITE;
/*!40000 ALTER TABLE `config_store_category` DISABLE KEYS */;
INSERT INTO `config_store_category` VALUES (119,20,175,30,'2016-04-08 10:38:07',NULL,NULL),(120,20,163,100,'2016-04-08 10:38:45',NULL,NULL),(122,20,161,10,'2016-05-19 05:55:40',NULL,NULL),(244,20,165,110,'2016-06-30 14:52:27',NULL,NULL),(245,20,162,20,'2016-06-30 14:54:01',NULL,NULL),(346,33,101,100,'2016-06-21 08:54:47',NULL,NULL),(347,33,114,56,'2016-06-21 08:55:16',NULL,NULL),(348,33,118,60,'2016-06-21 08:55:34',NULL,NULL),(349,33,121,40,'2016-06-21 08:56:03',NULL,NULL),(350,33,104,50,'2016-06-21 08:56:36',NULL,NULL),(351,33,119,70,'2016-06-21 08:56:57',NULL,NULL),(352,33,123,80,'2016-06-21 08:57:22',NULL,NULL),(353,33,418,10,'2016-06-21 08:57:56',NULL,NULL),(354,33,112,88,'2016-06-21 08:58:38',NULL,NULL),(355,33,107,87,'2016-06-21 08:58:57',NULL,NULL),(356,33,113,86,'2016-06-21 08:59:06',NULL,NULL),(357,33,349,200,'2016-06-22 02:38:48',NULL,NULL),(360,33,422,400,'2016-06-22 02:40:12',NULL,NULL),(364,33,421,410,'2016-06-22 02:48:36',NULL,NULL),(365,33,111,95,'2016-06-22 02:58:49',NULL,NULL),(366,33,299,900,'2016-06-28 10:01:21',NULL,NULL),(368,33,266,65,'2016-07-03 17:13:05',NULL,NULL),(369,33,132,85,'2016-07-10 11:28:53',NULL,NULL),(370,33,108,97,'2016-07-12 05:00:41',NULL,NULL),(391,20,164,120,'2016-09-21 11:36:30',NULL,NULL),(392,20,39,15,'2016-09-27 13:24:00',NULL,NULL),(401,33,355,230,'2016-12-05 05:34:07',NULL,NULL),(431,33,117,57,'2016-12-24 04:47:51',NULL,NULL),(436,20,103,35,'2017-01-14 12:51:06',NULL,NULL),(440,33,368,340,'2017-01-17 03:53:49',NULL,NULL),(484,33,228,800,'2017-02-25 05:54:15',NULL,NULL),(490,33,198,305,'2017-03-17 16:54:45',NULL,NULL),(512,22,148,NULL,'2017-04-07 15:38:07',NULL,NULL),(522,22,295,NULL,'2017-04-07 15:42:02',NULL,NULL),(524,33,380,220,'2017-04-08 07:57:40',NULL,NULL),(540,22,152,NULL,'2017-04-17 10:05:33',NULL,NULL),(559,22,403,NULL,'2017-05-19 11:14:03',NULL,NULL),(560,22,404,NULL,'2017-05-20 14:58:07',NULL,NULL),(619,22,233,NULL,'2017-09-01 13:48:21',NULL,NULL),(621,33,415,350,'2017-10-02 06:28:46',NULL,NULL),(628,33,417,210,'2017-12-23 15:58:44',NULL,NULL),(641,22,445,NULL,'2018-01-27 07:00:01',NULL,NULL),(657,33,419,205,'2018-02-09 09:22:40',NULL,NULL),(658,33,155,215,'2018-02-09 09:22:59',NULL,NULL),(663,22,466,NULL,'2018-03-06 10:01:21',NULL,NULL);
/*!40000 ALTER TABLE `config_store_category` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `config_store_category_BEFORE_INSERT` BEFORE INSERT ON `config_store_category` FOR EACH ROW BEGIN

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
-- Dumping data for table `mail_template`
--

LOCK TABLES `mail_template` WRITE;
/*!40000 ALTER TABLE `mail_template` DISABLE KEYS */;
INSERT INTO `mail_template` VALUES (5,'tpl_verify_user','%servername%: Please verify your email address','<h2>Hey %username%,</h2>\r\n<p><br />Thank you for signing up to %servername%.</p>\r\n<p><br />Please click the button below to verify this email address:</p>\r\n<div><!-- [if mso]>\r\n    <v:roundrect xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:w=\"urn:schemas-microsoft-com:office:word\"\r\n                 href=\"%verificationurl%\" style=\"height:40px;v-text-anchor:middle;width:300px;\" arcsize=\"10%\"\r\n                 stroke=\"f\" fillcolor=\"#34495C\">\r\n        <w:anchorlock/>\r\n        <center style=\"color:#ffffff;font-family:sans-serif;font-size:16px;font-weight:bold;\">\r\n            Verify your e-mail address\r\n        </center>\r\n    </v:roundrect>\r\n    <![endif]--> <!-- [if !mso]> <!-->\r\n<table cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"-webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; color: #ffffff; display: block;\" align=\"center\" bgcolor=\"#34495C\" width=\"300\" height=\"40\"><a style=\"color: #ffffff; font-size: 16px; font-weight: bold; font-family: sans-serif; text-decoration: none; line-height: 40px; width: 100%; display: inline-block;\" href=\"%verificationurl%\"> Verify your e-mail address </a></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<!-- <![endif]--></div>\r\n<p><br />If the button doesn&rsquo;t work, you can copy and paste the following link to your browser:<br /><br />%verificationlink%&nbsp;</p>\r\n<p><br />If you have any problems, feel free to contact us at any time!</p>\r\n<p><br /><br />Kind regards,<br />Your openDesktop Team <br /><a href=\"mailto:contact@opendesktop.org\" target=\"_blank\">contact@opendesktop.org</a><br /><br /></p>','2011-11-07 10:28:43','2015-12-09 16:27:02',NULL),(7,'tpl_social_mail','<%sender%> sent you a recommendation','<p>&lt;%sender%&gt; has suggested that you could be interested in this member</p>\r\n<h2>%username%.</h2>\r\n<p>%permalinktext%</p>\r\n<p><br />If the link doesn&rsquo;t work, you can copy and paste the following link to your browser:</p>\r\n<h4>%permalink%</h4>\r\n<p><br />Kind regards,<br />\r\n    Team Pling</p>','2011-11-07 10:36:48','2013-11-08 11:51:44',NULL),(8,'tpl_user_message','opendesktop.org - Du hast eine Nachricht erhalten','Hallo %username%,<br/><br/>%sender% hat dir eine Nachricht geschickt.<br/><br/><div style=\'width: 500px; background-color: #F2F2F2; border: 1px solid #C1C1C1; padding: 10px;\'>%message_text%</div>','2011-11-07 10:40:06','2011-11-28 16:18:48',NULL),(9,'tpl_newuser_notification','opendesktop.org - Neue Memberanmeldung','Jemand hat sich angemeldet: <strong>%username%</strong> angemeldet.<br/><br/><br/>Grüße das pling-System :)','2011-11-28 15:50:59',NULL,NULL),(10,'tpl_user_newpass','opendesktop.org - your new password','<p>Hello %username%,<br /><br />We created this new password for your opendesktop.org account: <b>%newpass%</b><br /><br /><p><br />If you have any problems, feel free to contact us at any time!</p>\r\n<p><br /><br />Kind regards,<br />Your openDesktop Team <br /><a href=\"mailto:contact@opendesktop.org\" target=\"_blank\">contact@opendesktop.org</a><br /><br /></p>','2011-11-28 15:55:38','2015-12-09 16:26:10',NULL),(11,'tpl_newproject_notification','opendesktop.org - Neue Projektanmeldung','Ein neues Projekt wurde von <strong>%username%</strong> angemeldet.<br/>Mehr dazu im Backend hier: http://opendesktop.org/backend/project/apply<br/>Grüße das opendesktop.org-System :)','2011-11-28 16:41:00',NULL,NULL),(12,'tpl_verify_button_user','%servername%: Please verify your email address','<h2>Hey %username%,</h2>\r\n<p><br />thank you for signing up to opendesktop.org</p>\r\n<p>We have generated a new password for you. We recommend you to change this password as soon as possible in your settings.<br /><br />Your password: %password%</p>\r\n<p><br />Before you&nbsp;can use your button and&nbsp;receive loads of plings or love and pling other products, please klick the link below&nbsp;to verify your email address.</p>\r\n<p><br />If the link doesn&rsquo;t work, you can copy and paste the following link to your browser:<br /><br />%verificationlinktext%&nbsp;</p>\r\n<p><br />In case the problem still occurs, feel free to contact us at any time!</p>\r\n<p><br /><br />Kind regards,<br />Your openDesktop Team <br /><a href=\"mailto:contact@opendesktop.org\" target=\"_blank\">contact@opendesktop.org</a><br /><br /></p>','2014-04-24 08:40:27','2015-12-09 17:29:18',NULL),(13,'tpl_social_mail_product','<%sender%> sent you a recommendation','<p>&lt;%sender%&gt; has suggested that you could be interested in this product</p>\r\n<h2>%title%</h2>\r\n<p>from our opendesktop.org member <em>%username%</em>.\r\n</p>\r\n<p>%permalinktext%</p>\r\n<p><br />If the link doesn&rsquo;t work, you can copy and paste the following link to your browser:</p>\r\n<h4>%permalinktext%</h4>\r\n<p><br />Kind regards,<br />\r\n    Team opendesktop.org</p>','2013-11-08 10:46:42','2013-11-08 11:52:04',NULL),(14,'tpl_mail_claim_product','User wants to claim a product','<p>The opendesktop.org-system received a request from a user</p>\r <p>%userid% :: %username% :: %usermail%</p>\r <p>who wants to claim the following product:</p>\r <p>%productid% :: %producttitle%&nbsp;</p>\r <p>&nbsp;</p>\r <p>Greetings from the opendesktop.org-system</p>','2014-05-14 10:15:22','2014-05-14 10:43:21','0000-00-00 00:00:00'),(15,'tpl_mail_claim_confirm','opendesktop.org: We received your inquiry','<h2>Hello %username%,</h2>\r\n<p>you want to claim the following product:</p>\r\n<p><a href=\"%productlink%\">%producttitle%</a></p>\r\n<p>We try to process your request as quickly as possible.<br />You will receive a notice shortly if your claim has been approved.</p>\r\n<p><br /><br />Kind regards,<br />Team opendesktop.org&nbsp;<br /><a href=\"mailto:contact@opendesktop.org\">contact@opendesktop.org</a></p>','2014-05-14 10:39:59','2015-12-09 17:29:52',NULL),(16,'tpl_user_comment_note','opendesktop.org - You Received A New Comment','<h2>Hey %username%,</h2>\r\n<p><br />you received a new comment on <b>%product_title%</b></p>\r\n<p><br />Here is what the user wrote:</p>\r\n<div><br />%comment_text%</div>\r\n<p><br /><br />Please do not reply to the email, but use the comment system for this product instead:<br />\r\n<a href=\"https://www.opendesktop.org/p/%product_id%\">%product_title%</a></p>\r\n<p><br /><br />Kind regards,<br />Your openDesktop Team <br /><a href=\"mailto:contact@opendesktop.org\" target=\"_blank\">contact@opendesktop.org</a><br /><br /></p>','2016-09-15 08:16:00','2016-09-15 08:31:07',NULL),(17,'tpl_verify_email','%servername% - Please verify your email address','<h2>Hey %username%,</h2>\r\n<p>\r\n  Help us secure your account by verifying your email address\r\n  (<a href=\"mailto:%email_address%\">%email_address%</a>).\r\n    This will let you receive notifications and password resets from our system.\r\n</p>\r\n<div><!-- [if mso]>\r\n    <v:roundrect xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:w=\"urn:schemas-microsoft-com:office:word\"\r\n                 href=\"%verificationurl%\" style=\"height:40px;v-text-anchor:middle;width:300px;\" arcsize=\"10%\"\r\n                 stroke=\"f\" fillcolor=\"#34495C\">\r\n        <w:anchorlock/>\r\n        <center style=\"color:#ffffff;font-family:sans-serif;font-size:16px;font-weight:bold;\">\r\n            Verify your e-mail address here\r\n        </center>\r\n    </v:roundrect>\r\n    <![endif]--> <!-- [if !mso]> <!-->\r\n<table cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"-webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; color: #ffffff; display: block;\" align=\"center\" bgcolor=\"#34495C\" width=\"300\" height=\"40\"><a style=\"color: #ffffff; font-size: 16px; font-weight: bold; font-family: sans-serif; text-decoration: none; line-height: 40px; width: 100%; display: inline-block;\" href=\"%verificationurl%\"> Verify your e-mail address </a></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<!-- <![endif]--></div>\r\n<p><br />If the button doesn&rsquo;t work, you can copy and paste the following link to your browser:<br /><br />%verificationlink%&nbsp;</p>\r\n<p><br />If you have any problems, feel free to contact us at any time!</p>\r\n<p><br /><br />Kind regards,<br />Your openDesktop Team <br /><a href=\"mailto:contact@opendesktop.org\" target=\"_blank\">contact@opendesktop.org</a><br /><br /></p>','2016-09-23 07:16:31','2016-09-23 07:16:31',NULL),(18,'tpl_user_comment_reply_note','opendesktop.org - You received a new reply to your comment','<h2>Hey %username%,</h2>\r\n<p><br />you received a new reply to your comment on <b>%product_title%</b></p>\r\n<p><br />Here is what the user wrote:</p>\r\n<div><br />%comment_text%</div>\r\n<p><br /><br />Please do not reply to the email, but use the comment system for this product instead:<br />\r\n<a href=\"https://www.opendesktop.org/p/%product_id%\">%product_title%</a></p>\r\n<p><br /><br />Kind regards,<br />Your openDesktop Team <br /><a href=\"mailto:contact@opendesktop.org\" target=\"_blank\">contact@opendesktop.org</a><br /><br /></p>','2016-10-07 10:49:15','2016-10-07 10:49:15',NULL);
/*!40000 ALTER TABLE `mail_template` ENABLE KEYS */;
UNLOCK TABLES;

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
  `roleId` int(11) NOT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT 'default-profile.png',
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
  PRIMARY KEY (`member_id`),
  KEY `uuid` (`uuid`),
  KEY `idx_created` (`created_at`),
  KEY `idx_login` (`mail`,`username`,`password`,`is_active`,`is_deleted`,`login_method`),
  KEY `idx_mem_search` (`member_id`,`username`,`is_deleted`,`mail_checked`),
  KEY `idx_source` (`source_id`,`source_pk`),
  KEY `idx_username` (`username`),
  KEY `idx_id_active` (`member_id`,`is_active`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member`
--

LOCK TABLES `member` WRITE;
/*!40000 ALTER TABLE `member` DISABLE KEYS */;
INSERT INTO `member` VALUES (24,'88d29e42a4b2476ea8b27fd4bb258ad4','dummy','dummy@dummy.de','889324c8e02fe90d559d2f9095c3284d',100,'0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png',0,1,0,1,1,0,'local','cc','dd','','','Berlin','','','2018-05-28 19:10:43','','',NULL,'',NULL,53,'https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','https://cn.pling.com/cache/1920x450-2/img/5/7/c/4/9ff2159787a3438bffbe16d6ac24ec00a6f3.jpg','local',NULL,NULL,'','','','http://www.fcaebook.com','','www.pling.cc','','dschinni','2014-09-16 11:41:02',0,'2013-04-15 13:18:34','2018-05-28 19:10:43',NULL,0,NULL,0),(25,'4ba35312870546448c515720fbe68d4a','e16fd708','e16fd708@opayq.com','e10adc3949ba59abbe56e057f20f883e',300,'5/6/d/3/f13a53215da96351fc96827da27c43645b94.png',0,0,0,0,0,0,'local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1209920,'http://cn.pling.it/cache/200x200-2/img/5/6/d/3/f13a53215da96351fc96827da27c43645b94.png',NULL,'local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2018-05-25 20:03:24','2018-05-25 20:03:24',NULL,0,NULL,0),(26,'1d3958cc48a840aaa7336aa2bd0bde36','e9c57324','e9c57324@opayq.com','e10adc3949ba59abbe56e057f20f883e',300,'8/d/2/2/c2ba924ea7b7e1f0da82b3aa3866c0025069.png',0,1,0,1,0,0,'local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1209921,'http://cn.pling.it/cache/200x200-2/img/8/d/2/2/c2ba924ea7b7e1f0da82b3aa3866c0025069.png',NULL,'local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2018-05-25 20:11:45','2018-05-25 20:12:12',NULL,0,NULL,0);
/*!40000 ALTER TABLE `member` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `member_created` BEFORE INSERT ON `member` FOR EACH ROW BEGIN

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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `member_BEFORE_UPDATE` BEFORE UPDATE ON `member` FOR EACH ROW BEGIN

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
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  UNIQUE KEY `uk_month_proj` (`yearmonth`,`member_id`,`project_id`),
  KEY `idx_yearmonth` (`yearmonth`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_dl_plings`
--

LOCK TABLES `member_dl_plings` WRITE;
/*!40000 ALTER TABLE `member_dl_plings` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_dl_plings` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER member_dl_plings_BEFORE_INSERT BEFORE INSERT ON member_dl_plings FOR EACH ROW

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_download_history`
--

LOCK TABLES `member_download_history` WRITE;
/*!40000 ALTER TABLE `member_download_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_download_history` ENABLE KEYS */;
UNLOCK TABLES;

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
  PRIMARY KEY (`email_id`),
  KEY `idx_address` (`email_address`),
  KEY `idx_member` (`email_member_id`),
  KEY `idx_verification` (`email_verification_value`)
) ENGINE=InnoDB AUTO_INCREMENT=417845 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_email`
--

LOCK TABLES `member_email` WRITE;
/*!40000 ALTER TABLE `member_email` DISABLE KEYS */;
INSERT INTO `member_email` VALUES (1,24,'dummy@dummy.de',1,0,'2013-04-15 13:18:34','2013-04-15 13:18:34','2a3b9cbabb5ca1c6649d912482408cbc'),(413665,24,'cc@cc.de',0,1,'2017-06-12 10:41:01',NULL,'a9f26b0bf16c2ee8fb572ccea4ae9d22'),(417842,24,'6e39aaba@opayq.com',0,0,'2017-08-03 09:58:29','2017-08-03 09:58:55','9fc1114684c5bd251a7f5bb2150047b3'),(417843,25,'e16fd708@opayq.com',1,0,'2018-05-25 20:03:24',NULL,'5aed1cec2f67b8472837868e87b6ce56'),(417844,26,'e9c57324@opayq.com',1,0,'2018-05-25 20:11:45','2018-05-25 20:12:12','2aacfdd2e96c4d3dcf081174bad6cb8c');
/*!40000 ALTER TABLE `member_email` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER member_email_BEFORE_INSERT BEFORE INSERT ON member_email FOR EACH ROW

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='Logs all changes on table member (no inserts, only updates)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_log`
--

LOCK TABLES `member_log` WRITE;
/*!40000 ALTER TABLE `member_log` DISABLE KEYS */;
INSERT INTO `member_log` VALUES (1,26,'is_active','0','1','2018-05-25 18:12:12'),(2,26,'mail_checked','0','1','2018-05-25 18:12:12');
/*!40000 ALTER TABLE `member_log` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table for our monthly payouts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_payout`
--

LOCK TABLES `member_payout` WRITE;
/*!40000 ALTER TABLE `member_payout` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_payout` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER member_payout_BEFORE_INSERT BEFORE INSERT ON member_payout FOR EACH ROW

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_paypal`
--

LOCK TABLES `member_paypal` WRITE;
/*!40000 ALTER TABLE `member_paypal` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_paypal` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `member_role`
--

LOCK TABLES `member_role` WRITE;
/*!40000 ALTER TABLE `member_role` DISABLE KEYS */;
INSERT INTO `member_role` VALUES (100,'Administrator','admin',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(200,'Mitarbeiter','staff',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(300,'FrontendBenutzer','feuser',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(400,'Moderator','moderator',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(500,'SystemUser','sysuser',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `member_role` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_score`
--

LOCK TABLES `member_score` WRITE;
/*!40000 ALTER TABLE `member_score` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_score` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_score_factors`
--

LOCK TABLES `member_score_factors` WRITE;
/*!40000 ALTER TABLE `member_score_factors` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_score_factors` ENABLE KEYS */;
UNLOCK TABLES;

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
  `token_value` varchar(255) NOT NULL,
  `token_provider_username` varchar(45) DEFAULT NULL,
  `token_fingerprint` varchar(45) DEFAULT NULL,
  `token_created` datetime DEFAULT NULL,
  `token_changed` datetime DEFAULT NULL,
  `token_deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`token_id`),
  KEY `idx_token` (`token_member_id`,`token_provider_name`,`token_value`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_token`
--

LOCK TABLES `member_token` WRITE;
/*!40000 ALTER TABLE `member_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_token` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `member_token_before_insert` BEFORE INSERT ON `member_token` FOR EACH ROW BEGIN

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
-- Dumping data for table `package_types`
--

LOCK TABLES `package_types` WRITE;
/*!40000 ALTER TABLE `package_types` DISABLE KEYS */;
INSERT INTO `package_types` VALUES (1,'AppImage',1,1),(2,'Android (APK)',2,1),(3,'OS X compatible',3,1),(4,'Windows executable',4,1),(5,'Debian',5,1),(6,'Snappy',6,1),(7,'Flatpak',7,1),(8,'Electron-Webapp',8,1),(9,'Arch',9,1),(10,'open/Suse',10,1),(11,'Redhat',11,1),(12,'Source Code',12,1);
/*!40000 ALTER TABLE `package_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payout`
--

DROP TABLE IF EXISTS `payout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `yearmonth` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `timestamp_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `timestamp_success` timestamp NULL DEFAULT NULL,
  `paypal_ipn` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_PAYOUT` (`yearmonth`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table for our monthly payouts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payout`
--

LOCK TABLES `payout` WRITE;
/*!40000 ALTER TABLE `payout` DISABLE KEYS */;
/*!40000 ALTER TABLE `payout` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payout_status`
--

DROP TABLE IF EXISTS `payout_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payout_status` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `title` varchar(50) DEFAULT NULL,
  `description` text,
  `color` varchar(50) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'glyphicon-info-sign',
  `is_active` int(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payout_status`
--

LOCK TABLES `payout_status` WRITE;
/*!40000 ALTER TABLE `payout_status` DISABLE KEYS */;
INSERT INTO `payout_status` VALUES (1,'info','Status: Requested','We send your payout. The actual status is: Requested.','#31708f;','glyphicon-info-sign',0),(10,'info','Status: Processed','We send your payout. The actual status is: Processed.','#31708f;','glyphicon-info-sign',0),(50,'info','Status: Pending','We send your payout. The actual status is: Pending.','#31708f;','glyphicon-info-sign',1),(99,'info','Status: Refund','We tried to payout your plings, but your payment was refund.','#112c8b;','glyphicon-info-sign',0),(100,'success','Status: Completed','For this month we has successfully paid you.','#3c763d;','glyphicon-ok-sign',1),(900,'info','Status: Refunded','We send you the payment, but you refunded it. ','#0f2573','glyphicon-exclamation-sign',1),(901,'info','Status: Refunded by Paypal','Your Mailadress is not signed up for a PayPal account or you did not complete the registration process.','#112c8b','glyphicon-info-sign',1),(910,'warning','Status: Not allowed','PayPal denies our payment because you only can receive website payments. Please change your settings on PayPal.','#bd8614','glyphicon-exclamation-sign',1),(920,'warning','Status: Personal Payments','We tried to send you money, but the PayPal message was: Sorry, this recipient can’t accept personal payments.','#bd8614','glyphicon-exclamation-sign',1),(930,'danger','Status: currently unable','We tried to send you money, but Paypal denied this with the following message: This recipient is currently unable to receive money.','#a94442;','glyphicon-exclamation-sign',1),(940,'danger','Status: Denied','We tried to send you money, but Paypal denied this with the following message: We can’t send your payment right now. If you keep running into this issue, please contact.','#a94442;','glyphicon-exclamation-sign',1),(950,'danger','Status: Failed','Our Payment failed','#a94442;','glyphicon-exclamation-sign',1),(999,'danger','API Error','We tried to send the money automatically via the Paypal-API, but we temporarily got an error.  We will try the payout again, so please stay tuned.','#f71f1f','glyphicon-info-sign',1);
/*!40000 ALTER TABLE `payout_status` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Save all PayPal IPNs here';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paypal_ipn`
--

LOCK TABLES `paypal_ipn` WRITE;
/*!40000 ALTER TABLE `paypal_ipn` DISABLE KEYS */;
/*!40000 ALTER TABLE `paypal_ipn` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `paypal_valid_status`
--

LOCK TABLES `paypal_valid_status` WRITE;
/*!40000 ALTER TABLE `paypal_valid_status` DISABLE KEYS */;
INSERT INTO `paypal_valid_status` VALUES (100,'Valid','Valid - we can send you money per PayPal','green',1),(404,'Unknown Address','Invalid - Your PayPal-Address could not be found.','red',1),(500,'Invalid','Invalid - at the moment we can not send you money per PayPal','red',1),(501,'Can receive only from homepage.','Invalid - You can only receive money from homepage. Please change your Settings on the PayPal Website.','red',1),(502,'Can receive only personal payments.','Invalid - You can not receive personal payments. Please change your Settings on the PayPal Website.','red',1),(503,'Currently unable to receive money.','Invalid - You are currently unable to receive money. Please change your Settings on the PayPal Website.','red',1);
/*!40000 ALTER TABLE `paypal_valid_status` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plings`
--

LOCK TABLES `plings` WRITE;
/*!40000 ALTER TABLE `plings` DISABLE KEYS */;
/*!40000 ALTER TABLE `plings` ENABLE KEYS */;
UNLOCK TABLES;

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
  `deleted_at` datetime DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL COMMENT 'Member_id of the creator. Important for groups.',
  `facebook_code` text,
  `source_url` text,
  `twitter_code` text,
  `google_code` text,
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
  KEY `idx_ppload` (`ppload_collection_id`,`status`),
  KEY `idx_src_status` (`status`,`source_pk`,`source_type`)
) ENGINE=InnoDB AUTO_INCREMENT=1209935 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project`
--

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;
INSERT INTO `project` VALUES (53,24,'text',0,0,1,0,100,'63d5eab4f0f546ae9d1c3dfec063fe68',NULL,0,NULL,'It\'s me, Dummy! :)',NULL,NULL,'default-profile.png','default-profile.png',NULL,NULL,'2016-05-09 16:48:54','2018-05-24 17:29:28',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,NULL,0,0,NULL,NULL),(1099990,24,'text',296,0,0,0,40,'659191e37c99468a8749eba696e1f9a5',NULL,1,'Wallpaper Lucky Goats','V&vamus quis felis molestie, pretium nibh sit aet, commodo mauris? PraesenÖ et males$ada l\"rem. Duis henÖrerit suscipit ante id lacinia? Pellentesque porttitor, mauris et laoreet porta, arcu nulla feugiat libero, friÜgilla sollicitudin diam leo vitae diam! Etiam non lacus acÄumsan, \'ictüm urna vuüputate, äondimenÖum urna. Sed nec justo ex! SuÖpendisse aliquet hendÄerÖt erat, ac auctor sem t$nciduüt at. Integer fringilla condimentum dolor.\r\n\r\nDonec pretium convallis tortor, non eleifend dui? Nunc libero velt, hendrerit ac interdum id, f\"Öilisis ülementum est! Proin orna$e ä\'at nisi; sed euismod est ultrices quis. Mauris iacÄli& egestas diam at selerisque! Donec maximus ult&icies ultriöes. Ut seü auctor lectus. Sed ut est porttiÜor, blandit magnÜ eu, congue Änim. Aenean coümodo efficitur tortor sed pel$entesqu&.\r\n\r\nVestibulum eö velit sagittis, convallis risus ut, i\'perdiöt tellus. Sed eget diam condÄ$ÜntuÜ, suscipit erat at, egestas nöque? Curabitür dapibus libero sed ex varius malesuada. Nunc porta ipsum a vulputate variuÜ! Phas$ll$s mollis enim venenatis, porttitor ligula in, tempus erat? Pellentesque nibh felis, ultrices in convallis vitÖe, &lacerat a arcu. Integer at malesuada mauris, nÄn üccumsan arcu. Nullam at eros tortor.','',NULL,NULL,'8/2/3/7/55e40f90ca63cca7421730c5fbd2457ef266.jpg',NULL,NULL,'2016-05-11 09:20:20','2018-05-24 17:29:28','2017-07-10 05:03:05',24,'','','','','','','1462972916',0,NULL,0,0,0,1,0,0,NULL,NULL,NULL,1,1,0,0,0,NULL,NULL),(1150344,24,'text',318,0,0,0,40,'668d26f5a6974002bf206067cf85abc0',NULL,1,'testest','qt widgets','',NULL,NULL,'4/f/7/e/56931fd867ece19dd0da397d85c26f328e68.gif',NULL,NULL,'2016-08-05 06:28:11','2018-05-24 17:29:28','2017-02-03 04:08:06',24,'','','','','','','1485972156',0,NULL,0,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1151756,24,'text',261,0,0,0,10,'fca85db7e005453a80541530c2f37a03',NULL,1,'test','ewrw weq weq wer wer','',NULL,NULL,'',NULL,NULL,'2016-08-20 15:17:34','2018-05-24 17:29:28',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1170170,24,'text',203,0,0,0,100,'d8c6f1ce624d4f789096a0b47f951a24',NULL,1,'Kblocks Snappy','Snap Image for KBlocks','',NULL,NULL,'8/1/c/7/32d154ef84a55bed224a4e45d990c4e074b4.png',NULL,NULL,'2017-02-06 12:19:21','2018-05-24 17:29:28',NULL,24,'','','','','','','1486401592',0,NULL,0,0,0,1,0,0,NULL,NULL,NULL,0,0,NULL,0,0,NULL,NULL),(1170226,24,'text',205,0,0,0,100,'8d0c4f9c5d5e4c198b2ecaf9caffaaa3',NULL,1,'KAtomic Snap','KAtomic KDE Game','',NULL,NULL,'b/f/d/8/f9c7cd91be9773c064f505ca6ed4a06aa4b5.png',NULL,NULL,'2017-02-07 08:51:48','2018-05-24 17:29:28',NULL,24,'','','','','','','1486475516',0,NULL,0,0,0,1,0,0,NULL,NULL,NULL,2,0,NULL,0,0,NULL,NULL),(1180374,24,'text',282,0,0,0,30,'9c40cec3cd0648d4b08b7257d4517a1c',NULL,1,'test_20170614','test','',NULL,NULL,'7/5/4/f/8024df0142ccdea07069c7c2495ad388755d.jpg',NULL,NULL,'2017-06-09 03:19:11','2018-05-24 17:29:28','2017-07-10 05:02:56',24,'','','','','','','1496992787',0,NULL,0,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1183305,24,'text',404,0,0,0,30,'6cf2a0b8d0af41c9893f5db4a27dd01d',NULL,1,'testet','etegdsf tgedsgdfsg dsfg dsfg dsfg dsfg d','',NULL,NULL,'7/5/4/f/8024df0142ccdea07069c7c2495ad388755d.jpg',NULL,NULL,'2017-07-10 05:02:42','2018-05-24 17:29:28','2018-02-13 09:39:43',24,'','','','','','',NULL,0,NULL,0,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1183308,24,'text',282,0,0,0,30,'76e45e01e24c4328b576e81516486f49',NULL,1,'zzrzretz','erzrezretz','',NULL,NULL,'7/5/4/f/8024df0142ccdea07069c7c2495ad388755d.jpg',NULL,NULL,'2017-07-10 05:36:29','2018-05-24 17:29:28','2017-07-10 05:36:36',24,'','','','','','',NULL,0,NULL,0,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1193329,24,'text',58,0,0,0,10,'2279a23240a94a01903642358a15f17b',NULL,1,'test','test','',NULL,NULL,'',NULL,NULL,'2017-09-29 05:16:44','2018-05-24 17:29:28',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1193331,24,'text',305,0,0,0,10,'3675b3b5f48346f8961c0bcdd7819890',NULL,1,'budapest','budapest','',NULL,NULL,'',NULL,NULL,'2017-09-29 05:20:32','2018-05-24 17:29:28',NULL,24,'','','','','','','1506676859',0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1193335,24,'text',58,0,0,0,10,'2b388ab2583f4a0f975c18c177d6f38d',NULL,1,'test','test','',NULL,NULL,'',NULL,NULL,'2017-09-29 05:48:31','2018-05-24 17:29:28',NULL,24,'','','','','','','1506678529',0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1193336,24,'text',58,0,0,0,30,'750de692071d4819b18769c23d8a5489',NULL,1,'test','test','',NULL,NULL,'1/0/5/3/37e68079c7c07038076c5bf12accef7a0245.png',NULL,NULL,'2017-09-29 05:52:54','2018-05-24 17:29:28','2017-09-29 05:53:18',24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1193365,24,'text',58,0,0,0,40,'7568ffbef37e4fa5b68d6da020a3d8d3',NULL,1,'test add product with file','wefwe sdf sdfsdf sdfsd fsdf sdfs dfsdf','',NULL,NULL,'7/5/4/f/8024df0142ccdea07069c7c2495ad388755d.jpg',NULL,NULL,'2017-09-29 10:11:39','2018-05-24 17:29:28','2017-09-29 10:15:18',24,'','','','','','','1506694305',0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209919,24,'text',301,0,0,0,30,'bdfca4bc981e4120993f54aafa17af66',NULL,1,'https://www.google.de/','TESTing....','',NULL,NULL,'e/6/2/6/b109894e65dff46c216f983278bddd968774.jpg',NULL,NULL,'2018-01-24 08:30:05','2018-05-24 17:29:28','2018-01-24 08:31:04',24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209920,25,'text',0,0,0,0,10,'b647ff9345464912a52d471fc5998fa2',NULL,0,'Personal Page',NULL,NULL,NULL,'std_avatar_80.png','std_avatar_80.png',NULL,NULL,'2018-05-25 20:03:24','2018-05-25 20:03:24',NULL,25,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209921,26,'text',0,0,0,0,10,'c07a64b21a3c479f8b8b9433437c26a6',NULL,0,'Personal Page',NULL,NULL,NULL,'std_avatar_80.png','std_avatar_80.png',NULL,NULL,'2018-05-25 20:11:45','2018-05-25 20:11:45',NULL,26,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209922,24,'text',58,0,0,0,100,'418fd506065345ad92c6aa9b66e33ee6',NULL,1,'Minimal Material Solus Wallpapers','Minimal Material wallpapers for the infamous Solus OS! Day and night versions and a version to look great with the ever popular Adapta theme.\r\nAll wallpapers are 3840 x 2160.\r\nAs always, requests are more that welcome! Give me a shout in the comments or G+. http://google.com/+KarlSchneider1','',NULL,NULL,'9/4/1/c/087f0f78c922c82a48e72685129c0dea8b61.png',NULL,NULL,'2018-05-28 18:08:45','2018-05-28 18:08:45',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209923,24,'text',445,0,0,0,100,'3d41b489e3b44e3f80d6f8ddb7b47ee0',NULL,1,'Simple kmenu','idea simple menu for kde inspired by the android menu','',NULL,NULL,'b/d/7/2/0728b9d9dc4b7d1bcb8bcfa7e7b9e14eb9c4.png',NULL,NULL,'2018-05-28 19:12:10','2018-05-28 19:12:10',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209924,24,'text',466,0,0,0,100,'e89ea324030e49d9b6445572e8ad25af',NULL,1,'Mobicaching','Mobicaching is a Symbian application dedicated to geocachers.\r\n\r\nCurrently downloads data from:\r\n- Opencaching.nl\r\n- Opencaching.pl\r\n- Opencaching.org.uk\r\n- Opencaching.us\r\n- Opencaching.com\r\n\r\nGeocaches, with their logs and images, may be saved in device\\\\\\\'s memory so there is no need to maintain network connection. There is also possibility to save geocache position as landmark in Nokia Maps.','',NULL,NULL,'2/a/a/9/3c23c0f8fc2cacec64ca508671baef53713b.jpg',NULL,NULL,'2018-05-28 19:15:43','2018-05-28 19:15:43',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209925,24,'text',135,0,0,0,100,'0fe8e708989745369dbb46670ce7694f',NULL,1,'Dark-Side','Dark-Side\r\n\r\nOpen image in a new tab and maximize to get a decent view\r\n\r\nDArk-Side is a true dark theme, and comes in all colors as long it is black...\r\n\r\nPlease, try and rate, comment. Any input or criticism is welcome.\r\n\r\nRequirements:\r\n\r\nGTK+ 3.20 or later. Only standard (or Ubuntu) gnome-desktop is supported. Ready for Ubuntu 18.04.\r\n\r\nGTK2 ENGINES REQUIREMENT\r\n\r\n- GTK2 engine Murrine\r\n- GTK2 engine Pixbuf\r\n\r\nFedora/RedHat distros:\r\nyum install gtk-murrine-engine gtk2-engines\r\n\r\nUbuntu/Mint/Debian distros:\r\nsudo apt-get install gtk2-engines-murrine gtk2-engines-pixbuf\r\n\r\nArchLinux:\r\npacman -S gtk-engine-murrine gtk-engines\r\n\r\nWhat to do:\r\n\r\nExtract and put it into the themes directory i.e. ~/.themes/ or /usr/share/themes/ (create it if necessary).Then change the theme via distribution specific tool like Gnome tweak tool or Unity tweak tool, etc. (If you use Snap-packages instead of app\'s from the normal repositories than definitely put the theme to /usr/share/themes/.\r\n\r\nThis theme is based on the dark-theme of the Arc theme, by Horst 3180, under license GPLv3.\r\nhttps://github.com/horst3180/arc-theme','',NULL,NULL,'6/2/5/6/ee5926f842802027dcd64b11587b565e391e.jpg',NULL,NULL,'2018-05-28 19:17:37','2018-05-28 19:17:37',NULL,24,'','','','','','',NULL,0,NULL,1,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209926,24,'text',174,0,0,0,100,'1f88b68472054b1b9d297bffd794215c',NULL,1,'KDE/KaOS 002 Dark Gray Menu Button','KDE/KaOS 002 Dark Gray Menu Button is a great replacement of your present option\r\n\r\nDon\'t be shy, give it a try, hesitation isn\'t good for you, trust me, I\'m a doctor ;-). I believe in you capability to download and set it up, after all what have you got to lose, if you don\'t like it you can always bin it and once you change your mind, bring it back.\r\n\r\nIMPORTANT NOTE: If you voted out of favour because you had a bad day or acted upon feeling like a spoiled toddler, be aware that I consider it as indeed very immature and silly. On contrary, if you liked what you got, definitely feel free to vote + and possibly point others to the product site, you will actually do well if you do this, unlike most who in fact can\'t do anything else than express negativity because they think it matters.','',NULL,NULL,'7/1/1/d/dd5d40f3e3143a5a9209d1fba0223537b843.png',NULL,NULL,'2018-05-28 19:19:41','2018-05-28 19:19:41',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209927,24,'text',485,0,0,0,100,'2d102a6971f44025b347c69c93720e70',NULL,1,'Helia ( Gtv-Dvb )','Digital TV\r\n* DVB-T2/S2/C, ATSC, DTMB\r\nMedia Player\r\n* IPTV\r\n\r\nGraphical user interface - Gtk+3\r\nAudio & Video & Digital TV - Gstreamer 1.0\r\n\r\nDrag and Drop\r\n* folders\r\n* files\r\n* playlists - M3U, M3U8\r\n\r\nChannels\r\n* scan channels manually\r\n* scan initial file\r\n* convert - dvb_channel.conf ( dvbv5-scan {OPTION...} --output-format=DVBV5 initial file )','',NULL,NULL,'d/3/c/0/38d2e898e2d56b5b13ef2c59108bef4b9044.png',NULL,NULL,'2018-05-28 19:21:57','2018-05-28 19:21:57',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209928,24,'text',404,0,0,0,100,'932e83220e3b4415a6dce6b46af01aba',NULL,1,'EQuilibrium Level Three','GNU/Linux distributions EQuilibrium \"Level Three\" New Experience - beta\r\nBrings a whole new experience ... a modern, stable and fast GNU/Linux distribution.\r\nEQuilibrium is designed to suit more to portable computers (such as laptops, ultrabooks etc.) and computers with smaller monitors (up to 19\"). Made is for the people as who seek for a stable operating system, and spend a lot of time on the Internet.\r\n0 comments','',NULL,NULL,'6/e/e/b/3572f33092ba0b8c92b4b79db23cd182b399.png',NULL,NULL,'2018-05-28 19:23:57','2018-05-28 19:23:57',NULL,24,'','','','','','',NULL,0,NULL,1,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209929,24,'text',324,0,0,0,100,'4af51873cb614bc7a1de0a73209099a2',NULL,1,'Twitch.tv playlist parser','Twitch.tv playlist parser\r\n\r\nInstall:\r\n1. I have included a client_id in this script. If it gets blocked in the future, you can generate your own client at https://www.twitch.tv/settings/connections and put it in the file.\r\n2. Put the file in the lua/playlist/ directory:\r\n- On Windows: %APPDATA%/vlc/lua/playlist/\r\n- On Mac: $HOME/Library/Application Support/org.videolan.vlc/lua/playlist/\r\n- On Linux: ~/.local/share/vlc/lua/playlist/\r\n- On Linux (snap package): ~/snap/vlc/current/.local/share/vlc/lua/playlist/\r\nTo install the addon for all users, put the file here instead:\r\n- On Windows: C:/Program Files (x86)/VideoLAN/VLC/lua/playlist/\r\n- On Mac: /Applications/VLC.app/Contents/MacOS/share/lua/playlist/\r\n- On Linux: /usr/lib/vlc/lua/playlist/\r\n- On Linux (snap package): /snap/vlc/current/usr/lib/vlc/lua/playlist/\r\n3. Open a twitch.tv url using \"Open Network Stream...\"\r\n\r\nIf you are using a Mac and have Homebrew installed, you can download and install with one Terminal command:\r\nbrew install --no-sandbox --HEAD stefansundin/tap/vlc-twitch\r\n\r\nIf you are using a Mac without Homebrew, you can still install by running:\r\nmkdir -p \"$HOME/Library/Application Support/org.videolan.vlc/lua/playlist/twitch.lua\"\r\ncurl -o \"$HOME/Library/Application Support/org.videolan.vlc/lua/playlist/twitch.lua\" https://gist.githubusercontent.com/stefansundin/c200324149bb00001fef5a252a120fc2/raw/twitch.lua\r\n\r\nOn Linux, you can download and install by running:\r\nmkdir -p ~/.local/share/vlc/lua/playlist/\r\ncurl -o ~/.local/share/vlc/lua/playlist/twitch.lua https://gist.githubusercontent.com/stefansundin/c200324149bb00001fef5a252a120fc2/raw/twitch.lua\r\n\r\nFeatures:\r\n- Load up a channel and watch live, e.g.: https://www.twitch.tv/speedgaming\r\n- Load an archived video, e.g.: https://www.twitch.tv/videos/113837699\r\n- Load a collection, e.g.: https://www.twitch.tv/videos/137244955?collection=JAFNfSvAtxS25w\r\n- Load a game and get the top streams, e.g.: https://www.twitch.tv/directory/game/Minecraft\r\n- Load a game\'s archived videos, e.g.: https://www.twitch.tv/directory/game/Minecraft/videos/all\r\n- Load a community and get the top streams, e.g.: https://www.twitch.tv/communities/speedrunning\r\n- Load a channel\'s most recent videos, e.g.: https://www.twitch.tv/speedgaming/videos/all\r\n- Load the homepage and get a list of featured streams: https://www.twitch.tv/\r\n- Load Twitch Clips, e.g.: https://clips.twitch.tv/AmazonianKnottyLapwingSwiftRage\r\n- Load a channel\'s clips, e.g.: https://www.twitch.tv/speedgaming/clips\r\n- Load a game\'s clips, e.g.: https://www.twitch.tv/directory/game/Minecraft/clips\r\n- Load the next page.\r\n\r\nIf you are experiencing issues (e.g. seeking), make sure that you are using VLC 3.0. You can also try nightlies: https://nightlies.videolan.org/\r\n\r\nIn order to load VODs with a timestamp in the url (e.g. ?t=1h10m10s), then you must also install the Twitch.tv extension from here: https://gist.githubusercontent.com/stefansundin/c200324149bb00001fef5a252a120fc2/raw/twitch-extension.lua\r\nNote that this extension must be activated in the VLC menu each time VLC is started (if you know of a workaround for this, please let me know in the comments below).\r\n\r\nIf you like this addon, please click the [+] in the top right corner. If you have any issues, please report them in the comments below. Thank you!\r\n\r\nNote: I expect this addon to stop working on Dec. 31, 2018. This is because API v3 will be deprecated at that time. I am not sure it will be possible to fix, but I will try my best.\r\n\r\nEnjoy!!','',NULL,NULL,'5/8/d/5/176b0d3de4744f317075901082d47ebdd70c.png',NULL,NULL,'2018-05-28 19:25:13','2018-05-28 19:25:13',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209930,24,'text',132,0,0,0,100,'2c456a3325594efa97a6363f6f776046',NULL,1,'Flat Remix icon theme','Flat Remix icon theme is licensed under the GNU General Public License v3.0\r\n\r\n# New Folder Color support:\r\nhttp://foldercolor.tuxfamily.org\r\n\r\n\r\n## Please up vote or press the plus sign to show your support.\r\n\r\n\r\n# Web Page: https://drasite.com/flat-remix\r\n# Github: https://github.com/daniruiz/Flat-Remix\r\n\r\nFlat remix is a pretty simple icon theme inspired on material design. It is mostly flat with some shadows, highlights and gradients for some depth and uses a colorful palette with nice contrasts.\r\n\r\nFlat Remix GTK:\r\nhttps://www.opendesktop.org/p/1214931/\r\nFlat Remix GNOME theme:\r\nhttps://www.opendesktop.org/p/1013030/\r\nFlat Remix DARK GNOME theme:\r\nhttps://www.opendesktop.org/p/1197717/\r\nFlat Remix miami GNOME theme:\r\nhttps://www.gnome-look.org/p/1205642/\r\nFlat Remix DARK miami GNOME theme:\r\nhttps://www.gnome-look.org/p/1197969/\r\n\r\n---------------------------------------\r\n\r\n# Files\r\nFlat Remix - main icon theme\r\nFlat Remix Dark - for dark interfaces\r\nFlat Remix Light - for light interfaces\r\nFlat Remix git version (master) - latest version fom github (may not be stable)\r\n\r\n---------------------------------------\r\n\r\n# Installation\r\n\r\n1. Download and uncompress the zip file.\r\n2. Move \"Flat Remix\" folder to \".icons\" in your home directory.\r\n3. To set the theme, run the following command in Terminal:\r\n.... # gsettings set org.gnome.desktop.interface icon-theme \"Flat Remix\"\r\n.... or select \"Flat Remix\" as icon theme via distribution specific tweaktool.','',NULL,NULL,'0/4/2/b/9fdfe3c7b33cd2c6805e884ff9a94c87bc80.png',NULL,NULL,'2018-05-28 19:30:29','2018-05-28 19:30:29',NULL,24,'','','','','','',NULL,0,NULL,1,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209931,24,'text',100,0,0,0,100,'0ab532c9b80d43eea224940b4e3f6c4c',NULL,1,'Modified Oxygen-Air Theme','Modified Oxygen-Air KDM Theme. The background is not so good because I have to use a picture of my own.','',NULL,NULL,'3/b/0/f/86e909ed985d17b9e57227f3c7fe884bbac5.png',NULL,NULL,'2018-05-28 19:32:03','2018-05-28 19:32:03',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209932,24,'text',104,0,0,0,100,'b1122b29d34e46459232251c9d21629a',NULL,1,'Nilium','A dark theme based on Noc and Helium\r\n\r\nI hope you like it :-)\r\n\r\nWallpaper: https://www.opendesktop.org/p/1227884/\r\nIcons: https://www.opendesktop.org/p/1188266/\r\n\r\nIf you like my work, please press the \"Pling me\" button to make a donation :-)','',NULL,NULL,'d/9/7/c/c31d25a1b18ae458371ebfbb18343045a092.png',NULL,NULL,'2018-05-28 19:34:34','2018-05-28 19:34:34',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209933,24,'text',140,0,0,0,100,'d24558026e46467aa50e9aa1664d730d',NULL,1,'Equilux for Openbox','Openbox theme for Equilux by ddnexus.\r\nFor a good effect recommended titelbar\'s font size:\r\nEquilux - 11\r\nEquilux-compact - 10','',NULL,NULL,'9/a/5/4/6ab11d1cec21d5f2113d242e404385561577.png',NULL,NULL,'2018-05-28 19:37:41','2018-05-28 19:37:41',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL),(1209934,24,'text',139,0,0,0,100,'2718548117744a33959037cb1e98bd05',NULL,1,'Equilux Theme','A theme to match the equilux theme','',NULL,NULL,'a/d/e/3/26a121cf17a6c5bfab86a0b04c933b1fb87e.png',NULL,NULL,'2018-05-28 19:39:31','2018-05-28 19:39:31',NULL,24,'','','','','','',NULL,0,NULL,0,0,0,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL);
/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `project_created` BEFORE INSERT ON `project` FOR EACH ROW BEGIN

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
) ENGINE=InnoDB AUTO_INCREMENT=487 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_category`
--

LOCK TABLES `project_category` WRITE;
/*!40000 ALTER TABLE `project_category` DISABLE KEYS */;
INSERT INTO `project_category` VALUES (1,641,644,'3D Printing',0,1,NULL,NULL,10,1,0,1,'2015-01-14 13:06:56','2018-03-14 12:24:09','2015-03-17 13:29:43'),(2,679,680,'Websites',0,1,NULL,NULL,20,1,0,1,'2015-01-14 13:06:56','2018-03-14 12:24:09','2015-07-13 10:49:47'),(3,39,72,'Books',0,0,'',NULL,30,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06','2010-11-19 10:26:45'),(4,73,76,'Comics',0,0,'',NULL,40,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06','2010-11-19 10:27:17'),(5,673,678,'Videos',0,0,'',NULL,120,1,0,1,'2015-01-14 13:06:56','2018-03-14 12:24:09','2011-06-09 16:34:57'),(6,706,721,'Games',1,0,'','',60,1,1,1,'2010-08-24 15:52:09','2018-03-14 12:24:09','2010-11-19 10:28:29'),(7,649,654,'Music',0,0,'','',70,1,0,1,'2010-08-24 15:52:14','2018-03-14 12:24:09','2010-11-19 10:27:27'),(8,671,672,'Other',0,1,NULL,NULL,999,1,0,1,'2010-08-24 15:52:22','2018-03-14 12:24:09','2015-03-17 13:32:23'),(30,11,38,'Computer Artwork',0,1,NULL,NULL,90,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06','2016-07-05 05:44:09'),(32,655,666,'Podcasts',0,0,'',NULL,100,1,0,1,'2015-01-14 13:06:56','2018-03-14 12:24:09',NULL),(33,1,10,'Apps',0,0,'',NULL,110,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(34,0,905,'root',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2018-03-14 12:24:09',NULL),(35,64,65,'Arts & Photography',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(36,66,67,'Biographies & Memoirs',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(37,68,69,'Business & Investing',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(38,58,59,'Calendars',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(39,890,891,'Comics',1,0,'','Comics',0,1,0,1,'2015-01-14 13:06:56','2018-03-14 12:24:09',NULL),(40,42,51,'Computers & Technology',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(41,56,57,'Sports & Outdoors',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(42,60,61,'Travel',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(43,70,71,'Cookbooks, Food & Wine',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(44,40,41,'Health, Fitness & Dieting',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(45,52,53,'Literature & Fiction',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(46,54,55,'Mystery, Thriller & Suspense',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(47,366,367,'Plasma 5 Plasmoids',0,1,'',NULL,0,1,0,1,'2015-01-14 13:06:56','2018-03-14 12:24:09',NULL),(48,8,9,'Windows Apps',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(52,4,5,'Linux Apps',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(53,6,7,'Jolla Apps',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(54,43,44,'Perl',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(55,45,46,'PHP',1,0,NULL,NULL,0,1,0,1,'2015-01-14 13:06:56','2017-09-01 14:55:06',NULL),(56,47,48,'Python',1,0,NULL,NULL,0,1,0,1,'2015-01-08 14:33:26','2017-09-01 14:55:06',NULL),(57,162,163,'Cinnamon Desklets',1,0,'cinnamon_desklets','',0,1,0,1,'2015-01-08 14:39:17','2018-03-06 11:43:53',NULL),(58,862,863,'Wallpaper Other',1,0,'wallpapers','',0,1,0,1,'2015-01-08 14:52:31','2018-03-14 12:24:09',NULL),(59,32,33,'Themes',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:53:57','2017-09-01 14:55:06','2015-07-13 10:32:34'),(60,20,21,'Icon Themes',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:54:05','2017-09-01 14:55:06','2016-03-04 05:58:57'),(61,22,23,'Mouse Cursors',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:54:25','2017-09-01 14:55:06','2016-03-04 06:01:28'),(62,34,35,'Window Decorations',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:54:54','2017-09-01 14:55:06','2015-07-13 10:35:22'),(63,12,13,'Aurorae Window Decorations',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:55:10','2017-09-01 14:55:06','2016-03-04 05:51:03'),(64,14,15,'Beryl Window Decorations',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:55:27','2017-09-01 14:55:06','2016-03-04 05:51:09'),(65,30,31,'Plasma4 Themes',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:55:57','2017-09-01 14:55:06','2015-07-13 10:37:15'),(66,18,19,'Plasma 5 Themes',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:56:05','2017-09-01 14:55:06','2016-03-04 05:58:52'),(67,28,29,'Cinnamon Themes',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:56:13','2017-09-01 14:55:06','2016-03-04 06:00:20'),(68,26,27,'QtCurve Themes',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:56:52','2017-09-01 14:55:06','2016-03-04 05:59:17'),(69,24,25,'Gnome Shell Themes',0,1,NULL,NULL,0,1,0,1,'2015-01-08 14:57:17','2017-09-01 14:55:06','2016-03-04 05:59:04'),(78,77,78,'Blogs / News',0,1,NULL,NULL,0,1,0,1,'2015-01-27 15:00:00','2017-09-01 14:55:06','2015-03-17 13:30:03'),(79,62,63,'Fun',1,0,NULL,NULL,0,1,0,1,'2015-03-11 12:01:18','2017-09-01 14:55:06',NULL),(80,719,720,'Fun',0,1,NULL,NULL,0,1,0,1,'2015-03-11 16:15:26','2018-03-14 12:24:09','2016-06-03 04:45:57'),(81,74,75,'test',1,0,NULL,NULL,0,1,0,1,'2015-03-13 10:24:05','2017-09-01 14:55:06',NULL),(82,717,718,'Android',0,1,NULL,NULL,0,1,0,1,'2015-03-13 13:08:22','2018-03-14 12:24:09','2016-06-03 04:46:21'),(83,645,648,'Test',0,1,NULL,NULL,0,1,0,1,'2015-03-13 16:08:30','2018-03-14 12:24:09','2015-04-17 14:29:59'),(84,646,647,'Test 2',0,1,NULL,NULL,0,1,0,1,'2015-03-17 09:17:05','2018-03-14 12:24:09','2015-04-17 14:31:27'),(85,642,643,'Test 3',0,1,NULL,NULL,0,1,0,1,'2015-04-01 18:34:01','2018-03-14 12:24:09','2015-04-17 14:30:10'),(86,667,670,'Pellentesue luctus rhoncus null a iaculis',0,1,NULL,NULL,0,1,0,1,'2015-04-01 20:30:28','2018-03-14 12:24:09',NULL),(87,668,669,'Aenean pretiu massa varius dignissi vestibulum',0,1,NULL,NULL,0,1,0,1,'2015-04-01 20:30:52','2018-03-14 12:24:09','2015-04-17 14:33:15'),(88,650,651,'Rock',1,0,NULL,NULL,0,1,0,1,'2015-07-13 10:36:07','2018-03-14 12:24:09',NULL),(89,652,653,'Dance',1,0,NULL,NULL,0,1,0,1,'2015-07-13 10:36:18','2018-03-14 12:24:09',NULL),(90,674,675,'Music',1,0,NULL,NULL,0,1,0,1,'2015-07-13 10:36:36','2018-03-14 12:24:09',NULL),(91,676,677,'Fun',1,0,NULL,NULL,0,1,0,1,'2015-07-13 10:36:44','2018-03-14 12:24:09',NULL),(92,658,659,'Health',1,0,NULL,NULL,0,1,0,1,'2015-07-13 10:48:15','2018-03-14 12:24:09',NULL),(93,660,661,'Culture',1,0,NULL,NULL,0,1,0,1,'2015-07-13 10:48:30','2018-03-14 12:24:09',NULL),(94,662,663,'Computer & Technology',1,0,NULL,NULL,0,1,0,1,'2015-07-13 10:48:55','2018-03-14 12:24:09',NULL),(95,664,665,'News',1,0,NULL,NULL,0,1,0,1,'2015-07-13 10:49:12','2018-03-14 12:24:09',NULL),(96,656,657,'Art',1,0,NULL,NULL,0,1,0,1,'2015-07-13 10:49:20','2018-03-14 12:24:09',NULL),(97,16,17,'Plasma Look\'n\'Feel',0,1,NULL,NULL,0,1,0,1,'2015-10-23 08:44:53','2017-09-01 14:55:06','2016-03-04 05:58:46'),(98,2,3,'Android Apps',1,0,NULL,NULL,0,1,0,1,'2015-12-27 19:52:25','2017-09-01 14:55:06',NULL),(99,36,37,'Xfce Themes',0,1,NULL,NULL,0,1,0,1,'2015-12-29 05:21:20','2017-09-01 14:55:06','2016-03-04 06:00:36'),(100,301,302,'KDM4 Themes',1,0,NULL,NULL,0,1,0,1,'2016-03-04 05:43:35','2018-03-14 12:24:09',NULL),(101,299,300,'SDDM Login Themes',1,0,'','SDDM Theme',0,1,0,1,'2016-03-04 05:43:50','2018-03-14 12:24:09',NULL),(102,549,550,'Dolphin Service Menus',1,0,'','',0,1,0,1,'2016-03-04 05:44:00','2018-03-14 12:24:09',NULL),(103,240,241,'Fonts',1,0,'fonts','',0,1,0,1,'2016-03-04 05:44:08','2018-03-06 11:43:53',NULL),(104,128,129,'Plasma Themes',1,0,'plasma5_desktopthemes','Plasma Theme',0,1,0,1,'2016-03-04 05:44:24','2018-02-19 10:35:07',NULL),(105,171,172,'Plasma 5 Add-Ons',1,0,'plasma5_plasmoids','',0,1,1,1,'2016-03-04 05:44:32','2018-03-06 11:43:53',NULL),(106,192,193,'Plasma 4 Widgets',1,0,'plasma4_plasmoids','',0,1,1,1,'2016-03-04 05:44:40','2018-03-06 11:43:53',NULL),(107,292,293,'Cursors',1,0,'cursors','X11 Mouse Theme',0,1,0,1,'2016-03-04 05:44:58','2018-03-14 12:24:09',NULL),(108,277,278,'Plymouth Themes',1,0,'','',0,1,1,1,'2016-03-04 05:45:06','2018-03-14 12:25:19',NULL),(109,275,276,'GRUB Themes',1,0,'','',0,1,1,1,'2016-03-04 05:45:14','2018-03-14 12:25:54',NULL),(110,273,274,'Plasma 5 Splash Screens',0,0,'','',0,1,0,1,'2016-03-04 05:45:23','2018-03-07 10:31:44',NULL),(111,271,272,'Plasma Splash Screens',1,0,'','KDE 4.x Splash Screen',0,1,1,1,'2016-03-04 05:45:32','2018-03-14 12:26:02',NULL),(112,124,125,'Plasma Color Schemes',1,0,'plasma_color_schemes','KDE Color Scheme KDE4',0,1,0,1,'2016-03-04 05:45:41','2018-02-19 10:35:07',NULL),(113,361,362,'Emoticons',1,0,'emoticons','Emoticon Theme',0,1,0,1,'2016-03-04 05:45:47','2018-03-14 12:24:09',NULL),(114,327,328,'Aurorae Themes',1,0,'aurorae_themes','Window Decoration Aurorae',0,1,0,1,'2016-03-04 05:46:13','2018-03-14 12:24:09',NULL),(115,636,637,'Plasma Icon Themes',0,1,'icons','KDE Icon Theme',0,1,0,1,'2016-03-04 05:47:13','2018-03-14 12:24:09','2016-07-05 05:45:02'),(116,314,315,'Compiz Themes',1,0,'compiz_themes','Compiz Theme',0,1,0,1,'2016-03-04 05:47:20','2018-03-14 12:24:09',NULL),(117,329,330,'Beryl/Emerald Themes',1,0,'beryl_themes','Beryl Emerald Theme',0,1,0,1,'2016-03-04 05:47:28','2018-03-14 12:24:09',NULL),(118,331,332,'deKorator Themes',1,0,'dekorator_themes','Window Decoration deKorator',0,1,0,1,'2016-03-04 05:47:35','2018-03-14 12:24:09',NULL),(119,135,136,'QtCurve',1,0,'qtcurve','',0,1,0,1,'2016-03-04 05:47:43','2018-02-19 10:35:07',NULL),(120,83,84,'Various KDE 1.-4. Styles',1,0,'','',0,1,1,1,'2016-03-04 05:47:51','2018-02-19 10:35:07',NULL),(121,126,127,'Plasma Look-and-Feel Packs',1,0,'plasma_look_and_feel','121',0,1,1,1,'2016-03-04 05:47:58','2018-02-19 10:35:07',NULL),(122,350,351,'QtCurve Styles',0,1,NULL,NULL,0,1,0,1,'2016-03-04 05:48:06','2018-03-14 12:24:09','2016-06-22 02:52:49'),(123,137,138,'Kvantum',1,0,'kvantum_themes','',0,1,0,1,'2016-03-04 05:48:13','2018-02-19 10:35:07',NULL),(124,153,154,'Conky',1,0,'conky','',0,1,0,1,'2016-03-04 05:48:20','2018-03-06 11:43:53',NULL),(125,333,334,'Metacity Themes',1,0,'metacity_themes','',0,1,0,1,'2016-03-04 05:48:27','2018-03-14 12:24:09',NULL),(126,577,578,'Nautilus Scripts',1,0,'nautilus_scripts','',0,1,0,1,'2016-03-04 05:48:34','2018-03-14 12:24:09',NULL),(127,247,248,'GnoMenu Skins',1,0,'','',0,1,0,1,'2016-03-04 05:48:40','2018-03-06 11:43:53',NULL),(128,409,410,'VLC Skins',1,0,'','VLC Skin',0,1,1,1,'2016-03-04 05:48:48','2018-03-14 12:24:09',NULL),(129,462,463,'XMMS Skins',1,0,NULL,NULL,0,1,0,1,'2016-03-04 05:48:56','2018-03-14 12:24:09',NULL),(130,269,270,'Gnome 2 Splash Screens',1,0,'','',0,1,1,1,'2016-03-04 05:49:03','2018-03-14 12:23:47',NULL),(131,303,304,'GDM Themes',1,0,NULL,NULL,0,1,0,1,'2016-03-04 05:49:09','2018-03-14 12:24:09',NULL),(132,359,360,'Icon Themes',1,0,'icons','KDE Icon Theme',0,1,0,1,'2016-03-04 05:49:16','2018-03-14 12:24:09',NULL),(133,81,82,'Cinnamon Themes',1,0,'cinnamon_themes','',0,1,0,1,'2016-03-04 05:49:24','2018-02-19 10:35:07',NULL),(134,118,119,'Gnome Shell Themes',1,0,'gnome_shell_themes','GNOME Shell Theme',0,1,0,1,'2016-03-04 05:49:32','2018-02-19 10:35:07',NULL),(135,116,117,'GTK3 Themes',1,0,'gtk3_themes','GTK 3.x Theme/Style',0,1,0,1,'2016-03-04 05:49:38','2018-02-19 10:35:07',NULL),(136,114,115,'GTK2 Themes',1,0,'gtk2_themes','GTK 2.x Theme/Style',0,1,0,1,'2016-03-04 05:49:44','2018-02-19 10:35:07',NULL),(137,638,639,'XFCE Icon Themes',0,1,'icons',NULL,0,1,0,1,'2016-03-04 05:49:51','2018-03-14 12:24:09','2016-07-05 05:45:19'),(138,325,326,'XFCE/XFWM4 Themes',1,0,'xfwm4_themes','',0,1,0,1,'2016-03-04 05:49:58','2018-03-14 12:24:09',NULL),(139,343,344,'Fluxbox Themes',1,0,'fluxbox_styles',NULL,0,1,0,1,'2016-03-04 05:50:06','2018-03-14 12:24:09',NULL),(140,341,342,'Openbox Themes',1,0,'openbox_themes',NULL,0,1,0,1,'2016-03-04 05:50:13','2018-03-14 12:24:09',NULL),(141,339,340,'Pek-WM Themes',1,0,'pekwm_themes',NULL,0,1,0,1,'2016-03-04 05:50:20','2018-03-14 12:24:09',NULL),(142,337,338,'Ice-WM Themes',1,0,'icewm_themes',NULL,0,1,0,1,'2016-03-04 05:50:27','2018-03-14 12:24:09',NULL),(143,335,336,'FVWM Themes',1,0,NULL,NULL,0,1,0,1,'2016-03-04 05:50:38','2018-03-14 12:24:09',NULL),(144,345,346,'Window-Maker Themes',1,0,NULL,NULL,0,1,0,1,'2016-03-04 05:50:45','2018-03-14 12:24:09',NULL),(145,104,105,'Enlightenment Themes',1,0,'enlightenment_themes','',0,1,0,1,'2016-03-04 05:50:53','2018-02-19 10:35:07',NULL),(146,294,307,'Login Managers',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(147,308,349,'Window Managers',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(148,79,364,'Linux/Unix Desktops',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(149,365,370,'Desktop Extensions',0,1,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09','2017-01-14 16:24:56'),(150,635,640,'Icons/Cursors/Emoticons',0,1,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09','2017-01-14 16:07:45'),(151,268,291,'Boot and Splashscreens',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(152,371,634,'App Addons',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(153,297,298,'MDM Themes',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(154,295,296,'LightDM Themes',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(155,188,189,'Plasma Comic Sources',1,0,'','Plasma Comic',0,1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(156,200,201,'Gnome Extensions',1,0,'gnome_shell_extensions','',0,1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(157,190,191,'Various KDE 1.-4. Improvements',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(158,681,700,'Art (Images/Drawings/Illustrations)',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(159,686,687,'3D Renderings',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(160,688,689,'Stock Images',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(161,690,691,'Animations',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(162,692,693,'Drawings',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(163,423,424,'Krita Color Profiles',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(164,425,426,'Krita Templates',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(165,427,428,'Krita Resource Bundles',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(166,106,107,'E Entrance Themes',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(167,100,101,'E Enlightenment Backgrounds',1,0,'enlightenment_backgrounds','',0,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(168,98,99,'E Animated Backgrounds',1,0,'enlightenment_backgrounds','',0,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(169,347,348,'KDE 3.x Window Decorations',1,0,'',NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(170,312,313,'Skydomes',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(171,310,311,'Cubecaps',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(172,90,91,'KDE 3.5 Themes',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(173,102,103,'E Modules',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(174,684,685,'Cliparts',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(175,682,683,'Paintings',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(176,422,433,'Krita',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(177,245,246,'Kbfx Startmenu',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(178,151,152,'Karamba & Superkaramba',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(179,510,511,'Amarok Themes',1,0,'','Amarok Theme',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(180,561,562,'K3b Themes',1,0,'','K3b Theme',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(181,305,306,'KDM3 Themes',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(182,694,695,'Various Artwork',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(183,383,384,'Knights Themes',1,0,'','KDE Knights Theme',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(184,545,546,'Yakuake Skins',1,0,'yakuake_skins','Yakuake Skin',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(185,476,477,'Kdenlive FX',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(186,446,457,'LibreOffice/OpenOffice',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(187,449,450,'ODF Text',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(188,451,452,'ODF Spreadsheet',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(189,453,454,'ODF Presentation',1,0,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(190,434,445,'Gimp',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(191,435,436,'Gimp Brushes',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(192,437,438,'Gimp Patterns',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(193,439,440,'Gimp Palettes',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(194,441,442,'Gimp Splashes',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(195,460,461,'Inkscape',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(196,458,459,'Scribus',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(197,357,358,'Logos',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(198,464,465,'SMPlayer/MPlayer',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(199,355,356,'Individual Icons/-sets',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(200,120,121,'Gnome 2 Color Schemes',1,0,'gnome_color_schemes','',0,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(201,701,702,'Games',0,1,NULL,NULL,0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09','2016-06-03 10:09:24'),(202,715,716,'Arcade',1,0,'bin','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(203,713,714,'Board',1,0,'bin','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(204,711,712,'Card',1,0,'bin','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(205,709,710,'Tactics & Strategy',1,0,'bin','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(206,707,708,'Games Other',1,0,'bin','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(207,198,199,'Various Gnome Stuff',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(208,157,158,'Cairo Clock',1,0,'cairo_clock_themes','',0,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(209,322,323,'Kwin Effects',1,0,'kwin_effects','KWin Effects',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(210,320,321,'Kwin Scripts',1,0,'kwin_scripts','KWin Scripts',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(211,318,319,'Kwin Switching Layouts',1,0,'kwin_tabbox','KWin Switching Layouts',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(212,543,544,'Plasma Public Transport Timetables',1,0,'','Plasma public transport timetable',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(213,547,548,'KTextEditor Snippets',1,0,'','KTextEditor Snippet',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(214,559,560,'Okteta Structure Definitions',1,0,'','Okteta Structure Definition',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(215,402,403,'KPat Decks',1,0,'','KDE Card Deck',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(216,522,523,'Parley Vocabulary Files',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(217,557,558,'Marble Maps',1,0,'','Marble Map',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(218,381,382,'KWordQuiz',1,0,'','',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(219,555,556,'KTurtle Scripts',1,0,'','KTurtle Script',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(220,553,554,'KStars Data',1,0,'','KStars Data',0,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(221,279,280,'KDE 3.x Splash Screens',1,0,'','',0,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:25:13',NULL),(222,783,784,'Office',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(223,85,86,'KDE 2 Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(224,94,95,'KDE 3.0-3.4 Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(225,208,239,'Screenshots',1,0,'','',NULL,0.1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(226,209,210,'Unity Screenshots',1,0,'','',NULL,0.1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(227,211,212,'Cinnamon Screenshots',1,0,'','',NULL,0.1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(228,213,214,'Plasma/KDE Screenshots',1,0,'','',NULL,0.1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(229,551,552,'Kopete Styles',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(230,88,89,'KDE 3 Color Schemes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(231,266,267,'Screensavers',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-07 10:31:44',NULL),(232,466,467,'Noatun Skins',1,0,NULL,NULL,NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(233,703,804,'Apps',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(234,732,733,'Database',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(235,785,786,'Financial',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(236,787,788,'Groupware',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(237,769,770,'Audio',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(238,793,794,'Video',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(239,704,705,'Graphics',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(240,789,790,'Text Editors',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(241,751,752,'Education',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(242,743,744,'Telephony',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(243,734,735,'Development',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(244,736,737,'Utilities',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(245,738,739,'System Software',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(246,765,766,'Security',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(247,749,750,'Science',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(248,757,758,'Web',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(249,755,756,'Email',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(250,745,746,'Chat',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(251,763,764,'Network',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(252,729,730,'Qt Widgets',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(253,727,728,'Qt Components',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(254,725,726,'Qt Stuff',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(255,723,724,'Qt Mobile',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(256,215,216,'XFCE Screenshots',1,0,'','',NULL,0.1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(257,217,218,'Gnome Screenshots',1,0,'','',NULL,0.1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(258,219,220,'Window-Manager Screenshots',1,0,'','',NULL,0.1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(259,508,509,'Amarok 1.x Scripts',1,0,'','Amarok Script',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(260,506,507,'Amarok 2.x Scripts',1,0,'amarok_scripts','Amarok 2.0 Script',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(261,806,861,'OS specific',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(262,429,430,'Python Scripts',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(263,431,432,'Python Plugins',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(264,160,161,'Cinnamon Applets',1,0,'cinnamon_applets','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(265,164,165,'Cinnamon Extensions',1,0,'cinnamon_extensions','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(266,139,140,'Be-Shell/Bespin',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:45:13',NULL),(267,309,316,'Compiz',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(268,443,444,'Gimp Themes',1,0,NULL,NULL,NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(269,281,282,'Usplash Themes',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:25:05',NULL),(270,283,284,'XSplash Themes',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:47',NULL),(271,447,448,'LibreOffice Splash Screens',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(272,257,258,'Docky Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(273,249,250,'Plank Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(274,255,256,'Cairo-Dock Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(275,253,254,'AWN Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(276,251,252,'DockbarX Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(277,244,265,'Docks and Launchers',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-07 10:31:44',NULL),(278,285,286,'Splashy Themes',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:41',NULL),(279,575,576,'Gedit Color Schemes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(280,722,731,'Qt Other',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(281,468,469,'Thunderbird Themes',1,0,NULL,NULL,NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(282,470,471,'Chrome/Chromium',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(283,807,808,'Wallpapers Mint',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(284,112,113,'GTK1 Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(285,259,260,'Kicker Panel',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(286,809,810,'Wallpapers Ubuntu',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(287,811,812,'Wallpapers Kubuntu',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(288,813,814,'Wallpapers SUSE',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(289,815,816,'Wallpapers Arch',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(290,817,818,'Wallpapers Fedora',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(291,819,820,'Wallpapers Mandriva',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(292,821,822,'Wallpapers Gentoo',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(293,823,824,'Wallpapers Frugalware',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(294,97,108,'Enlightenment',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(295,805,888,'Wallpapers',1,0,NULL,NULL,NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(296,864,865,'Abstract',1,0,'wallpapers','KDE Wallpaper 1024x768',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(297,866,867,'Animals',1,0,'wallpapers','KDE Wallpaper 1280x1024',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(298,868,869,'Nature',1,0,'wallpapers','KDE Wallpaper 1440x900',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(299,825,826,'Wallpapers KDE Plasma',1,0,'wallpapers','KDE Wallpaper (other)',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(300,827,828,'Wallpapers Gnome',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(301,870,871,'People',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(302,829,830,'Wallpapers XFCE/Xubuntu',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(303,831,832,'Wallpapers Debian',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(304,872,873,'Buildings',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(305,874,875,'Landscapes',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(306,876,877,'Mountains',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(307,878,879,'Beaches and Oceans',1,0,'wallpapers','KDE Wallpaper 1600x1200',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(308,880,881,'Bridges',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(309,833,834,'Wallpapers Manjaro',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(310,835,836,'Wallpapers Firefox',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(311,837,838,'Wallpapers Windows',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(312,839,840,'Wallpapers OSX/Apple',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(313,882,883,'Manga and Anime',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(314,841,842,'Wallpapers BSD',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(315,92,93,'KDE 3 Domino Styles',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(316,242,243,'System Sounds',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(317,535,542,'Simon Speech',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(318,536,537,'Simon Base Models',1,0,'','Simon Base Models',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(319,538,539,'Simon Scenarios',1,0,'','Simon Scenario',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(320,540,541,'Simon Dictionaries',1,0,'','Simon Dictionaries',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(321,408,421,'VLC',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(322,411,412,'VLC Internet Channels',1,0,'','VLC Internet Channel',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(323,413,414,'VLC Extensions',1,0,'','VLC Extension',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(324,415,416,'VLC Playlist Parsers',1,0,'','VLC Playlist Parser',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(325,417,418,'VLC Plugins',1,0,'','VLC Plugin',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(326,419,420,'VLC Other',1,0,'','VLC other',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(327,500,501,'KDevelop File Templates',1,0,'','KDevelop File Template',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(328,502,503,'KDevelop App Templates',1,0,'','KDE App Template',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(329,49,50,'Java',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2017-09-01 14:55:06',NULL),(330,889,892,'Comics',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(331,893,894,'System Sounds',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09','2017-01-14 16:47:17'),(332,895,896,'Screensavers',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09','2017-01-14 16:09:44'),(333,475,484,'Kdenlive',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(334,478,479,'Kdenlive Export Profiles',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(335,480,481,'Kdenlive Title Templates',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(336,379,380,'Kanagram',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(337,377,378,'Khangman',1,0,'','KHangMan',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(338,518,519,'Skrooge Report Templates',1,0,'','Skrooge report template',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(339,516,517,'Skrooge Quote Sources',1,0,'','Skrooge quote source',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(340,505,512,'Amarok',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(341,515,520,'Skrooge',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(342,499,504,'KDevelop',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(343,485,498,'Kontact/PIM',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(344,486,487,'KOrganizer Calendars',1,0,'','KOrganizer Calendar',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(345,488,489,'KNotes Printing Themes',1,0,'','KNotes Printing Theme',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(346,490,491,'KMail Header Themes',1,0,'','KMail Header Theme',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(347,492,493,'KAdressbook Themes',1,0,'','KAdressbook Theme',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(348,494,495,'Script Sieve',1,0,'','Script Sieve',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(349,317,324,'KWin',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(350,843,844,'Wallpapers PCLinuxOS',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(351,375,376,'KAtomic Levels',1,0,'','KAtomic Level',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(352,373,374,'KPat',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09','2017-01-30 06:55:21'),(353,404,405,'KPat Themes',1,0,'','KDE KPat Theme',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(354,496,497,'Akonadi Email Providers',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(355,372,407,'KDE Game-Addons',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(356,521,526,'Parley',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(357,524,525,'Parley Themes',1,0,'','KDE Parley Theme',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(358,845,846,'Wallpapers Mageia',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(359,847,848,'Wallpapers MATE',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(360,849,850,'Wallpapers Linux/Tux',1,0,'wallpapers','KDE Wallpaper 800x600',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(361,851,852,'Wallpapers Solus',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(362,853,854,'Wallpapers GNU',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(363,109,110,'Cinnamon',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:19','2018-02-19 10:35:19'),(364,368,369,'Plasma Extensions',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09','2017-01-14 16:23:48'),(365,123,134,'KDE Plasma',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(366,111,122,'Gnome/GTK',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(367,696,697,'Fractals',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(368,472,473,'Telegram Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(369,130,131,'Plasma public transport timetables',0,1,'','Plasma public transport timetable',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07','2017-01-23 09:23:55'),(370,527,534,'Krusader',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(371,528,529,'Krusader Colormaps',1,0,'','Krusader colormap',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(372,530,531,'Krusader User Actions',1,0,'','Krusader user action',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(373,532,533,'Krusader JS Extensions',1,0,'','Krusader JS extension',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(374,855,856,'Wallpapers Zorin',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(375,513,514,'Krunner Plugins',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(376,740,741,'Social',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(377,155,156,'GKrellM',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(378,206,207,'Monitoring Tools',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53','2017-07-14 11:02:23'),(379,352,353,'Startmenus',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09','2017-05-02 03:09:54'),(380,474,573,'KDE App-Addons',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(381,80,147,'Desktop Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(382,574,579,'Gnome App-Addons',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(383,194,195,'Plasma 5 Extensions',0,1,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53','2018-02-09 09:16:29'),(384,148,205,'Desktop Extensions',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(385,87,96,'KDE 3 Themes',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-02-19 10:35:07',NULL),(386,354,363,'Icons',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(387,742,747,'Communication',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(388,748,753,'Science & Education',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(389,754,759,'Web & Email',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(390,760,761,'Graphics & Video',0,1,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09','2017-06-10 12:59:26'),(391,762,767,'Network & Security',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(392,768,781,'Audio',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(393,771,772,'Audioplayers',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(394,773,774,'Music Production',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(395,775,776,'Radio',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(396,777,778,'MP3 Taggers',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(397,779,780,'Audio Extractors/Converters',1,0,'bin','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(398,173,174,'Plasma 5 Menus',1,0,'plasma5_plasmoids','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(399,175,176,'Plasma 5 Clocks',1,0,'plasma5_plasmoids','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-06 11:43:53',NULL),(400,857,858,'Wallpapers LXQt/LXDE',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(401,884,885,'Cars',1,0,'wallpapers','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(402,580,581,'Various Scripts and Stuff',1,0,'','',NULL,1,0,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(403,897,898,'Tutorials',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(404,899,900,'Distros',1,0,'','',NULL,0.1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(405,563,564,'Konversation Nicklist Themes',1,0,'','',NULL,1,1,1,'2018-05-24 17:29:28','2018-03-14 12:24:09',NULL),(406,385,386,'KBlocks Themes',1,0,'','',NULL,1,0,1,'2017-08-22 09:34:24','2018-03-14 12:24:09',NULL),(407,387,388,'KDiamonds Themes',1,0,'','',NULL,1,0,1,'2017-08-22 09:35:14','2018-03-14 12:24:09',NULL),(408,389,390,'KGoldrunner Themes',1,0,'','',NULL,1,0,1,'2017-08-22 09:35:48','2018-03-14 12:24:09',NULL),(409,391,392,'Kigo Games',1,0,'','',NULL,1,0,1,'2017-08-22 09:36:11','2018-03-14 12:24:09',NULL),(410,393,394,'Kigo Themes',1,0,'','',NULL,1,0,1,'2017-08-22 09:36:38','2018-03-14 12:24:09',NULL),(411,395,396,'KSirk Themes',1,0,'','',NULL,1,0,1,'2017-08-22 09:37:09','2018-03-14 12:24:09',NULL),(412,397,398,'KSnakeDuel Themes',1,0,'','',NULL,1,0,1,'2017-08-22 09:37:30','2018-03-14 12:24:09',NULL),(413,399,400,'KSudoku Games',1,0,'','',NULL,1,0,1,'2017-08-22 09:38:17','2018-03-14 12:24:09',NULL),(414,159,166,'Cinnamon Extensions',1,0,'','',NULL,1,0,1,'2017-09-01 14:58:46','2018-03-06 11:43:53',NULL),(415,582,583,'Mycroft Skills',1,0,'','',NULL,1,0,1,'2017-10-02 06:27:44','2018-03-14 12:24:09',NULL),(416,565,566,'System Monitor Tabs',1,0,'','',NULL,1,1,1,'2017-10-25 03:23:11','2018-03-14 12:24:09',NULL),(417,261,262,'Latte Layouts',1,0,'','',NULL,1,0,1,'2017-12-23 15:56:31','2018-03-06 11:43:53',NULL),(418,170,185,'Plasma 5 Widgets',1,0,'','Plasma 5 Plasmoid',NULL,1,1,1,'2018-01-03 15:38:16','2018-03-06 11:43:53',NULL),(419,186,187,'Plasma Wallpaper Plugins',1,0,'','Plasma Wallpaper Plugin',NULL,1,1,1,'2018-01-03 15:46:07','2018-03-06 11:43:53',NULL),(420,177,178,'Plasma 5 Multimedia',1,0,'plasma5_plasmoids','',NULL,1,1,1,'2018-01-03 18:46:40','2018-03-06 11:43:53',NULL),(421,132,133,'Various Plasma Styles',1,0,'','',NULL,1,1,1,'2018-01-03 22:29:21','2018-02-19 10:35:07',NULL),(422,168,169,'Various Plasma 5 Improvements',1,0,'','',NULL,1,1,1,'2018-01-03 23:02:32','2018-03-06 11:43:53',NULL),(423,167,196,'KDE Plasma Extensions',1,0,'','',NULL,1,0,1,'2018-01-13 14:59:26','2018-03-06 11:43:53',NULL),(424,179,180,'Plasma 5 Weather',1,0,'plasma5_plasmoids','',NULL,1,1,1,'2018-01-13 15:25:22','2018-03-06 11:43:53',NULL),(425,181,182,'Plasma 5 Monitoring',1,0,'plasma5_plasmoids','',NULL,1,1,1,'2018-01-13 15:26:04','2018-03-06 11:43:53',NULL),(426,482,483,'Kdenlive Keyboard Schemes',1,0,'','',NULL,1,0,1,'2018-01-17 04:14:28','2018-03-14 12:24:09',NULL),(427,584,617,'SubSpace Continuum',1,0,'','',NULL,1,1,1,'2018-01-25 04:55:30','2018-03-14 12:24:09',NULL),(428,587,588,'Audio/Visuals',1,0,'','',NULL,1,1,1,'2018-01-25 04:56:19','2018-03-14 12:24:09',NULL),(429,589,590,'Banners',1,0,'','',NULL,1,1,1,'2018-01-25 04:56:54','2018-03-14 12:24:09',NULL),(430,591,592,'Bots',1,0,'','',NULL,1,0,1,'2018-01-25 04:58:26','2018-03-14 12:24:09',NULL),(431,593,594,'Catids',1,0,'','',NULL,1,0,1,'2018-01-25 04:58:43','2018-03-14 12:24:09',NULL),(432,595,596,'Clients',1,0,'','',NULL,1,0,1,'2018-01-25 04:58:56','2018-03-14 12:24:09',NULL),(433,597,598,'Editors',1,0,'','',NULL,1,0,1,'2018-01-25 04:59:19','2018-03-14 12:24:09',NULL),(434,599,600,'Fonts',1,0,'','',NULL,1,0,1,'2018-01-25 04:59:36','2018-03-14 12:24:09',NULL),(435,601,602,'Graphics',1,0,'','',NULL,1,0,1,'2018-01-25 04:59:56','2018-03-14 12:24:09',NULL),(436,603,604,'Images',1,0,'','',NULL,1,0,1,'2018-01-25 05:00:29','2018-03-14 12:24:09',NULL),(437,605,606,'Misc',1,0,'','',NULL,1,0,1,'2018-01-25 05:00:45','2018-03-14 12:24:09',NULL),(438,607,608,'Mods',1,0,'','',NULL,1,0,1,'2018-01-25 05:00:53','2018-03-14 12:24:09',NULL),(439,609,610,'Server',1,0,'','',NULL,1,0,1,'2018-01-25 05:01:07','2018-03-14 12:24:09',NULL),(440,611,612,'Skins',1,0,'','',NULL,1,0,1,'2018-01-25 05:01:18','2018-03-14 12:24:09',NULL),(441,585,586,'Sounds',1,0,'','',NULL,1,0,1,'2018-01-25 05:01:35','2018-03-14 12:24:09',NULL),(442,613,614,'SubspaceISO',1,0,'','',NULL,1,0,1,'2018-01-25 05:02:25','2018-03-14 12:24:09',NULL),(443,615,616,'Zones',1,0,'','',NULL,1,0,1,'2018-01-25 05:02:37','2018-03-14 12:24:09',NULL),(444,886,887,'Mobile Phones',1,0,'wallpapers','',NULL,1,0,1,'2018-01-26 06:15:43','2018-03-14 12:24:09',NULL),(445,901,902,'UI Concepts',1,0,'','',NULL,1,1,1,'2018-01-27 06:57:35','2018-03-14 12:24:09',NULL),(446,141,142,'LXQt Themes',1,0,'lxqt_themes','',NULL,1,0,1,'2018-02-05 10:45:08','2018-02-19 10:35:07',NULL),(447,143,144,'LXQt Themes',0,1,'','',NULL,1,0,1,'2018-02-05 12:53:59','2018-02-19 10:35:07','2018-02-05 12:54:17'),(448,618,619,'GMusicbrowser Layouts',1,0,'','',NULL,1,1,1,'2018-02-10 04:50:26','2018-03-14 12:24:09',NULL),(449,620,621,'Pidgin',1,0,'','',NULL,1,0,1,'2018-02-10 05:02:32','2018-03-14 12:24:09',NULL),(450,782,791,'Productivity',1,0,'','',NULL,1,1,1,'2018-02-11 16:46:31','2018-03-14 12:24:09',NULL),(451,859,860,'Wallpapers Deepin',1,0,'wallpapers','',NULL,1,0,1,'2018-02-13 04:09:23','2018-03-14 12:24:09',NULL),(452,221,222,'Mate Screenshots',1,0,'','',NULL,0.1,1,1,'2018-02-13 10:35:15','2018-03-06 11:43:53',NULL),(453,223,224,'Deepin Screenshots',1,0,'','',NULL,0.1,1,1,'2018-02-13 10:38:20','2018-03-06 11:43:53',NULL),(454,225,226,'Budgie Screenshots',1,0,'','',NULL,0.1,1,1,'2018-02-13 10:38:35','2018-03-06 11:43:53',NULL),(455,227,228,'Fluxbox Screenshots',1,0,'','',NULL,0.1,1,1,'2018-02-13 10:50:13','2018-03-06 11:43:53',NULL),(456,229,230,'LXDE/LXQt Screenshots',1,0,'','',NULL,0.1,1,1,'2018-02-13 10:51:28','2018-03-06 11:43:53',NULL),(457,231,232,'IceWM Screenshots',1,0,'','',NULL,0.1,1,1,'2018-02-13 10:52:13','2018-03-06 11:43:53',NULL),(458,233,234,'Enlightenment Screenshots',1,0,'','',NULL,0.1,1,1,'2018-02-13 11:07:08','2018-03-06 11:43:53',NULL),(459,235,236,'Elementary Screenshots',1,0,'','',NULL,0.1,1,1,'2018-02-13 11:17:53','2018-03-06 11:43:53',NULL),(460,197,202,'Gnome',1,0,'','',NULL,1,1,1,'2018-02-19 09:25:53','2018-03-06 11:43:53',NULL),(461,237,238,'Openbox Screenshots',1,0,'','',NULL,0.1,1,1,'2018-02-19 10:27:40','2018-03-06 11:43:53',NULL),(462,567,568,'Konsole Color Schemes',1,0,'','',NULL,1,1,1,'2018-02-23 05:48:14','2018-03-14 12:24:09',NULL),(463,183,184,'Plasma 5 Calendars',1,0,'plasma5_plasmoids','',NULL,1,1,1,'2018-02-27 11:58:54','2018-03-06 11:43:53',NULL),(464,149,150,'Various Stuff',1,0,'','',NULL,1,1,1,'2018-03-06 04:43:12','2018-03-06 11:43:53',NULL),(465,203,204,'Amor Themes',1,0,'','',NULL,1,1,1,'2018-03-06 06:21:28','2018-03-06 11:43:53',NULL),(466,903,904,'Telephone UI',1,0,'','',NULL,1,1,1,'2018-03-06 08:35:16','2018-03-14 12:24:09',NULL),(467,622,623,'Kirocker',1,0,'','',NULL,1,1,1,'2018-03-06 10:39:05','2018-03-14 12:24:09',NULL),(468,698,699,'CD/DVD Labels',1,0,'','',NULL,1,0,1,'2018-03-06 10:57:28','2018-03-14 12:24:09',NULL),(469,624,625,'Mixxx Skins',1,0,'','',NULL,1,1,1,'2018-03-06 11:00:54','2018-03-14 12:24:09',NULL),(470,145,146,'Be-Shell',0,1,'','',NULL,1,0,1,'2018-03-06 11:43:53','2018-03-06 11:44:56','2018-03-06 11:44:56'),(471,401,406,'KPatience',1,0,'','',NULL,1,1,1,'2018-03-06 12:09:57','2018-03-14 12:24:09',NULL),(472,569,570,'Kate',1,0,'','Kate Highlighting',NULL,1,1,1,'2018-03-07 10:13:43','2018-03-14 12:24:09',NULL),(473,626,627,'aMSN',1,0,'','',NULL,1,1,1,'2018-03-07 10:17:09','2018-03-14 12:24:09',NULL),(474,628,629,'Opera',1,0,'','',NULL,1,1,1,'2018-03-07 10:19:56','2018-03-14 12:24:09',NULL),(475,263,264,'KXDocker',1,0,'','',NULL,1,0,1,'2018-03-07 10:31:44',NULL,NULL),(476,455,456,'OpenOffice Splash Screens',1,0,'','',NULL,1,0,1,'2018-03-07 10:32:58','2018-03-14 12:24:09',NULL),(477,571,572,'Digikam',1,0,'','',NULL,1,1,1,'2018-03-07 10:39:20','2018-03-14 12:24:09',NULL),(478,630,631,'VIM',1,0,'','',NULL,1,1,1,'2018-03-07 10:40:34','2018-03-14 12:24:09',NULL),(479,632,633,'Covergloobus',1,0,'','',NULL,1,1,1,'2018-03-07 10:48:40','2018-03-14 12:24:09',NULL),(480,287,288,'GFXBoot',1,0,'','',NULL,1,1,1,'2018-03-07 10:52:25','2018-03-14 12:24:34',NULL),(481,792,803,'Video',1,0,'','',NULL,1,1,1,'2018-03-12 18:42:25','2018-03-14 12:24:09',NULL),(482,795,796,'Video Players',1,0,'bin','',NULL,1,1,1,'2018-03-12 18:43:08','2018-03-14 12:24:09',NULL),(483,797,798,'Video Production',1,0,'bin','',NULL,1,1,1,'2018-03-12 18:43:27','2018-03-14 12:24:09',NULL),(484,799,800,'Video Converter',1,0,'bin','',NULL,1,1,1,'2018-03-12 18:49:40','2018-03-14 12:24:09',NULL),(485,801,802,'TV & Streaming',1,0,'bin','',NULL,1,1,1,'2018-03-12 19:05:08','2018-03-14 12:24:09',NULL),(486,289,290,'Bootsplash Various',1,0,'','',NULL,1,1,1,'2018-03-14 12:24:09','2018-03-14 12:24:27',NULL);
/*!40000 ALTER TABLE `project_category` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `project_category_BEFORE_INSERT` BEFORE INSERT ON `project_category` FOR EACH ROW
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `project_category_BEFORE_UPDATE` BEFORE UPDATE ON `project_category` FOR EACH ROW
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_cc_license`
--

LOCK TABLES `project_cc_license` WRITE;
/*!40000 ALTER TABLE `project_cc_license` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_cc_license` ENABLE KEYS */;
UNLOCK TABLES;

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
  KEY `idxReport` (`project_id`,`member_id`,`is_deleted`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_clone`
--

LOCK TABLES `project_clone` WRITE;
/*!40000 ALTER TABLE `project_clone` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_clone` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_follower`
--

LOCK TABLES `project_follower` WRITE;
/*!40000 ALTER TABLE `project_follower` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_follower` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `project_gallery_picture`
--

LOCK TABLES `project_gallery_picture` WRITE;
/*!40000 ALTER TABLE `project_gallery_picture` DISABLE KEYS */;
INSERT INTO `project_gallery_picture` VALUES (1209922,1,'c/2/f/3/6e9c2479b3df3962dc2ed0d32538f3312090.png'),(1209923,1,'b/d/7/2/0728b9d9dc4b7d1bcb8bcfa7e7b9e14eb9c4.png'),(1209924,1,'2/a/a/9/3c23c0f8fc2cacec64ca508671baef53713b.jpg'),(1209925,1,'4/d/1/1/410ab1ceeb90798110f09972caf319098eb2.jpg'),(1209926,1,'7/1/1/d/dd5d40f3e3143a5a9209d1fba0223537b843.png'),(1209927,1,'7/1/7/c/46a34e4911e5bca0d0d6e333f8d744315a01.png'),(1209928,1,'2/f/5/0/f01ec1128ca82ab57badcdc27b15c7a6b358.png'),(1209930,1,'1/d/c/f/259d5bb1c4d767310ae4ddb2aaa10217cfc4.png'),(1209931,1,'3/b/0/f/86e909ed985d17b9e57227f3c7fe884bbac5.png'),(1209932,1,'f/d/f/9/dc634d9fa369a564365f5709fe0dce180cf9.png'),(1209933,1,'9/a/5/4/6ab11d1cec21d5f2113d242e404385561577.png'),(1209934,1,'a/d/e/3/26a121cf17a6c5bfab86a0b04c933b1fb87e.png');
/*!40000 ALTER TABLE `project_gallery_picture` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `project_license`
--

LOCK TABLES `project_license` WRITE;
/*!40000 ALTER TABLE `project_license` DISABLE KEYS */;
INSERT INTO `project_license` VALUES (0,'Other',NULL),(1,'GPLv2 or later',NULL),(2,'LGPL',NULL),(3,'Artistic 2.0',NULL),(4,'X11',NULL),(5,'QPL',NULL),(6,'BSD',NULL),(7,'Proprietary License',NULL),(8,'GFDL',NULL),(9,'CPL 1.0',NULL),(10,'Creative Commons by',NULL),(11,'Creative Commons by-sa',NULL),(12,'Creative Commons by-nd',NULL),(13,'Creative Commons by-nc',NULL),(14,'Creative Commons by-nc-sa',NULL),(15,'Creative Commons by-nc-nd',NULL),(16,'AGPL',NULL),(17,'CC0 1.0 Universal (Public Domain)',NULL),(18,'GPLv2 only',NULL),(19,'GPLv3',NULL);
/*!40000 ALTER TABLE `project_license` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `project_package_type`
--

LOCK TABLES `project_package_type` WRITE;
/*!40000 ALTER TABLE `project_package_type` DISABLE KEYS */;
INSERT INTO `project_package_type` VALUES (1170226,1494193875,1),(1150344,1485972156,2),(1180374,1496992787,2),(1193365,1506694307,2),(1170170,1486401592,6),(1170226,1486475516,6),(1170226,1494178980,7),(1170226,1494252412,7);
/*!40000 ALTER TABLE `project_package_type` ENABLE KEYS */;
UNLOCK TABLES;

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
  PRIMARY KEY (`project_plings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_plings`
--

LOCK TABLES `project_plings` WRITE;
/*!40000 ALTER TABLE `project_plings` DISABLE KEYS */;
INSERT INTO `project_plings` VALUES (1,1209922,497632,'2018-05-28 16:08:45',1,NULL,0,'0000-00-00 00:00:00'),(2,1209923,497632,'2018-05-28 17:12:10',1,NULL,0,'0000-00-00 00:00:00'),(3,1209924,497632,'2018-05-28 17:15:43',1,NULL,0,'0000-00-00 00:00:00'),(4,1209925,497632,'2018-05-28 17:17:37',1,NULL,0,'0000-00-00 00:00:00'),(5,1209926,497632,'2018-05-28 17:19:41',1,NULL,0,'0000-00-00 00:00:00'),(6,1209927,497632,'2018-05-28 17:21:57',1,NULL,0,'0000-00-00 00:00:00'),(7,1209928,497632,'2018-05-28 17:23:57',1,NULL,0,'0000-00-00 00:00:00'),(8,1209929,497632,'2018-05-28 17:25:13',1,NULL,0,'0000-00-00 00:00:00'),(9,1209930,497632,'2018-05-28 17:30:29',1,NULL,0,'0000-00-00 00:00:00'),(10,1209931,497632,'2018-05-28 17:32:03',1,NULL,0,'0000-00-00 00:00:00'),(11,1209932,497632,'2018-05-28 17:34:34',1,NULL,0,'0000-00-00 00:00:00'),(12,1209933,497632,'2018-05-28 17:37:41',1,NULL,0,'0000-00-00 00:00:00'),(13,1209934,497632,'2018-05-28 17:39:31',1,NULL,0,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `project_plings` ENABLE KEYS */;
UNLOCK TABLES;

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
  KEY `idx_member_id` (`member_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_rating`
--

LOCK TABLES `project_rating` WRITE;
/*!40000 ALTER TABLE `project_rating` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_rating` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=42181 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_updates`
--

LOCK TABLES `project_updates` WRITE;
/*!40000 ALTER TABLE `project_updates` DISABLE KEYS */;
INSERT INTO `project_updates` VALUES (42180,1193365,24,0,'test','- SliderEditor has been converted to bootstrap 3 carousel because of license issues.<br />\n- News will be displayed on the dashboard now.<br />\n- Additional files and folders under &lt;project&gt;/content/* will also be copied to the deploy directory while building the site.<br />\n    This makes it possible to deploy for example robots.txt, google&lt;id&gt;.html and other files.<br />\n- Added a warning when SiteWizard will override an already existing site<br />\n- The install directory can now be choosen prior to install FlatSiteBuilder','2017-11-10 10:10:37','2017-11-10 11:10:31',0,NULL),(42179,1193365,24,1,'test','- SliderEditor has been converted to bootstrap 3 carousel because of license issues.\n- News will be displayed on the dashboard now.\n- Additional files and folders under <project>/content/* will also be copied to the deploy directory while building the site.\n    This makes it possible to deploy for example robots.txt, google<id>.html and other files.\n- Added a warning when SiteWizard will override an already existing site\n- The install directory can now be choosen prior to install FlatSiteBuilder\n- lalalalalalalalala','2017-11-10 10:09:54','2017-11-10 11:10:23',0,NULL),(41418,1175480,24,1,'1.0.0','MyCollection:\n* able to easily keep track of and remove installed items\n* directly \"Apply\" wallpapers on any Linux distro from within MyCollection\n','2017-05-27 07:29:36','2017-06-09 12:23:43',0,NULL),(40656,1150344,24,1,'mmmnnn','mmmnnn','2016-12-22 04:24:11','2016-12-22 04:24:29',0,NULL),(40655,1150344,24,1,'lll','lll','2016-12-22 04:24:03','2016-12-22 04:24:03',0,NULL),(40653,1099990,24,1,'hhh','hhh','2016-12-22 04:17:56','2016-12-22 04:17:56',0,NULL),(40652,1150344,24,1,'kkk','kkk','2016-12-22 04:17:44','2016-12-22 04:20:45',0,NULL),(40651,1150344,24,0,'aaaaaa','aaaaaa','2016-12-22 04:16:49','2016-12-22 04:16:55',0,NULL),(40650,1150344,24,0,'bbb','bbb','2016-12-22 04:15:47','2016-12-22 04:15:55',0,NULL),(40649,1150344,24,0,'aaa','aaa','2016-12-22 04:15:43','2016-12-22 04:15:56',0,NULL);
/*!40000 ALTER TABLE `project_updates` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `queue`
--

LOCK TABLES `queue` WRITE;
/*!40000 ALTER TABLE `queue` DISABLE KEYS */;
INSERT INTO `queue` VALUES (1,'website_validate',30),(2,'search',30),(3,'ocs_jobs',30);
/*!40000 ALTER TABLE `queue` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue_message`
--

LOCK TABLES `queue_message` WRITE;
/*!40000 ALTER TABLE `queue_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue_message` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports_comment`
--

LOCK TABLES `reports_comment` WRITE;
/*!40000 ALTER TABLE `reports_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports_comment` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `report_comment_created` BEFORE INSERT ON `reports_comment` FOR EACH ROW
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

--
-- Dumping data for table `reports_member`
--

LOCK TABLES `reports_member` WRITE;
/*!40000 ALTER TABLE `reports_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports_member` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `reports_member_created` BEFORE INSERT ON `reports_member` FOR EACH ROW
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
  KEY `idxReport` (`project_id`,`reported_by`,`is_deleted`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports_project`
--

LOCK TABLES `reports_project` WRITE;
/*!40000 ALTER TABLE `reports_project` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports_project` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `report_project_created` BEFORE INSERT ON `reports_project` FOR EACH ROW
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session`
--

LOCK TABLES `session` WRITE;
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
INSERT INTO `session` VALUES (1,24,'01f16d5a81424ed697afcffa3512dc07','2019-05-28 19:10:43','2018-05-28 15:13:32','2018-05-28 17:10:43');
/*!40000 ALTER TABLE `session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_cat_prod_count`
--

DROP TABLE IF EXISTS `stat_cat_prod_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_cat_prod_count` (
  `project_category_id` int(11) NOT NULL,
  `package_type_id` int(11) DEFAULT NULL,
  `count_product` int(11) DEFAULT NULL,
  KEY `idx_package` (`project_category_id`,`package_type_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stat_cat_prod_count`
--

LOCK TABLES `stat_cat_prod_count` WRITE;
/*!40000 ALTER TABLE `stat_cat_prod_count` DISABLE KEYS */;
INSERT INTO `stat_cat_prod_count` VALUES (6,NULL,2),(34,NULL,15),(35,NULL,0),(36,NULL,0),(37,NULL,0),(38,NULL,0),(39,NULL,0),(40,NULL,0),(41,NULL,0),(42,NULL,0),(43,NULL,0),(44,NULL,0),(45,NULL,0),(46,NULL,0),(48,NULL,0),(52,NULL,0),(53,NULL,0),(54,NULL,0),(55,NULL,0),(56,NULL,0),(57,NULL,0),(58,NULL,1),(79,NULL,0),(81,NULL,0),(88,NULL,0),(89,NULL,0),(90,NULL,0),(91,NULL,0),(92,NULL,0),(93,NULL,0),(94,NULL,0),(95,NULL,0),(96,NULL,0),(98,NULL,0),(100,NULL,1),(101,NULL,0),(102,NULL,0),(103,NULL,0),(104,NULL,1),(105,NULL,0),(106,NULL,0),(107,NULL,0),(108,NULL,0),(109,NULL,0),(111,NULL,0),(112,NULL,0),(113,NULL,0),(114,NULL,0),(116,NULL,0),(117,NULL,0),(118,NULL,0),(119,NULL,0),(120,NULL,0),(121,NULL,0),(123,NULL,0),(124,NULL,0),(125,NULL,0),(126,NULL,0),(127,NULL,0),(128,NULL,0),(129,NULL,0),(130,NULL,0),(131,NULL,0),(132,NULL,1),(133,NULL,0),(134,NULL,0),(135,NULL,1),(136,NULL,0),(138,NULL,0),(139,NULL,1),(140,NULL,1),(141,NULL,0),(142,NULL,0),(143,NULL,0),(144,NULL,0),(145,NULL,0),(146,NULL,1),(147,NULL,2),(148,NULL,6),(151,NULL,0),(152,NULL,1),(153,NULL,0),(154,NULL,0),(155,NULL,0),(156,NULL,0),(157,NULL,0),(158,NULL,1),(159,NULL,0),(160,NULL,0),(161,NULL,0),(162,NULL,0),(163,NULL,0),(164,NULL,0),(165,NULL,0),(166,NULL,0),(167,NULL,0),(168,NULL,0),(169,NULL,0),(170,NULL,0),(171,NULL,0),(172,NULL,0),(173,NULL,0),(174,NULL,1),(175,NULL,0),(176,NULL,0),(177,NULL,0),(178,NULL,0),(179,NULL,0),(180,NULL,0),(181,NULL,0),(182,NULL,0),(183,NULL,0),(184,NULL,0),(185,NULL,0),(186,NULL,0),(187,NULL,0),(188,NULL,0),(189,NULL,0),(190,NULL,0),(191,NULL,0),(192,NULL,0),(193,NULL,0),(194,NULL,0),(195,NULL,0),(196,NULL,0),(197,NULL,0),(198,NULL,0),(199,NULL,0),(200,NULL,0),(202,NULL,0),(203,NULL,1),(204,NULL,0),(205,NULL,1),(206,NULL,0),(207,NULL,0),(208,NULL,0),(209,NULL,0),(210,NULL,0),(211,NULL,0),(212,NULL,0),(213,NULL,0),(214,NULL,0),(215,NULL,0),(216,NULL,0),(217,NULL,0),(218,NULL,0),(219,NULL,0),(220,NULL,0),(221,NULL,0),(222,NULL,0),(223,NULL,0),(224,NULL,0),(225,NULL,0),(226,NULL,0),(227,NULL,0),(228,NULL,0),(229,NULL,0),(230,NULL,0),(231,NULL,0),(232,NULL,0),(233,NULL,3),(234,NULL,0),(235,NULL,0),(236,NULL,0),(237,NULL,0),(238,NULL,0),(239,NULL,0),(240,NULL,0),(241,NULL,0),(242,NULL,0),(243,NULL,0),(244,NULL,0),(245,NULL,0),(246,NULL,0),(247,NULL,0),(248,NULL,0),(249,NULL,0),(250,NULL,0),(251,NULL,0),(252,NULL,0),(253,NULL,0),(254,NULL,0),(255,NULL,0),(256,NULL,0),(257,NULL,0),(258,NULL,0),(259,NULL,0),(260,NULL,0),(261,NULL,0),(264,NULL,0),(265,NULL,0),(266,NULL,0),(267,NULL,0),(268,NULL,0),(269,NULL,0),(270,NULL,0),(271,NULL,0),(272,NULL,0),(273,NULL,0),(274,NULL,0),(275,NULL,0),(276,NULL,0),(277,NULL,0),(278,NULL,0),(279,NULL,0),(280,NULL,0),(281,NULL,0),(282,NULL,0),(283,NULL,0),(284,NULL,0),(285,NULL,0),(286,NULL,0),(287,NULL,0),(288,NULL,0),(289,NULL,0),(290,NULL,0),(291,NULL,0),(292,NULL,0),(293,NULL,0),(294,NULL,0),(295,NULL,1),(296,NULL,0),(297,NULL,0),(298,NULL,0),(299,NULL,0),(300,NULL,0),(301,NULL,0),(302,NULL,0),(303,NULL,0),(304,NULL,0),(305,NULL,0),(306,NULL,0),(307,NULL,0),(308,NULL,0),(309,NULL,0),(310,NULL,0),(311,NULL,0),(312,NULL,0),(313,NULL,0),(314,NULL,0),(315,NULL,0),(316,NULL,0),(317,NULL,0),(318,NULL,0),(319,NULL,0),(320,NULL,0),(321,NULL,1),(322,NULL,0),(323,NULL,0),(324,NULL,1),(325,NULL,0),(326,NULL,0),(327,NULL,0),(328,NULL,0),(330,NULL,0),(333,NULL,0),(334,NULL,0),(335,NULL,0),(336,NULL,0),(337,NULL,0),(338,NULL,0),(339,NULL,0),(340,NULL,0),(341,NULL,0),(342,NULL,0),(343,NULL,0),(344,NULL,0),(345,NULL,0),(346,NULL,0),(347,NULL,0),(348,NULL,0),(349,NULL,0),(350,NULL,0),(351,NULL,0),(353,NULL,0),(354,NULL,0),(355,NULL,0),(356,NULL,0),(357,NULL,0),(358,NULL,0),(359,NULL,0),(360,NULL,0),(361,NULL,0),(362,NULL,0),(365,NULL,1),(366,NULL,1),(367,NULL,0),(368,NULL,0),(370,NULL,0),(371,NULL,0),(372,NULL,0),(373,NULL,0),(374,NULL,0),(375,NULL,0),(376,NULL,0),(377,NULL,0),(380,NULL,0),(381,NULL,2),(382,NULL,0),(384,NULL,0),(385,NULL,0),(386,NULL,1),(387,NULL,0),(388,NULL,0),(389,NULL,0),(391,NULL,0),(392,NULL,0),(393,NULL,0),(394,NULL,0),(395,NULL,0),(396,NULL,0),(397,NULL,0),(398,NULL,0),(399,NULL,0),(400,NULL,0),(401,NULL,0),(402,NULL,0),(403,NULL,0),(404,NULL,1),(405,NULL,0),(406,NULL,0),(407,NULL,0),(408,NULL,0),(409,NULL,0),(410,NULL,0),(411,NULL,0),(412,NULL,0),(413,NULL,0),(414,NULL,0),(415,NULL,0),(416,NULL,0),(417,NULL,0),(418,NULL,0),(419,NULL,0),(420,NULL,0),(421,NULL,0),(422,NULL,0),(423,NULL,0),(424,NULL,0),(425,NULL,0),(426,NULL,0),(427,NULL,0),(428,NULL,0),(429,NULL,0),(430,NULL,0),(431,NULL,0),(432,NULL,0),(433,NULL,0),(434,NULL,0),(435,NULL,0),(436,NULL,0),(437,NULL,0),(438,NULL,0),(439,NULL,0),(440,NULL,0),(441,NULL,0),(442,NULL,0),(443,NULL,0),(444,NULL,0),(445,NULL,1),(446,NULL,0),(448,NULL,0),(449,NULL,0),(450,NULL,0),(451,NULL,0),(452,NULL,0),(453,NULL,0),(454,NULL,0),(455,NULL,0),(456,NULL,0),(457,NULL,0),(458,NULL,0),(459,NULL,0),(460,NULL,0),(461,NULL,0),(462,NULL,0),(463,NULL,0),(464,NULL,0),(465,NULL,0),(466,NULL,1),(467,NULL,0),(468,NULL,0),(469,NULL,0),(471,NULL,0),(472,NULL,0),(473,NULL,0),(474,NULL,0),(475,NULL,0),(476,NULL,0),(477,NULL,0),(478,NULL,0),(479,NULL,0),(480,NULL,0),(481,NULL,1),(482,NULL,0),(483,NULL,0),(484,NULL,0),(485,NULL,1),(486,NULL,0),(34,1,1),(34,6,2),(34,7,1),(233,1,1),(233,6,2),(233,7,1),(6,1,1),(6,6,2),(6,7,1),(205,1,1),(205,6,1),(205,7,1),(203,6,1);
/*!40000 ALTER TABLE `stat_cat_prod_count` ENABLE KEYS */;
UNLOCK TABLES;

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
  PRIMARY KEY (`project_category_id`,`lft`,`rgt`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stat_cat_tree`
--

LOCK TABLES `stat_cat_tree` WRITE;
/*!40000 ALTER TABLE `stat_cat_tree` DISABLE KEYS */;
INSERT INTO `stat_cat_tree` VALUES (34,0,905,'root',NULL,1,0,NULL,1,0,0,'34','root','root'),(98,2,3,'Android Apps',NULL,1,0,NULL,1,0,1,'34,98','root | Android Apps','root | Android Apps'),(52,4,5,'Linux Apps',NULL,1,0,NULL,1,0,1,'34,52','root | Linux Apps','root | Linux Apps'),(53,6,7,'Jolla Apps',NULL,1,0,NULL,1,0,1,'34,53','root | Jolla Apps','root | Jolla Apps'),(48,8,9,'Windows Apps',NULL,1,0,NULL,1,0,1,'34,48','root | Windows Apps','root | Windows Apps'),(44,40,41,'Health, Fitness & Dieting',NULL,1,0,NULL,1,0,1,'34,44','root | Health, Fitness & Dieting','root | Health, Fitness & Dieting'),(40,42,51,'Computers & Technology',NULL,1,0,NULL,1,0,1,'34,40','root | Computers & Technology','root | Computers & Technology'),(54,43,44,'Perl',NULL,1,0,NULL,1,0,2,'34,40,54','root | Computers & Technology | Perl','root | Computers & Technology | Perl'),(55,45,46,'PHP',NULL,1,0,NULL,1,0,2,'34,40,55','root | Computers & Technology | PHP','root | Computers & Technology | PHP'),(56,47,48,'Python',NULL,1,0,NULL,1,0,2,'34,40,56','root | Computers & Technology | Python','root | Computers & Technology | Python'),(45,52,53,'Literature & Fiction',NULL,1,0,NULL,1,0,1,'34,45','root | Literature & Fiction','root | Literature & Fiction'),(46,54,55,'Mystery, Thriller & Suspense',NULL,1,0,NULL,1,0,1,'34,46','root | Mystery, Thriller & Suspense','root | Mystery, Thriller & Suspense'),(41,56,57,'Sports & Outdoors',NULL,1,0,NULL,1,0,1,'34,41','root | Sports & Outdoors','root | Sports & Outdoors'),(38,58,59,'Calendars',NULL,1,0,NULL,1,0,1,'34,38','root | Calendars','root | Calendars'),(42,60,61,'Travel',NULL,1,0,NULL,1,0,1,'34,42','root | Travel','root | Travel'),(79,62,63,'Fun',NULL,1,0,NULL,1,0,1,'34,79','root | Fun','root | Fun'),(35,64,65,'Arts & Photography',NULL,1,0,NULL,1,0,1,'34,35','root | Arts & Photography','root | Arts & Photography'),(36,66,67,'Biographies & Memoirs',NULL,1,0,NULL,1,0,1,'34,36','root | Biographies & Memoirs','root | Biographies & Memoirs'),(37,68,69,'Business & Investing',NULL,1,0,NULL,1,0,1,'34,37','root | Business & Investing','root | Business & Investing'),(43,70,71,'Cookbooks, Food & Wine',NULL,1,0,NULL,1,0,1,'34,43','root | Cookbooks, Food & Wine','root | Cookbooks, Food & Wine'),(81,74,75,'test',NULL,1,0,NULL,1,0,1,'34,81','root | test','root | test'),(148,79,364,'Linux/Unix Desktops','',1,0,'',1,0,1,'34,148','root | Linux/Unix Desktops','root | Linux/Unix Desktops'),(381,80,147,'Desktop Themes','',1,NULL,'',1,0,2,'34,148,381','root | Linux/Unix Desktops | Desktop Themes','root | Linux/Unix Desktops | Desktop Themes'),(133,81,82,'Cinnamon Themes','',1,0,'cinnamon_themes',1,0,3,'34,148,381,133','root | Linux/Unix Desktops | Desktop Themes | Cinnamon Themes','root | Linux/Unix Desktops | Desktop Themes | Cinnamon Themes'),(120,83,84,'Various KDE 1.-4. Styles','',1,0,'',1,1,3,'34,148,381,120','root | Linux/Unix Desktops | Desktop Themes | Various KDE 1.-4. Styles','root | Linux/Unix Desktops | Desktop Themes | Various KDE 1.-4. Styles'),(223,85,86,'KDE 2 Themes','',1,NULL,'',1,0,3,'34,148,381,223','root | Linux/Unix Desktops | Desktop Themes | KDE 2 Themes','root | Linux/Unix Desktops | Desktop Themes | KDE 2 Themes'),(385,87,96,'KDE 3 Themes','',1,NULL,'',1,0,3,'34,148,381,385','root | Linux/Unix Desktops | Desktop Themes | KDE 3 Themes','root | Linux/Unix Desktops | Desktop Themes | KDE 3 Themes'),(230,88,89,'KDE 3 Color Schemes','',1,NULL,'',1,0,4,'34,148,381,385,230','root | Linux/Unix Desktops | Desktop Themes | KDE 3 Themes | KDE 3 Color Schemes','root | Linux/Unix Desktops | Desktop Themes | KDE 3 Themes | KDE 3 Color Schemes'),(172,90,91,'KDE 3.5 Themes','',1,0,'',1,0,4,'34,148,381,385,172','root | Linux/Unix Desktops | Desktop Themes | KDE 3 Themes | KDE 3.5 Themes','root | Linux/Unix Desktops | Desktop Themes | KDE 3 Themes | KDE 3.5 Themes'),(315,92,93,'KDE 3 Domino Styles','',1,NULL,'',1,0,4,'34,148,381,385,315','root | Linux/Unix Desktops | Desktop Themes | KDE 3 Themes | KDE 3 Domino Styles','root | Linux/Unix Desktops | Desktop Themes | KDE 3 Themes | KDE 3 Domino Styles'),(224,94,95,'KDE 3.0-3.4 Themes','',1,NULL,'',1,0,4,'34,148,381,385,224','root | Linux/Unix Desktops | Desktop Themes | KDE 3 Themes | KDE 3.0-3.4 Themes','root | Linux/Unix Desktops | Desktop Themes | KDE 3 Themes | KDE 3.0-3.4 Themes'),(294,97,108,'Enlightenment','',1,NULL,'',1,0,3,'34,148,381,294','root | Linux/Unix Desktops | Desktop Themes | Enlightenment','root | Linux/Unix Desktops | Desktop Themes | Enlightenment'),(168,98,99,'E Animated Backgrounds','',1,0,'enlightenment_backgrounds',1,0,4,'34,148,381,294,168','root | Linux/Unix Desktops | Desktop Themes | Enlightenment | E Animated Backgrounds','root | Linux/Unix Desktops | Desktop Themes | Enlightenment | E Animated Backgrounds'),(167,100,101,'E Enlightenment Backgrounds','',1,0,'enlightenment_backgrounds',1,0,4,'34,148,381,294,167','root | Linux/Unix Desktops | Desktop Themes | Enlightenment | E Enlightenment Backgrounds','root | Linux/Unix Desktops | Desktop Themes | Enlightenment | E Enlightenment Backgrounds'),(173,102,103,'E Modules','',1,0,'',1,0,4,'34,148,381,294,173','root | Linux/Unix Desktops | Desktop Themes | Enlightenment | E Modules','root | Linux/Unix Desktops | Desktop Themes | Enlightenment | E Modules'),(145,104,105,'Enlightenment Themes','',1,0,'enlightenment_themes',1,0,4,'34,148,381,294,145','root | Linux/Unix Desktops | Desktop Themes | Enlightenment | Enlightenment Themes','root | Linux/Unix Desktops | Desktop Themes | Enlightenment | Enlightenment Themes'),(166,106,107,'E Entrance Themes','',1,0,'',1,0,4,'34,148,381,294,166','root | Linux/Unix Desktops | Desktop Themes | Enlightenment | E Entrance Themes','root | Linux/Unix Desktops | Desktop Themes | Enlightenment | E Entrance Themes'),(366,111,122,'Gnome/GTK','',1,NULL,'',1,0,3,'34,148,381,366','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK'),(284,112,113,'GTK1 Themes','',1,NULL,'',1,0,4,'34,148,381,366,284','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK | GTK1 Themes','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK | GTK1 Themes'),(136,114,115,'GTK2 Themes','GTK 2.x Theme/Style',1,0,'gtk2_themes',1,0,4,'34,148,381,366,136','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK | GTK2 Themes','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK | GTK 2.x Theme/Style'),(135,116,117,'GTK3 Themes','GTK 3.x Theme/Style',1,0,'gtk3_themes',1,0,4,'34,148,381,366,135','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK | GTK3 Themes','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK | GTK 3.x Theme/Style'),(134,118,119,'Gnome Shell Themes','GNOME Shell Theme',1,0,'gnome_shell_themes',1,0,4,'34,148,381,366,134','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK | Gnome Shell Themes','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK | GNOME Shell Theme'),(200,120,121,'Gnome 2 Color Schemes','',1,0,'gnome_color_schemes',1,0,4,'34,148,381,366,200','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK | Gnome 2 Color Schemes','root | Linux/Unix Desktops | Desktop Themes | Gnome/GTK | Gnome 2 Color Schemes'),(365,123,134,'KDE Plasma','',1,NULL,'',1,0,3,'34,148,381,365','root | Linux/Unix Desktops | Desktop Themes | KDE Plasma','root | Linux/Unix Desktops | Desktop Themes | KDE Plasma'),(112,124,125,'Plasma Color Schemes','KDE Color Scheme KDE4',1,0,'plasma_color_schemes',1,0,4,'34,148,381,365,112','root | Linux/Unix Desktops | Desktop Themes | KDE Plasma | Plasma Color Schemes','root | Linux/Unix Desktops | Desktop Themes | KDE Plasma | KDE Color Scheme KDE4'),(121,126,127,'Plasma Look-and-Feel Packs','121',1,0,'plasma_look_and_feel',1,1,4,'34,148,381,365,121','root | Linux/Unix Desktops | Desktop Themes | KDE Plasma | Plasma Look-and-Feel Packs','root | Linux/Unix Desktops | Desktop Themes | KDE Plasma | 121'),(104,128,129,'Plasma Themes','Plasma Theme',1,0,'plasma5_desktopthemes',1,0,4,'34,148,381,365,104','root | Linux/Unix Desktops | Desktop Themes | KDE Plasma | Plasma Themes','root | Linux/Unix Desktops | Desktop Themes | KDE Plasma | Plasma Theme'),(421,132,133,'Various Plasma Styles','',1,NULL,'',1,1,4,'34,148,381,365,421','root | Linux/Unix Desktops | Desktop Themes | KDE Plasma | Various Plasma Styles','root | Linux/Unix Desktops | Desktop Themes | KDE Plasma | Various Plasma Styles'),(119,135,136,'QtCurve','',1,0,'qtcurve',1,0,3,'34,148,381,119','root | Linux/Unix Desktops | Desktop Themes | QtCurve','root | Linux/Unix Desktops | Desktop Themes | QtCurve'),(123,137,138,'Kvantum','',1,0,'kvantum_themes',1,0,3,'34,148,381,123','root | Linux/Unix Desktops | Desktop Themes | Kvantum','root | Linux/Unix Desktops | Desktop Themes | Kvantum'),(266,139,140,'Be-Shell/Bespin','',1,NULL,'',1,0,3,'34,148,381,266','root | Linux/Unix Desktops | Desktop Themes | Be-Shell/Bespin','root | Linux/Unix Desktops | Desktop Themes | Be-Shell/Bespin'),(446,141,142,'LXQt Themes','',1,NULL,'lxqt_themes',1,0,3,'34,148,381,446','root | Linux/Unix Desktops | Desktop Themes | LXQt Themes','root | Linux/Unix Desktops | Desktop Themes | LXQt Themes'),(384,148,205,'Desktop Extensions','',1,NULL,'',1,0,2,'34,148,384','root | Linux/Unix Desktops | Desktop Extensions','root | Linux/Unix Desktops | Desktop Extensions'),(464,149,150,'Various Stuff','',1,NULL,'',1,1,3,'34,148,384,464','root | Linux/Unix Desktops | Desktop Extensions | Various Stuff','root | Linux/Unix Desktops | Desktop Extensions | Various Stuff'),(178,151,152,'Karamba & Superkaramba','',1,0,'',1,1,3,'34,148,384,178','root | Linux/Unix Desktops | Desktop Extensions | Karamba & Superkaramba','root | Linux/Unix Desktops | Desktop Extensions | Karamba & Superkaramba'),(124,153,154,'Conky','',1,0,'conky',1,0,3,'34,148,384,124','root | Linux/Unix Desktops | Desktop Extensions | Conky','root | Linux/Unix Desktops | Desktop Extensions | Conky'),(377,155,156,'GKrellM','',1,NULL,'',1,0,3,'34,148,384,377','root | Linux/Unix Desktops | Desktop Extensions | GKrellM','root | Linux/Unix Desktops | Desktop Extensions | GKrellM'),(208,157,158,'Cairo Clock','',1,0,'cairo_clock_themes',1,0,3,'34,148,384,208','root | Linux/Unix Desktops | Desktop Extensions | Cairo Clock','root | Linux/Unix Desktops | Desktop Extensions | Cairo Clock'),(414,159,166,'Cinnamon Extensions','',1,NULL,'',1,0,3,'34,148,384,414','root | Linux/Unix Desktops | Desktop Extensions | Cinnamon Extensions','root | Linux/Unix Desktops | Desktop Extensions | Cinnamon Extensions'),(264,160,161,'Cinnamon Applets','',1,NULL,'cinnamon_applets',1,0,4,'34,148,384,414,264','root | Linux/Unix Desktops | Desktop Extensions | Cinnamon Extensions | Cinnamon Applets','root | Linux/Unix Desktops | Desktop Extensions | Cinnamon Extensions | Cinnamon Applets'),(57,162,163,'Cinnamon Desklets','',1,0,'cinnamon_desklets',1,0,4,'34,148,384,414,57','root | Linux/Unix Desktops | Desktop Extensions | Cinnamon Extensions | Cinnamon Desklets','root | Linux/Unix Desktops | Desktop Extensions | Cinnamon Extensions | Cinnamon Desklets'),(265,164,165,'Cinnamon Extensions','',1,NULL,'cinnamon_extensions',1,0,4,'34,148,384,414,265','root | Linux/Unix Desktops | Desktop Extensions | Cinnamon Extensions | Cinnamon Extensions','root | Linux/Unix Desktops | Desktop Extensions | Cinnamon Extensions | Cinnamon Extensions'),(423,167,196,'KDE Plasma Extensions','',1,NULL,'',1,0,3,'34,148,384,423','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions'),(422,168,169,'Various Plasma 5 Improvements','',1,NULL,'',1,1,4,'34,148,384,423,422','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Various Plasma 5 Improvements','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Various Plasma 5 Improvements'),(418,170,185,'Plasma 5 Widgets','Plasma 5 Plasmoid',1,NULL,'',1,1,4,'34,148,384,423,418','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Widgets','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Plasmoid'),(105,171,172,'Plasma 5 Add-Ons','',1,0,'plasma5_plasmoids',1,1,5,'34,148,384,423,418,105','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Widgets | Plasma 5 Add-Ons','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Plasmoid | Plasma 5 Add-Ons'),(398,173,174,'Plasma 5 Menus','',1,NULL,'plasma5_plasmoids',1,1,5,'34,148,384,423,418,398','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Widgets | Plasma 5 Menus','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Plasmoid | Plasma 5 Menus'),(399,175,176,'Plasma 5 Clocks','',1,NULL,'plasma5_plasmoids',1,1,5,'34,148,384,423,418,399','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Widgets | Plasma 5 Clocks','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Plasmoid | Plasma 5 Clocks'),(420,177,178,'Plasma 5 Multimedia','',1,NULL,'plasma5_plasmoids',1,1,5,'34,148,384,423,418,420','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Widgets | Plasma 5 Multimedia','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Plasmoid | Plasma 5 Multimedia'),(424,179,180,'Plasma 5 Weather','',1,NULL,'plasma5_plasmoids',1,1,5,'34,148,384,423,418,424','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Widgets | Plasma 5 Weather','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Plasmoid | Plasma 5 Weather'),(425,181,182,'Plasma 5 Monitoring','',1,NULL,'plasma5_plasmoids',1,1,5,'34,148,384,423,418,425','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Widgets | Plasma 5 Monitoring','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Plasmoid | Plasma 5 Monitoring'),(463,183,184,'Plasma 5 Calendars','',1,NULL,'plasma5_plasmoids',1,1,5,'34,148,384,423,418,463','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Widgets | Plasma 5 Calendars','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 5 Plasmoid | Plasma 5 Calendars'),(419,186,187,'Plasma Wallpaper Plugins','Plasma Wallpaper Plugin',1,NULL,'',1,1,4,'34,148,384,423,419','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma Wallpaper Plugins','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma Wallpaper Plugin'),(155,188,189,'Plasma Comic Sources','Plasma Comic',1,0,'',1,1,4,'34,148,384,423,155','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma Comic Sources','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma Comic'),(157,190,191,'Various KDE 1.-4. Improvements','',1,0,'',1,1,4,'34,148,384,423,157','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Various KDE 1.-4. Improvements','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Various KDE 1.-4. Improvements'),(106,192,193,'Plasma 4 Widgets','',1,0,'plasma4_plasmoids',1,1,4,'34,148,384,423,106','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 4 Widgets','root | Linux/Unix Desktops | Desktop Extensions | KDE Plasma Extensions | Plasma 4 Widgets'),(460,197,202,'Gnome','',1,NULL,'',1,1,3,'34,148,384,460','root | Linux/Unix Desktops | Desktop Extensions | Gnome','root | Linux/Unix Desktops | Desktop Extensions | Gnome'),(207,198,199,'Various Gnome Stuff','',1,0,'',1,1,4,'34,148,384,460,207','root | Linux/Unix Desktops | Desktop Extensions | Gnome | Various Gnome Stuff','root | Linux/Unix Desktops | Desktop Extensions | Gnome | Various Gnome Stuff'),(156,200,201,'Gnome Extensions','',1,0,'gnome_shell_extensions',1,1,4,'34,148,384,460,156','root | Linux/Unix Desktops | Desktop Extensions | Gnome | Gnome Extensions','root | Linux/Unix Desktops | Desktop Extensions | Gnome | Gnome Extensions'),(465,203,204,'Amor Themes','',1,NULL,'',1,1,3,'34,148,384,465','root | Linux/Unix Desktops | Desktop Extensions | Amor Themes','root | Linux/Unix Desktops | Desktop Extensions | Amor Themes'),(225,208,239,'Screenshots','',1,NULL,'',0.1,1,2,'34,148,225','root | Linux/Unix Desktops | Screenshots','root | Linux/Unix Desktops | Screenshots'),(226,209,210,'Unity Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,226','root | Linux/Unix Desktops | Screenshots | Unity Screenshots','root | Linux/Unix Desktops | Screenshots | Unity Screenshots'),(227,211,212,'Cinnamon Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,227','root | Linux/Unix Desktops | Screenshots | Cinnamon Screenshots','root | Linux/Unix Desktops | Screenshots | Cinnamon Screenshots'),(228,213,214,'Plasma/KDE Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,228','root | Linux/Unix Desktops | Screenshots | Plasma/KDE Screenshots','root | Linux/Unix Desktops | Screenshots | Plasma/KDE Screenshots'),(256,215,216,'XFCE Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,256','root | Linux/Unix Desktops | Screenshots | XFCE Screenshots','root | Linux/Unix Desktops | Screenshots | XFCE Screenshots'),(257,217,218,'Gnome Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,257','root | Linux/Unix Desktops | Screenshots | Gnome Screenshots','root | Linux/Unix Desktops | Screenshots | Gnome Screenshots'),(258,219,220,'Window-Manager Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,258','root | Linux/Unix Desktops | Screenshots | Window-Manager Screenshots','root | Linux/Unix Desktops | Screenshots | Window-Manager Screenshots'),(452,221,222,'Mate Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,452','root | Linux/Unix Desktops | Screenshots | Mate Screenshots','root | Linux/Unix Desktops | Screenshots | Mate Screenshots'),(453,223,224,'Deepin Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,453','root | Linux/Unix Desktops | Screenshots | Deepin Screenshots','root | Linux/Unix Desktops | Screenshots | Deepin Screenshots'),(454,225,226,'Budgie Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,454','root | Linux/Unix Desktops | Screenshots | Budgie Screenshots','root | Linux/Unix Desktops | Screenshots | Budgie Screenshots'),(455,227,228,'Fluxbox Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,455','root | Linux/Unix Desktops | Screenshots | Fluxbox Screenshots','root | Linux/Unix Desktops | Screenshots | Fluxbox Screenshots'),(456,229,230,'LXDE/LXQt Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,456','root | Linux/Unix Desktops | Screenshots | LXDE/LXQt Screenshots','root | Linux/Unix Desktops | Screenshots | LXDE/LXQt Screenshots'),(457,231,232,'IceWM Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,457','root | Linux/Unix Desktops | Screenshots | IceWM Screenshots','root | Linux/Unix Desktops | Screenshots | IceWM Screenshots'),(458,233,234,'Enlightenment Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,458','root | Linux/Unix Desktops | Screenshots | Enlightenment Screenshots','root | Linux/Unix Desktops | Screenshots | Enlightenment Screenshots'),(459,235,236,'Elementary Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,459','root | Linux/Unix Desktops | Screenshots | Elementary Screenshots','root | Linux/Unix Desktops | Screenshots | Elementary Screenshots'),(461,237,238,'Openbox Screenshots','',1,NULL,'',0.1,1,3,'34,148,225,461','root | Linux/Unix Desktops | Screenshots | Openbox Screenshots','root | Linux/Unix Desktops | Screenshots | Openbox Screenshots'),(103,240,241,'Fonts','',1,0,'fonts',1,0,2,'34,148,103','root | Linux/Unix Desktops | Fonts','root | Linux/Unix Desktops | Fonts'),(316,242,243,'System Sounds','',1,NULL,'',1,0,2,'34,148,316','root | Linux/Unix Desktops | System Sounds','root | Linux/Unix Desktops | System Sounds'),(277,244,265,'Docks and Launchers','',1,NULL,'',1,0,2,'34,148,277','root | Linux/Unix Desktops | Docks and Launchers','root | Linux/Unix Desktops | Docks and Launchers'),(177,245,246,'Kbfx Startmenu','',1,0,'',1,0,3,'34,148,277,177','root | Linux/Unix Desktops | Docks and Launchers | Kbfx Startmenu','root | Linux/Unix Desktops | Docks and Launchers | Kbfx Startmenu'),(127,247,248,'GnoMenu Skins','',1,0,'',1,0,3,'34,148,277,127','root | Linux/Unix Desktops | Docks and Launchers | GnoMenu Skins','root | Linux/Unix Desktops | Docks and Launchers | GnoMenu Skins'),(273,249,250,'Plank Themes','',1,NULL,'',1,0,3,'34,148,277,273','root | Linux/Unix Desktops | Docks and Launchers | Plank Themes','root | Linux/Unix Desktops | Docks and Launchers | Plank Themes'),(276,251,252,'DockbarX Themes','',1,NULL,'',1,0,3,'34,148,277,276','root | Linux/Unix Desktops | Docks and Launchers | DockbarX Themes','root | Linux/Unix Desktops | Docks and Launchers | DockbarX Themes'),(275,253,254,'AWN Themes','',1,NULL,'',1,0,3,'34,148,277,275','root | Linux/Unix Desktops | Docks and Launchers | AWN Themes','root | Linux/Unix Desktops | Docks and Launchers | AWN Themes'),(274,255,256,'Cairo-Dock Themes','',1,NULL,'',1,0,3,'34,148,277,274','root | Linux/Unix Desktops | Docks and Launchers | Cairo-Dock Themes','root | Linux/Unix Desktops | Docks and Launchers | Cairo-Dock Themes'),(272,257,258,'Docky Themes','',1,NULL,'',1,0,3,'34,148,277,272','root | Linux/Unix Desktops | Docks and Launchers | Docky Themes','root | Linux/Unix Desktops | Docks and Launchers | Docky Themes'),(285,259,260,'Kicker Panel','',1,NULL,'',1,0,3,'34,148,277,285','root | Linux/Unix Desktops | Docks and Launchers | Kicker Panel','root | Linux/Unix Desktops | Docks and Launchers | Kicker Panel'),(417,261,262,'Latte Layouts','',1,NULL,'',1,0,3,'34,148,277,417','root | Linux/Unix Desktops | Docks and Launchers | Latte Layouts','root | Linux/Unix Desktops | Docks and Launchers | Latte Layouts'),(475,263,264,'KXDocker','',1,NULL,'',1,0,3,'34,148,277,475','root | Linux/Unix Desktops | Docks and Launchers | KXDocker','root | Linux/Unix Desktops | Docks and Launchers | KXDocker'),(231,266,267,'Screensavers','',1,NULL,'',1,0,2,'34,148,231','root | Linux/Unix Desktops | Screensavers','root | Linux/Unix Desktops | Screensavers'),(151,268,291,'Boot and Splashscreens','',1,0,'',1,1,2,'34,148,151','root | Linux/Unix Desktops | Boot and Splashscreens','root | Linux/Unix Desktops | Boot and Splashscreens'),(130,269,270,'Gnome 2 Splash Screens','',1,0,'',1,1,3,'34,148,151,130','root | Linux/Unix Desktops | Boot and Splashscreens | Gnome 2 Splash Screens','root | Linux/Unix Desktops | Boot and Splashscreens | Gnome 2 Splash Screens'),(111,271,272,'Plasma Splash Screens','KDE 4.x Splash Screen',1,0,'',1,1,3,'34,148,151,111','root | Linux/Unix Desktops | Boot and Splashscreens | Plasma Splash Screens','root | Linux/Unix Desktops | Boot and Splashscreens | KDE 4.x Splash Screen'),(109,275,276,'GRUB Themes','',1,0,'',1,1,3,'34,148,151,109','root | Linux/Unix Desktops | Boot and Splashscreens | GRUB Themes','root | Linux/Unix Desktops | Boot and Splashscreens | GRUB Themes'),(108,277,278,'Plymouth Themes','',1,0,'',1,1,3,'34,148,151,108','root | Linux/Unix Desktops | Boot and Splashscreens | Plymouth Themes','root | Linux/Unix Desktops | Boot and Splashscreens | Plymouth Themes'),(221,279,280,'KDE 3.x Splash Screens','',1,0,'',1,1,3,'34,148,151,221','root | Linux/Unix Desktops | Boot and Splashscreens | KDE 3.x Splash Screens','root | Linux/Unix Desktops | Boot and Splashscreens | KDE 3.x Splash Screens'),(269,281,282,'Usplash Themes','',1,NULL,'',1,1,3,'34,148,151,269','root | Linux/Unix Desktops | Boot and Splashscreens | Usplash Themes','root | Linux/Unix Desktops | Boot and Splashscreens | Usplash Themes'),(270,283,284,'XSplash Themes','',1,NULL,'',1,1,3,'34,148,151,270','root | Linux/Unix Desktops | Boot and Splashscreens | XSplash Themes','root | Linux/Unix Desktops | Boot and Splashscreens | XSplash Themes'),(278,285,286,'Splashy Themes','',1,NULL,'',1,1,3,'34,148,151,278','root | Linux/Unix Desktops | Boot and Splashscreens | Splashy Themes','root | Linux/Unix Desktops | Boot and Splashscreens | Splashy Themes'),(480,287,288,'GFXBoot','',1,NULL,'',1,1,3,'34,148,151,480','root | Linux/Unix Desktops | Boot and Splashscreens | GFXBoot','root | Linux/Unix Desktops | Boot and Splashscreens | GFXBoot'),(486,289,290,'Bootsplash Various','',1,NULL,'',1,1,3,'34,148,151,486','root | Linux/Unix Desktops | Boot and Splashscreens | Bootsplash Various','root | Linux/Unix Desktops | Boot and Splashscreens | Bootsplash Various'),(107,292,293,'Cursors','X11 Mouse Theme',1,0,'cursors',1,0,2,'34,148,107','root | Linux/Unix Desktops | Cursors','root | Linux/Unix Desktops | X11 Mouse Theme'),(146,294,307,'Login Managers','',1,0,'',1,0,2,'34,148,146','root | Linux/Unix Desktops | Login Managers','root | Linux/Unix Desktops | Login Managers'),(154,295,296,'LightDM Themes',NULL,1,0,NULL,1,0,3,'34,148,146,154','root | Linux/Unix Desktops | Login Managers | LightDM Themes','root | Linux/Unix Desktops | Login Managers | LightDM Themes'),(153,297,298,'MDM Themes',NULL,1,0,NULL,1,0,3,'34,148,146,153','root | Linux/Unix Desktops | Login Managers | MDM Themes','root | Linux/Unix Desktops | Login Managers | MDM Themes'),(101,299,300,'SDDM Login Themes','SDDM Theme',1,0,'',1,0,3,'34,148,146,101','root | Linux/Unix Desktops | Login Managers | SDDM Login Themes','root | Linux/Unix Desktops | Login Managers | SDDM Theme'),(100,301,302,'KDM4 Themes',NULL,1,0,NULL,1,0,3,'34,148,146,100','root | Linux/Unix Desktops | Login Managers | KDM4 Themes','root | Linux/Unix Desktops | Login Managers | KDM4 Themes'),(131,303,304,'GDM Themes',NULL,1,0,NULL,1,0,3,'34,148,146,131','root | Linux/Unix Desktops | Login Managers | GDM Themes','root | Linux/Unix Desktops | Login Managers | GDM Themes'),(181,305,306,'KDM3 Themes',NULL,1,0,NULL,1,0,3,'34,148,146,181','root | Linux/Unix Desktops | Login Managers | KDM3 Themes','root | Linux/Unix Desktops | Login Managers | KDM3 Themes'),(147,308,349,'Window Managers','',1,0,'',1,0,2,'34,148,147','root | Linux/Unix Desktops | Window Managers','root | Linux/Unix Desktops | Window Managers'),(267,309,316,'Compiz','',1,NULL,'',1,0,3,'34,148,147,267','root | Linux/Unix Desktops | Window Managers | Compiz','root | Linux/Unix Desktops | Window Managers | Compiz'),(171,310,311,'Cubecaps','',1,0,'',1,0,4,'34,148,147,267,171','root | Linux/Unix Desktops | Window Managers | Compiz | Cubecaps','root | Linux/Unix Desktops | Window Managers | Compiz | Cubecaps'),(170,312,313,'Skydomes','',1,0,'',1,0,4,'34,148,147,267,170','root | Linux/Unix Desktops | Window Managers | Compiz | Skydomes','root | Linux/Unix Desktops | Window Managers | Compiz | Skydomes'),(116,314,315,'Compiz Themes','Compiz Theme',1,0,'compiz_themes',1,0,4,'34,148,147,267,116','root | Linux/Unix Desktops | Window Managers | Compiz | Compiz Themes','root | Linux/Unix Desktops | Window Managers | Compiz | Compiz Theme'),(349,317,324,'KWin','',1,NULL,'',1,0,3,'34,148,147,349','root | Linux/Unix Desktops | Window Managers | KWin','root | Linux/Unix Desktops | Window Managers | KWin'),(211,318,319,'Kwin Switching Layouts','KWin Switching Layouts',1,0,'kwin_tabbox',1,0,4,'34,148,147,349,211','root | Linux/Unix Desktops | Window Managers | KWin | Kwin Switching Layouts','root | Linux/Unix Desktops | Window Managers | KWin | KWin Switching Layouts'),(210,320,321,'Kwin Scripts','KWin Scripts',1,0,'kwin_scripts',1,0,4,'34,148,147,349,210','root | Linux/Unix Desktops | Window Managers | KWin | Kwin Scripts','root | Linux/Unix Desktops | Window Managers | KWin | KWin Scripts'),(209,322,323,'Kwin Effects','KWin Effects',1,0,'kwin_effects',1,0,4,'34,148,147,349,209','root | Linux/Unix Desktops | Window Managers | KWin | Kwin Effects','root | Linux/Unix Desktops | Window Managers | KWin | KWin Effects'),(138,325,326,'XFCE/XFWM4 Themes','',1,0,'xfwm4_themes',1,0,3,'34,148,147,138','root | Linux/Unix Desktops | Window Managers | XFCE/XFWM4 Themes','root | Linux/Unix Desktops | Window Managers | XFCE/XFWM4 Themes'),(114,327,328,'Aurorae Themes','Window Decoration Aurorae',1,0,'aurorae_themes',1,0,3,'34,148,147,114','root | Linux/Unix Desktops | Window Managers | Aurorae Themes','root | Linux/Unix Desktops | Window Managers | Window Decoration Aurorae'),(117,329,330,'Beryl/Emerald Themes','Beryl Emerald Theme',1,0,'beryl_themes',1,0,3,'34,148,147,117','root | Linux/Unix Desktops | Window Managers | Beryl/Emerald Themes','root | Linux/Unix Desktops | Window Managers | Beryl Emerald Theme'),(118,331,332,'deKorator Themes','Window Decoration deKorator',1,0,'dekorator_themes',1,0,3,'34,148,147,118','root | Linux/Unix Desktops | Window Managers | deKorator Themes','root | Linux/Unix Desktops | Window Managers | Window Decoration deKorator'),(125,333,334,'Metacity Themes','',1,0,'metacity_themes',1,0,3,'34,148,147,125','root | Linux/Unix Desktops | Window Managers | Metacity Themes','root | Linux/Unix Desktops | Window Managers | Metacity Themes'),(143,335,336,'FVWM Themes',NULL,1,0,NULL,1,0,3,'34,148,147,143','root | Linux/Unix Desktops | Window Managers | FVWM Themes','root | Linux/Unix Desktops | Window Managers | FVWM Themes'),(142,337,338,'Ice-WM Themes',NULL,1,0,'icewm_themes',1,0,3,'34,148,147,142','root | Linux/Unix Desktops | Window Managers | Ice-WM Themes','root | Linux/Unix Desktops | Window Managers | Ice-WM Themes'),(141,339,340,'Pek-WM Themes',NULL,1,0,'pekwm_themes',1,0,3,'34,148,147,141','root | Linux/Unix Desktops | Window Managers | Pek-WM Themes','root | Linux/Unix Desktops | Window Managers | Pek-WM Themes'),(140,341,342,'Openbox Themes',NULL,1,0,'openbox_themes',1,0,3,'34,148,147,140','root | Linux/Unix Desktops | Window Managers | Openbox Themes','root | Linux/Unix Desktops | Window Managers | Openbox Themes'),(139,343,344,'Fluxbox Themes',NULL,1,0,'fluxbox_styles',1,0,3,'34,148,147,139','root | Linux/Unix Desktops | Window Managers | Fluxbox Themes','root | Linux/Unix Desktops | Window Managers | Fluxbox Themes'),(144,345,346,'Window-Maker Themes',NULL,1,0,NULL,1,0,3,'34,148,147,144','root | Linux/Unix Desktops | Window Managers | Window-Maker Themes','root | Linux/Unix Desktops | Window Managers | Window-Maker Themes'),(169,347,348,'KDE 3.x Window Decorations',NULL,1,0,'',1,0,3,'34,148,147,169','root | Linux/Unix Desktops | Window Managers | KDE 3.x Window Decorations','root | Linux/Unix Desktops | Window Managers | KDE 3.x Window Decorations'),(386,354,363,'Icons','',1,NULL,'',1,0,2,'34,148,386','root | Linux/Unix Desktops | Icons','root | Linux/Unix Desktops | Icons'),(199,355,356,'Individual Icons/-sets','',1,0,'',1,0,3,'34,148,386,199','root | Linux/Unix Desktops | Icons | Individual Icons/-sets','root | Linux/Unix Desktops | Icons | Individual Icons/-sets'),(197,357,358,'Logos','',1,0,'',1,0,3,'34,148,386,197','root | Linux/Unix Desktops | Icons | Logos','root | Linux/Unix Desktops | Icons | Logos'),(132,359,360,'Icon Themes','KDE Icon Theme',1,0,'icons',1,0,3,'34,148,386,132','root | Linux/Unix Desktops | Icons | Icon Themes','root | Linux/Unix Desktops | Icons | KDE Icon Theme'),(113,361,362,'Emoticons','Emoticon Theme',1,0,'emoticons',1,0,3,'34,148,386,113','root | Linux/Unix Desktops | Icons | Emoticons','root | Linux/Unix Desktops | Icons | Emoticon Theme'),(152,371,634,'App Addons','',1,0,'',1,1,1,'34,152','root | App Addons','root | App Addons'),(355,372,407,'KDE Game-Addons','',1,NULL,'',1,1,2,'34,152,355','root | App Addons | KDE Game-Addons','root | App Addons | KDE Game-Addons'),(351,375,376,'KAtomic Levels','KAtomic Level',1,NULL,'',1,0,3,'34,152,355,351','root | App Addons | KDE Game-Addons | KAtomic Levels','root | App Addons | KDE Game-Addons | KAtomic Level'),(337,377,378,'Khangman','KHangMan',1,NULL,'',1,0,3,'34,152,355,337','root | App Addons | KDE Game-Addons | Khangman','root | App Addons | KDE Game-Addons | KHangMan'),(336,379,380,'Kanagram','',1,NULL,'',1,0,3,'34,152,355,336','root | App Addons | KDE Game-Addons | Kanagram','root | App Addons | KDE Game-Addons | Kanagram'),(218,381,382,'KWordQuiz','',1,0,'',1,0,3,'34,152,355,218','root | App Addons | KDE Game-Addons | KWordQuiz','root | App Addons | KDE Game-Addons | KWordQuiz'),(183,383,384,'Knights Themes','KDE Knights Theme',1,0,'',1,0,3,'34,152,355,183','root | App Addons | KDE Game-Addons | Knights Themes','root | App Addons | KDE Game-Addons | KDE Knights Theme'),(406,385,386,'KBlocks Themes','',1,NULL,'',1,0,3,'34,152,355,406','root | App Addons | KDE Game-Addons | KBlocks Themes','root | App Addons | KDE Game-Addons | KBlocks Themes'),(407,387,388,'KDiamonds Themes','',1,NULL,'',1,0,3,'34,152,355,407','root | App Addons | KDE Game-Addons | KDiamonds Themes','root | App Addons | KDE Game-Addons | KDiamonds Themes'),(408,389,390,'KGoldrunner Themes','',1,NULL,'',1,0,3,'34,152,355,408','root | App Addons | KDE Game-Addons | KGoldrunner Themes','root | App Addons | KDE Game-Addons | KGoldrunner Themes'),(409,391,392,'Kigo Games','',1,NULL,'',1,0,3,'34,152,355,409','root | App Addons | KDE Game-Addons | Kigo Games','root | App Addons | KDE Game-Addons | Kigo Games'),(410,393,394,'Kigo Themes','',1,NULL,'',1,0,3,'34,152,355,410','root | App Addons | KDE Game-Addons | Kigo Themes','root | App Addons | KDE Game-Addons | Kigo Themes'),(411,395,396,'KSirk Themes','',1,NULL,'',1,0,3,'34,152,355,411','root | App Addons | KDE Game-Addons | KSirk Themes','root | App Addons | KDE Game-Addons | KSirk Themes'),(412,397,398,'KSnakeDuel Themes','',1,NULL,'',1,0,3,'34,152,355,412','root | App Addons | KDE Game-Addons | KSnakeDuel Themes','root | App Addons | KDE Game-Addons | KSnakeDuel Themes'),(413,399,400,'KSudoku Games','',1,NULL,'',1,0,3,'34,152,355,413','root | App Addons | KDE Game-Addons | KSudoku Games','root | App Addons | KDE Game-Addons | KSudoku Games'),(471,401,406,'KPatience','',1,NULL,'',1,1,3,'34,152,355,471','root | App Addons | KDE Game-Addons | KPatience','root | App Addons | KDE Game-Addons | KPatience'),(215,402,403,'KPat Decks','KDE Card Deck',1,0,'',1,1,4,'34,152,355,471,215','root | App Addons | KDE Game-Addons | KPatience | KPat Decks','root | App Addons | KDE Game-Addons | KPatience | KDE Card Deck'),(353,404,405,'KPat Themes','KDE KPat Theme',1,NULL,'',1,1,4,'34,152,355,471,353','root | App Addons | KDE Game-Addons | KPatience | KPat Themes','root | App Addons | KDE Game-Addons | KPatience | KDE KPat Theme'),(321,408,421,'VLC','',1,NULL,'',1,1,2,'34,152,321','root | App Addons | VLC','root | App Addons | VLC'),(128,409,410,'VLC Skins','VLC Skin',1,0,'',1,1,3,'34,152,321,128','root | App Addons | VLC | VLC Skins','root | App Addons | VLC | VLC Skin'),(322,411,412,'VLC Internet Channels','VLC Internet Channel',1,NULL,'',1,1,3,'34,152,321,322','root | App Addons | VLC | VLC Internet Channels','root | App Addons | VLC | VLC Internet Channel'),(323,413,414,'VLC Extensions','VLC Extension',1,NULL,'',1,1,3,'34,152,321,323','root | App Addons | VLC | VLC Extensions','root | App Addons | VLC | VLC Extension'),(324,415,416,'VLC Playlist Parsers','VLC Playlist Parser',1,NULL,'',1,1,3,'34,152,321,324','root | App Addons | VLC | VLC Playlist Parsers','root | App Addons | VLC | VLC Playlist Parser'),(325,417,418,'VLC Plugins','VLC Plugin',1,NULL,'',1,1,3,'34,152,321,325','root | App Addons | VLC | VLC Plugins','root | App Addons | VLC | VLC Plugin'),(326,419,420,'VLC Other','VLC other',1,NULL,'',1,1,3,'34,152,321,326','root | App Addons | VLC | VLC Other','root | App Addons | VLC | VLC other'),(176,422,433,'Krita','',1,0,'',1,1,2,'34,152,176','root | App Addons | Krita','root | App Addons | Krita'),(163,423,424,'Krita Color Profiles','',1,0,'',1,1,3,'34,152,176,163','root | App Addons | Krita | Krita Color Profiles','root | App Addons | Krita | Krita Color Profiles'),(164,425,426,'Krita Templates','',1,0,'',1,1,3,'34,152,176,164','root | App Addons | Krita | Krita Templates','root | App Addons | Krita | Krita Templates'),(165,427,428,'Krita Resource Bundles','',1,0,'',1,1,3,'34,152,176,165','root | App Addons | Krita | Krita Resource Bundles','root | App Addons | Krita | Krita Resource Bundles'),(190,434,445,'Gimp','',1,0,'',1,1,2,'34,152,190','root | App Addons | Gimp','root | App Addons | Gimp'),(191,435,436,'Gimp Brushes','',1,0,'',1,0,3,'34,152,190,191','root | App Addons | Gimp | Gimp Brushes','root | App Addons | Gimp | Gimp Brushes'),(192,437,438,'Gimp Patterns','',1,0,'',1,0,3,'34,152,190,192','root | App Addons | Gimp | Gimp Patterns','root | App Addons | Gimp | Gimp Patterns'),(193,439,440,'Gimp Palettes','',1,0,'',1,0,3,'34,152,190,193','root | App Addons | Gimp | Gimp Palettes','root | App Addons | Gimp | Gimp Palettes'),(194,441,442,'Gimp Splashes','',1,0,'',1,0,3,'34,152,190,194','root | App Addons | Gimp | Gimp Splashes','root | App Addons | Gimp | Gimp Splashes'),(268,443,444,'Gimp Themes',NULL,1,NULL,NULL,1,0,3,'34,152,190,268','root | App Addons | Gimp | Gimp Themes','root | App Addons | Gimp | Gimp Themes'),(186,446,457,'LibreOffice/OpenOffice','',1,0,'',1,1,2,'34,152,186','root | App Addons | LibreOffice/OpenOffice','root | App Addons | LibreOffice/OpenOffice'),(271,447,448,'LibreOffice Splash Screens','',1,NULL,'',1,0,3,'34,152,186,271','root | App Addons | LibreOffice/OpenOffice | LibreOffice Splash Screens','root | App Addons | LibreOffice/OpenOffice | LibreOffice Splash Screens'),(187,449,450,'ODF Text',NULL,1,0,NULL,1,0,3,'34,152,186,187','root | App Addons | LibreOffice/OpenOffice | ODF Text','root | App Addons | LibreOffice/OpenOffice | ODF Text'),(188,451,452,'ODF Spreadsheet',NULL,1,0,NULL,1,0,3,'34,152,186,188','root | App Addons | LibreOffice/OpenOffice | ODF Spreadsheet','root | App Addons | LibreOffice/OpenOffice | ODF Spreadsheet'),(189,453,454,'ODF Presentation',NULL,1,0,NULL,1,0,3,'34,152,186,189','root | App Addons | LibreOffice/OpenOffice | ODF Presentation','root | App Addons | LibreOffice/OpenOffice | ODF Presentation'),(476,455,456,'OpenOffice Splash Screens','',1,NULL,'',1,0,3,'34,152,186,476','root | App Addons | LibreOffice/OpenOffice | OpenOffice Splash Screens','root | App Addons | LibreOffice/OpenOffice | OpenOffice Splash Screens'),(196,458,459,'Scribus','',1,0,'',1,0,2,'34,152,196','root | App Addons | Scribus','root | App Addons | Scribus'),(195,460,461,'Inkscape','',1,0,'',1,0,2,'34,152,195','root | App Addons | Inkscape','root | App Addons | Inkscape'),(129,462,463,'XMMS Skins',NULL,1,0,NULL,1,0,2,'34,152,129','root | App Addons | XMMS Skins','root | App Addons | XMMS Skins'),(198,464,465,'SMPlayer/MPlayer','',1,0,'',1,0,2,'34,152,198','root | App Addons | SMPlayer/MPlayer','root | App Addons | SMPlayer/MPlayer'),(232,466,467,'Noatun Skins',NULL,1,NULL,NULL,1,0,2,'34,152,232','root | App Addons | Noatun Skins','root | App Addons | Noatun Skins'),(281,468,469,'Thunderbird Themes',NULL,1,NULL,NULL,1,0,2,'34,152,281','root | App Addons | Thunderbird Themes','root | App Addons | Thunderbird Themes'),(282,470,471,'Chrome/Chromium','',1,NULL,'',1,0,2,'34,152,282','root | App Addons | Chrome/Chromium','root | App Addons | Chrome/Chromium'),(368,472,473,'Telegram Themes','',1,NULL,'',1,0,2,'34,152,368','root | App Addons | Telegram Themes','root | App Addons | Telegram Themes'),(380,474,573,'KDE App-Addons','',1,NULL,'',1,1,2,'34,152,380','root | App Addons | KDE App-Addons','root | App Addons | KDE App-Addons'),(333,475,484,'Kdenlive','',1,NULL,'',1,1,3,'34,152,380,333','root | App Addons | KDE App-Addons | Kdenlive','root | App Addons | KDE App-Addons | Kdenlive'),(185,476,477,'Kdenlive FX','',1,0,'',1,0,4,'34,152,380,333,185','root | App Addons | KDE App-Addons | Kdenlive | Kdenlive FX','root | App Addons | KDE App-Addons | Kdenlive | Kdenlive FX'),(334,478,479,'Kdenlive Export Profiles','',1,NULL,'',1,0,4,'34,152,380,333,334','root | App Addons | KDE App-Addons | Kdenlive | Kdenlive Export Profiles','root | App Addons | KDE App-Addons | Kdenlive | Kdenlive Export Profiles'),(335,480,481,'Kdenlive Title Templates','',1,NULL,'',1,0,4,'34,152,380,333,335','root | App Addons | KDE App-Addons | Kdenlive | Kdenlive Title Templates','root | App Addons | KDE App-Addons | Kdenlive | Kdenlive Title Templates'),(426,482,483,'Kdenlive Keyboard Schemes','',1,NULL,'',1,0,4,'34,152,380,333,426','root | App Addons | KDE App-Addons | Kdenlive | Kdenlive Keyboard Schemes','root | App Addons | KDE App-Addons | Kdenlive | Kdenlive Keyboard Schemes'),(343,485,498,'Kontact/PIM','',1,NULL,'',1,1,3,'34,152,380,343','root | App Addons | KDE App-Addons | Kontact/PIM','root | App Addons | KDE App-Addons | Kontact/PIM'),(344,486,487,'KOrganizer Calendars','KOrganizer Calendar',1,NULL,'',1,0,4,'34,152,380,343,344','root | App Addons | KDE App-Addons | Kontact/PIM | KOrganizer Calendars','root | App Addons | KDE App-Addons | Kontact/PIM | KOrganizer Calendar'),(345,488,489,'KNotes Printing Themes','KNotes Printing Theme',1,NULL,'',1,0,4,'34,152,380,343,345','root | App Addons | KDE App-Addons | Kontact/PIM | KNotes Printing Themes','root | App Addons | KDE App-Addons | Kontact/PIM | KNotes Printing Theme'),(346,490,491,'KMail Header Themes','KMail Header Theme',1,NULL,'',1,0,4,'34,152,380,343,346','root | App Addons | KDE App-Addons | Kontact/PIM | KMail Header Themes','root | App Addons | KDE App-Addons | Kontact/PIM | KMail Header Theme'),(347,492,493,'KAdressbook Themes','KAdressbook Theme',1,NULL,'',1,0,4,'34,152,380,343,347','root | App Addons | KDE App-Addons | Kontact/PIM | KAdressbook Themes','root | App Addons | KDE App-Addons | Kontact/PIM | KAdressbook Theme'),(348,494,495,'Script Sieve','Script Sieve',1,NULL,'',1,0,4,'34,152,380,343,348','root | App Addons | KDE App-Addons | Kontact/PIM | Script Sieve','root | App Addons | KDE App-Addons | Kontact/PIM | Script Sieve'),(354,496,497,'Akonadi Email Providers','',1,NULL,'',1,0,4,'34,152,380,343,354','root | App Addons | KDE App-Addons | Kontact/PIM | Akonadi Email Providers','root | App Addons | KDE App-Addons | Kontact/PIM | Akonadi Email Providers'),(342,499,504,'KDevelop','',1,NULL,'',1,1,3,'34,152,380,342','root | App Addons | KDE App-Addons | KDevelop','root | App Addons | KDE App-Addons | KDevelop'),(327,500,501,'KDevelop File Templates','KDevelop File Template',1,NULL,'',1,0,4,'34,152,380,342,327','root | App Addons | KDE App-Addons | KDevelop | KDevelop File Templates','root | App Addons | KDE App-Addons | KDevelop | KDevelop File Template'),(328,502,503,'KDevelop App Templates','KDE App Template',1,NULL,'',1,0,4,'34,152,380,342,328','root | App Addons | KDE App-Addons | KDevelop | KDevelop App Templates','root | App Addons | KDE App-Addons | KDevelop | KDE App Template'),(340,505,512,'Amarok','',1,NULL,'',1,0,3,'34,152,380,340','root | App Addons | KDE App-Addons | Amarok','root | App Addons | KDE App-Addons | Amarok'),(260,506,507,'Amarok 2.x Scripts','Amarok 2.0 Script',1,NULL,'amarok_scripts',1,0,4,'34,152,380,340,260','root | App Addons | KDE App-Addons | Amarok | Amarok 2.x Scripts','root | App Addons | KDE App-Addons | Amarok | Amarok 2.0 Script'),(259,508,509,'Amarok 1.x Scripts','Amarok Script',1,NULL,'',1,0,4,'34,152,380,340,259','root | App Addons | KDE App-Addons | Amarok | Amarok 1.x Scripts','root | App Addons | KDE App-Addons | Amarok | Amarok Script'),(179,510,511,'Amarok Themes','Amarok Theme',1,0,'',1,0,4,'34,152,380,340,179','root | App Addons | KDE App-Addons | Amarok | Amarok Themes','root | App Addons | KDE App-Addons | Amarok | Amarok Theme'),(375,513,514,'Krunner Plugins','',1,NULL,'',1,0,3,'34,152,380,375','root | App Addons | KDE App-Addons | Krunner Plugins','root | App Addons | KDE App-Addons | Krunner Plugins'),(341,515,520,'Skrooge','',1,NULL,'',1,0,3,'34,152,380,341','root | App Addons | KDE App-Addons | Skrooge','root | App Addons | KDE App-Addons | Skrooge'),(339,516,517,'Skrooge Quote Sources','Skrooge quote source',1,NULL,'',1,0,4,'34,152,380,341,339','root | App Addons | KDE App-Addons | Skrooge | Skrooge Quote Sources','root | App Addons | KDE App-Addons | Skrooge | Skrooge quote source'),(338,518,519,'Skrooge Report Templates','Skrooge report template',1,NULL,'',1,0,4,'34,152,380,341,338','root | App Addons | KDE App-Addons | Skrooge | Skrooge Report Templates','root | App Addons | KDE App-Addons | Skrooge | Skrooge report template'),(356,521,526,'Parley','',1,NULL,'',1,0,3,'34,152,380,356','root | App Addons | KDE App-Addons | Parley','root | App Addons | KDE App-Addons | Parley'),(216,522,523,'Parley Vocabulary Files','',1,0,'',1,0,4,'34,152,380,356,216','root | App Addons | KDE App-Addons | Parley | Parley Vocabulary Files','root | App Addons | KDE App-Addons | Parley | Parley Vocabulary Files'),(357,524,525,'Parley Themes','KDE Parley Theme',1,NULL,'',1,0,4,'34,152,380,356,357','root | App Addons | KDE App-Addons | Parley | Parley Themes','root | App Addons | KDE App-Addons | Parley | KDE Parley Theme'),(370,527,534,'Krusader','',1,NULL,'',1,1,3,'34,152,380,370','root | App Addons | KDE App-Addons | Krusader','root | App Addons | KDE App-Addons | Krusader'),(371,528,529,'Krusader Colormaps','Krusader colormap',1,NULL,'',1,0,4,'34,152,380,370,371','root | App Addons | KDE App-Addons | Krusader | Krusader Colormaps','root | App Addons | KDE App-Addons | Krusader | Krusader colormap'),(372,530,531,'Krusader User Actions','Krusader user action',1,NULL,'',1,0,4,'34,152,380,370,372','root | App Addons | KDE App-Addons | Krusader | Krusader User Actions','root | App Addons | KDE App-Addons | Krusader | Krusader user action'),(373,532,533,'Krusader JS Extensions','Krusader JS extension',1,NULL,'',1,0,4,'34,152,380,370,373','root | App Addons | KDE App-Addons | Krusader | Krusader JS Extensions','root | App Addons | KDE App-Addons | Krusader | Krusader JS extension'),(317,535,542,'Simon Speech','',1,NULL,'',1,1,3,'34,152,380,317','root | App Addons | KDE App-Addons | Simon Speech','root | App Addons | KDE App-Addons | Simon Speech'),(318,536,537,'Simon Base Models','Simon Base Models',1,NULL,'',1,0,4,'34,152,380,317,318','root | App Addons | KDE App-Addons | Simon Speech | Simon Base Models','root | App Addons | KDE App-Addons | Simon Speech | Simon Base Models'),(319,538,539,'Simon Scenarios','Simon Scenario',1,NULL,'',1,0,4,'34,152,380,317,319','root | App Addons | KDE App-Addons | Simon Speech | Simon Scenarios','root | App Addons | KDE App-Addons | Simon Speech | Simon Scenario'),(320,540,541,'Simon Dictionaries','Simon Dictionaries',1,NULL,'',1,0,4,'34,152,380,317,320','root | App Addons | KDE App-Addons | Simon Speech | Simon Dictionaries','root | App Addons | KDE App-Addons | Simon Speech | Simon Dictionaries'),(212,543,544,'Plasma Public Transport Timetables','Plasma public transport timetable',1,0,'',1,0,3,'34,152,380,212','root | App Addons | KDE App-Addons | Plasma Public Transport Timetables','root | App Addons | KDE App-Addons | Plasma public transport timetable'),(184,545,546,'Yakuake Skins','Yakuake Skin',1,0,'yakuake_skins',1,1,3,'34,152,380,184','root | App Addons | KDE App-Addons | Yakuake Skins','root | App Addons | KDE App-Addons | Yakuake Skin'),(213,547,548,'KTextEditor Snippets','KTextEditor Snippet',1,0,'',1,0,3,'34,152,380,213','root | App Addons | KDE App-Addons | KTextEditor Snippets','root | App Addons | KDE App-Addons | KTextEditor Snippet'),(102,549,550,'Dolphin Service Menus','',1,0,'',1,0,3,'34,152,380,102','root | App Addons | KDE App-Addons | Dolphin Service Menus','root | App Addons | KDE App-Addons | Dolphin Service Menus'),(229,551,552,'Kopete Styles','',1,NULL,'',1,0,3,'34,152,380,229','root | App Addons | KDE App-Addons | Kopete Styles','root | App Addons | KDE App-Addons | Kopete Styles'),(220,553,554,'KStars Data','KStars Data',1,0,'',1,0,3,'34,152,380,220','root | App Addons | KDE App-Addons | KStars Data','root | App Addons | KDE App-Addons | KStars Data'),(219,555,556,'KTurtle Scripts','KTurtle Script',1,0,'',1,0,3,'34,152,380,219','root | App Addons | KDE App-Addons | KTurtle Scripts','root | App Addons | KDE App-Addons | KTurtle Script'),(217,557,558,'Marble Maps','Marble Map',1,0,'',1,1,3,'34,152,380,217','root | App Addons | KDE App-Addons | Marble Maps','root | App Addons | KDE App-Addons | Marble Map'),(214,559,560,'Okteta Structure Definitions','Okteta Structure Definition',1,0,'',1,0,3,'34,152,380,214','root | App Addons | KDE App-Addons | Okteta Structure Definitions','root | App Addons | KDE App-Addons | Okteta Structure Definition'),(180,561,562,'K3b Themes','K3b Theme',1,0,'',1,1,3,'34,152,380,180','root | App Addons | KDE App-Addons | K3b Themes','root | App Addons | KDE App-Addons | K3b Theme'),(405,563,564,'Konversation Nicklist Themes','',1,NULL,'',1,1,3,'34,152,380,405','root | App Addons | KDE App-Addons | Konversation Nicklist Themes','root | App Addons | KDE App-Addons | Konversation Nicklist Themes'),(416,565,566,'System Monitor Tabs','',1,NULL,'',1,1,3,'34,152,380,416','root | App Addons | KDE App-Addons | System Monitor Tabs','root | App Addons | KDE App-Addons | System Monitor Tabs'),(462,567,568,'Konsole Color Schemes','',1,NULL,'',1,1,3,'34,152,380,462','root | App Addons | KDE App-Addons | Konsole Color Schemes','root | App Addons | KDE App-Addons | Konsole Color Schemes'),(472,569,570,'Kate','Kate Highlighting',1,NULL,'',1,1,3,'34,152,380,472','root | App Addons | KDE App-Addons | Kate','root | App Addons | KDE App-Addons | Kate Highlighting'),(477,571,572,'Digikam','',1,NULL,'',1,1,3,'34,152,380,477','root | App Addons | KDE App-Addons | Digikam','root | App Addons | KDE App-Addons | Digikam'),(382,574,579,'Gnome App-Addons','',1,NULL,'',1,0,2,'34,152,382','root | App Addons | Gnome App-Addons','root | App Addons | Gnome App-Addons'),(279,575,576,'Gedit Color Schemes','',1,NULL,'',1,0,3,'34,152,382,279','root | App Addons | Gnome App-Addons | Gedit Color Schemes','root | App Addons | Gnome App-Addons | Gedit Color Schemes'),(126,577,578,'Nautilus Scripts','',1,0,'nautilus_scripts',1,0,3,'34,152,382,126','root | App Addons | Gnome App-Addons | Nautilus Scripts','root | App Addons | Gnome App-Addons | Nautilus Scripts'),(402,580,581,'Various Scripts and Stuff','',1,NULL,'',1,0,2,'34,152,402','root | App Addons | Various Scripts and Stuff','root | App Addons | Various Scripts and Stuff'),(415,582,583,'Mycroft Skills','',1,NULL,'',1,0,2,'34,152,415','root | App Addons | Mycroft Skills','root | App Addons | Mycroft Skills'),(427,584,617,'SubSpace Continuum','',1,NULL,'',1,1,2,'34,152,427','root | App Addons | SubSpace Continuum','root | App Addons | SubSpace Continuum'),(441,585,586,'Sounds','',1,NULL,'',1,0,3,'34,152,427,441','root | App Addons | SubSpace Continuum | Sounds','root | App Addons | SubSpace Continuum | Sounds'),(428,587,588,'Audio/Visuals','',1,NULL,'',1,1,3,'34,152,427,428','root | App Addons | SubSpace Continuum | Audio/Visuals','root | App Addons | SubSpace Continuum | Audio/Visuals'),(429,589,590,'Banners','',1,NULL,'',1,1,3,'34,152,427,429','root | App Addons | SubSpace Continuum | Banners','root | App Addons | SubSpace Continuum | Banners'),(430,591,592,'Bots','',1,NULL,'',1,0,3,'34,152,427,430','root | App Addons | SubSpace Continuum | Bots','root | App Addons | SubSpace Continuum | Bots'),(431,593,594,'Catids','',1,NULL,'',1,0,3,'34,152,427,431','root | App Addons | SubSpace Continuum | Catids','root | App Addons | SubSpace Continuum | Catids'),(432,595,596,'Clients','',1,NULL,'',1,0,3,'34,152,427,432','root | App Addons | SubSpace Continuum | Clients','root | App Addons | SubSpace Continuum | Clients'),(433,597,598,'Editors','',1,NULL,'',1,0,3,'34,152,427,433','root | App Addons | SubSpace Continuum | Editors','root | App Addons | SubSpace Continuum | Editors'),(434,599,600,'Fonts','',1,NULL,'',1,0,3,'34,152,427,434','root | App Addons | SubSpace Continuum | Fonts','root | App Addons | SubSpace Continuum | Fonts'),(435,601,602,'Graphics','',1,NULL,'',1,0,3,'34,152,427,435','root | App Addons | SubSpace Continuum | Graphics','root | App Addons | SubSpace Continuum | Graphics'),(436,603,604,'Images','',1,NULL,'',1,0,3,'34,152,427,436','root | App Addons | SubSpace Continuum | Images','root | App Addons | SubSpace Continuum | Images'),(437,605,606,'Misc','',1,NULL,'',1,0,3,'34,152,427,437','root | App Addons | SubSpace Continuum | Misc','root | App Addons | SubSpace Continuum | Misc'),(438,607,608,'Mods','',1,NULL,'',1,0,3,'34,152,427,438','root | App Addons | SubSpace Continuum | Mods','root | App Addons | SubSpace Continuum | Mods'),(439,609,610,'Server','',1,NULL,'',1,0,3,'34,152,427,439','root | App Addons | SubSpace Continuum | Server','root | App Addons | SubSpace Continuum | Server'),(440,611,612,'Skins','',1,NULL,'',1,0,3,'34,152,427,440','root | App Addons | SubSpace Continuum | Skins','root | App Addons | SubSpace Continuum | Skins'),(442,613,614,'SubspaceISO','',1,NULL,'',1,0,3,'34,152,427,442','root | App Addons | SubSpace Continuum | SubspaceISO','root | App Addons | SubSpace Continuum | SubspaceISO'),(443,615,616,'Zones','',1,NULL,'',1,0,3,'34,152,427,443','root | App Addons | SubSpace Continuum | Zones','root | App Addons | SubSpace Continuum | Zones'),(448,618,619,'GMusicbrowser Layouts','',1,NULL,'',1,1,2,'34,152,448','root | App Addons | GMusicbrowser Layouts','root | App Addons | GMusicbrowser Layouts'),(449,620,621,'Pidgin','',1,NULL,'',1,0,2,'34,152,449','root | App Addons | Pidgin','root | App Addons | Pidgin'),(467,622,623,'Kirocker','',1,NULL,'',1,1,2,'34,152,467','root | App Addons | Kirocker','root | App Addons | Kirocker'),(469,624,625,'Mixxx Skins','',1,NULL,'',1,1,2,'34,152,469','root | App Addons | Mixxx Skins','root | App Addons | Mixxx Skins'),(473,626,627,'aMSN','',1,NULL,'',1,1,2,'34,152,473','root | App Addons | aMSN','root | App Addons | aMSN'),(474,628,629,'Opera','',1,NULL,'',1,1,2,'34,152,474','root | App Addons | Opera','root | App Addons | Opera'),(478,630,631,'VIM','',1,NULL,'',1,1,2,'34,152,478','root | App Addons | VIM','root | App Addons | VIM'),(479,632,633,'Covergloobus','',1,NULL,'',1,1,2,'34,152,479','root | App Addons | Covergloobus','root | App Addons | Covergloobus'),(88,650,651,'Rock',NULL,1,0,NULL,1,0,1,'34,88','root | Rock','root | Rock'),(89,652,653,'Dance',NULL,1,0,NULL,1,0,1,'34,89','root | Dance','root | Dance'),(96,656,657,'Art',NULL,1,0,NULL,1,0,1,'34,96','root | Art','root | Art'),(92,658,659,'Health',NULL,1,0,NULL,1,0,1,'34,92','root | Health','root | Health'),(93,660,661,'Culture',NULL,1,0,NULL,1,0,1,'34,93','root | Culture','root | Culture'),(94,662,663,'Computer & Technology',NULL,1,0,NULL,1,0,1,'34,94','root | Computer & Technology','root | Computer & Technology'),(95,664,665,'News',NULL,1,0,NULL,1,0,1,'34,95','root | News','root | News'),(90,674,675,'Music',NULL,1,0,NULL,1,0,1,'34,90','root | Music','root | Music'),(91,676,677,'Fun',NULL,1,0,NULL,1,0,1,'34,91','root | Fun','root | Fun'),(158,681,700,'Art (Images/Drawings/Illustrations)','',1,0,'',1,0,1,'34,158','root | Art (Images/Drawings/Illustrations)','root | Art (Images/Drawings/Illustrations)'),(175,682,683,'Paintings',NULL,1,0,NULL,1,0,2,'34,158,175','root | Art (Images/Drawings/Illustrations) | Paintings','root | Art (Images/Drawings/Illustrations) | Paintings'),(174,684,685,'Cliparts',NULL,1,0,NULL,1,0,2,'34,158,174','root | Art (Images/Drawings/Illustrations) | Cliparts','root | Art (Images/Drawings/Illustrations) | Cliparts'),(159,686,687,'3D Renderings',NULL,1,0,NULL,1,0,2,'34,158,159','root | Art (Images/Drawings/Illustrations) | 3D Renderings','root | Art (Images/Drawings/Illustrations) | 3D Renderings'),(160,688,689,'Stock Images',NULL,1,0,NULL,1,0,2,'34,158,160','root | Art (Images/Drawings/Illustrations) | Stock Images','root | Art (Images/Drawings/Illustrations) | Stock Images'),(161,690,691,'Animations',NULL,1,0,NULL,1,0,2,'34,158,161','root | Art (Images/Drawings/Illustrations) | Animations','root | Art (Images/Drawings/Illustrations) | Animations'),(162,692,693,'Drawings',NULL,1,0,NULL,1,0,2,'34,158,162','root | Art (Images/Drawings/Illustrations) | Drawings','root | Art (Images/Drawings/Illustrations) | Drawings'),(182,694,695,'Various Artwork','',1,0,'',1,1,2,'34,158,182','root | Art (Images/Drawings/Illustrations) | Various Artwork','root | Art (Images/Drawings/Illustrations) | Various Artwork'),(367,696,697,'Fractals','',1,NULL,'',1,0,2,'34,158,367','root | Art (Images/Drawings/Illustrations) | Fractals','root | Art (Images/Drawings/Illustrations) | Fractals'),(468,698,699,'CD/DVD Labels','',1,NULL,'',1,0,2,'34,158,468','root | Art (Images/Drawings/Illustrations) | CD/DVD Labels','root | Art (Images/Drawings/Illustrations) | CD/DVD Labels'),(233,703,804,'Apps','',1,NULL,'',1,1,1,'34,233','root | Apps','root | Apps'),(239,704,705,'Graphics','',1,NULL,'bin',1,1,2,'34,233,239','root | Apps | Graphics','root | Apps | Graphics'),(6,706,721,'Games','',1,60,'',1,1,2,'34,233,6','root | Apps | Games','root | Apps | Games'),(206,707,708,'Games Other','',1,0,'bin',1,1,3,'34,233,6,206','root | Apps | Games | Games Other','root | Apps | Games | Games Other'),(205,709,710,'Tactics & Strategy','',1,0,'bin',1,1,3,'34,233,6,205','root | Apps | Games | Tactics & Strategy','root | Apps | Games | Tactics & Strategy'),(204,711,712,'Card','',1,0,'bin',1,1,3,'34,233,6,204','root | Apps | Games | Card','root | Apps | Games | Card'),(203,713,714,'Board','',1,0,'bin',1,1,3,'34,233,6,203','root | Apps | Games | Board','root | Apps | Games | Board'),(202,715,716,'Arcade','',1,0,'bin',1,1,3,'34,233,6,202','root | Apps | Games | Arcade','root | Apps | Games | Arcade'),(280,722,731,'Qt Other','',1,NULL,'',1,1,2,'34,233,280','root | Apps | Qt Other','root | Apps | Qt Other'),(255,723,724,'Qt Mobile','',1,NULL,'bin',1,1,3,'34,233,280,255','root | Apps | Qt Other | Qt Mobile','root | Apps | Qt Other | Qt Mobile'),(254,725,726,'Qt Stuff','',1,NULL,'bin',1,1,3,'34,233,280,254','root | Apps | Qt Other | Qt Stuff','root | Apps | Qt Other | Qt Stuff'),(253,727,728,'Qt Components','',1,NULL,'bin',1,1,3,'34,233,280,253','root | Apps | Qt Other | Qt Components','root | Apps | Qt Other | Qt Components'),(252,729,730,'Qt Widgets','',1,NULL,'bin',1,1,3,'34,233,280,252','root | Apps | Qt Other | Qt Widgets','root | Apps | Qt Other | Qt Widgets'),(234,732,733,'Database','',1,NULL,'bin',1,1,2,'34,233,234','root | Apps | Database','root | Apps | Database'),(243,734,735,'Development','',1,NULL,'bin',1,1,2,'34,233,243','root | Apps | Development','root | Apps | Development'),(244,736,737,'Utilities','',1,NULL,'bin',1,1,2,'34,233,244','root | Apps | Utilities','root | Apps | Utilities'),(245,738,739,'System Software','',1,NULL,'bin',1,1,2,'34,233,245','root | Apps | System Software','root | Apps | System Software'),(376,740,741,'Social','',1,NULL,'bin',1,1,2,'34,233,376','root | Apps | Social','root | Apps | Social'),(387,742,747,'Communication','',1,NULL,'',1,1,2,'34,233,387','root | Apps | Communication','root | Apps | Communication'),(242,743,744,'Telephony','',1,NULL,'bin',1,1,3,'34,233,387,242','root | Apps | Communication | Telephony','root | Apps | Communication | Telephony'),(250,745,746,'Chat','',1,NULL,'bin',1,1,3,'34,233,387,250','root | Apps | Communication | Chat','root | Apps | Communication | Chat'),(388,748,753,'Science & Education','',1,NULL,'',1,1,2,'34,233,388','root | Apps | Science & Education','root | Apps | Science & Education'),(247,749,750,'Science','',1,NULL,'bin',1,1,3,'34,233,388,247','root | Apps | Science & Education | Science','root | Apps | Science & Education | Science'),(241,751,752,'Education','',1,NULL,'bin',1,1,3,'34,233,388,241','root | Apps | Science & Education | Education','root | Apps | Science & Education | Education'),(389,754,759,'Web & Email','',1,NULL,'',1,1,2,'34,233,389','root | Apps | Web & Email','root | Apps | Web & Email'),(249,755,756,'Email','',1,NULL,'bin',1,1,3,'34,233,389,249','root | Apps | Web & Email | Email','root | Apps | Web & Email | Email'),(248,757,758,'Web','',1,NULL,'bin',1,1,3,'34,233,389,248','root | Apps | Web & Email | Web','root | Apps | Web & Email | Web'),(391,762,767,'Network & Security','',1,NULL,'',1,1,2,'34,233,391','root | Apps | Network & Security','root | Apps | Network & Security'),(251,763,764,'Network','',1,NULL,'bin',1,1,3,'34,233,391,251','root | Apps | Network & Security | Network','root | Apps | Network & Security | Network'),(246,765,766,'Security','',1,NULL,'bin',1,1,3,'34,233,391,246','root | Apps | Network & Security | Security','root | Apps | Network & Security | Security'),(392,768,781,'Audio','',1,NULL,'',1,1,2,'34,233,392','root | Apps | Audio','root | Apps | Audio'),(237,769,770,'Audio','',1,NULL,'bin',1,1,3,'34,233,392,237','root | Apps | Audio | Audio','root | Apps | Audio | Audio'),(393,771,772,'Audioplayers','',1,NULL,'bin',1,1,3,'34,233,392,393','root | Apps | Audio | Audioplayers','root | Apps | Audio | Audioplayers'),(394,773,774,'Music Production','',1,NULL,'bin',1,1,3,'34,233,392,394','root | Apps | Audio | Music Production','root | Apps | Audio | Music Production'),(395,775,776,'Radio','',1,NULL,'bin',1,1,3,'34,233,392,395','root | Apps | Audio | Radio','root | Apps | Audio | Radio'),(396,777,778,'MP3 Taggers','',1,NULL,'bin',1,1,3,'34,233,392,396','root | Apps | Audio | MP3 Taggers','root | Apps | Audio | MP3 Taggers'),(397,779,780,'Audio Extractors/Converters','',1,NULL,'bin',1,1,3,'34,233,392,397','root | Apps | Audio | Audio Extractors/Converters','root | Apps | Audio | Audio Extractors/Converters'),(450,782,791,'Productivity','',1,NULL,'',1,1,2,'34,233,450','root | Apps | Productivity','root | Apps | Productivity'),(222,783,784,'Office','',1,NULL,'bin',1,1,3,'34,233,450,222','root | Apps | Productivity | Office','root | Apps | Productivity | Office'),(235,785,786,'Financial','',1,NULL,'bin',1,1,3,'34,233,450,235','root | Apps | Productivity | Financial','root | Apps | Productivity | Financial'),(236,787,788,'Groupware','',1,NULL,'bin',1,1,3,'34,233,450,236','root | Apps | Productivity | Groupware','root | Apps | Productivity | Groupware'),(240,789,790,'Text Editors','',1,NULL,'bin',1,1,3,'34,233,450,240','root | Apps | Productivity | Text Editors','root | Apps | Productivity | Text Editors'),(481,792,803,'Video','',1,NULL,'',1,1,2,'34,233,481','root | Apps | Video','root | Apps | Video'),(238,793,794,'Video','',1,NULL,'bin',1,1,3,'34,233,481,238','root | Apps | Video | Video','root | Apps | Video | Video'),(482,795,796,'Video Players','',1,NULL,'bin',1,1,3,'34,233,481,482','root | Apps | Video | Video Players','root | Apps | Video | Video Players'),(483,797,798,'Video Production','',1,NULL,'bin',1,1,3,'34,233,481,483','root | Apps | Video | Video Production','root | Apps | Video | Video Production'),(484,799,800,'Video Converter','',1,NULL,'bin',1,1,3,'34,233,481,484','root | Apps | Video | Video Converter','root | Apps | Video | Video Converter'),(485,801,802,'TV & Streaming','',1,NULL,'bin',1,1,3,'34,233,481,485','root | Apps | Video | TV & Streaming','root | Apps | Video | TV & Streaming'),(295,805,888,'Wallpapers',NULL,1,NULL,NULL,1,0,1,'34,295','root | Wallpapers','root | Wallpapers'),(261,806,861,'OS specific','',1,NULL,'',1,0,2,'34,295,261','root | Wallpapers | OS specific','root | Wallpapers | OS specific'),(283,807,808,'Wallpapers Mint','',1,NULL,'wallpapers',1,0,3,'34,295,261,283','root | Wallpapers | OS specific | Wallpapers Mint','root | Wallpapers | OS specific | Wallpapers Mint'),(286,809,810,'Wallpapers Ubuntu','',1,NULL,'wallpapers',1,0,3,'34,295,261,286','root | Wallpapers | OS specific | Wallpapers Ubuntu','root | Wallpapers | OS specific | Wallpapers Ubuntu'),(287,811,812,'Wallpapers Kubuntu','',1,NULL,'wallpapers',1,0,3,'34,295,261,287','root | Wallpapers | OS specific | Wallpapers Kubuntu','root | Wallpapers | OS specific | Wallpapers Kubuntu'),(288,813,814,'Wallpapers SUSE','',1,NULL,'wallpapers',1,0,3,'34,295,261,288','root | Wallpapers | OS specific | Wallpapers SUSE','root | Wallpapers | OS specific | Wallpapers SUSE'),(289,815,816,'Wallpapers Arch','',1,NULL,'wallpapers',1,0,3,'34,295,261,289','root | Wallpapers | OS specific | Wallpapers Arch','root | Wallpapers | OS specific | Wallpapers Arch'),(290,817,818,'Wallpapers Fedora','',1,NULL,'wallpapers',1,0,3,'34,295,261,290','root | Wallpapers | OS specific | Wallpapers Fedora','root | Wallpapers | OS specific | Wallpapers Fedora'),(291,819,820,'Wallpapers Mandriva','',1,NULL,'wallpapers',1,0,3,'34,295,261,291','root | Wallpapers | OS specific | Wallpapers Mandriva','root | Wallpapers | OS specific | Wallpapers Mandriva'),(292,821,822,'Wallpapers Gentoo','',1,NULL,'wallpapers',1,0,3,'34,295,261,292','root | Wallpapers | OS specific | Wallpapers Gentoo','root | Wallpapers | OS specific | Wallpapers Gentoo'),(293,823,824,'Wallpapers Frugalware','',1,NULL,'wallpapers',1,0,3,'34,295,261,293','root | Wallpapers | OS specific | Wallpapers Frugalware','root | Wallpapers | OS specific | Wallpapers Frugalware'),(299,825,826,'Wallpapers KDE Plasma','KDE Wallpaper (other)',1,NULL,'wallpapers',1,0,3,'34,295,261,299','root | Wallpapers | OS specific | Wallpapers KDE Plasma','root | Wallpapers | OS specific | KDE Wallpaper (other)'),(300,827,828,'Wallpapers Gnome','',1,NULL,'wallpapers',1,0,3,'34,295,261,300','root | Wallpapers | OS specific | Wallpapers Gnome','root | Wallpapers | OS specific | Wallpapers Gnome'),(302,829,830,'Wallpapers XFCE/Xubuntu','',1,NULL,'wallpapers',1,0,3,'34,295,261,302','root | Wallpapers | OS specific | Wallpapers XFCE/Xubuntu','root | Wallpapers | OS specific | Wallpapers XFCE/Xubuntu'),(303,831,832,'Wallpapers Debian','',1,NULL,'wallpapers',1,0,3,'34,295,261,303','root | Wallpapers | OS specific | Wallpapers Debian','root | Wallpapers | OS specific | Wallpapers Debian'),(309,833,834,'Wallpapers Manjaro','',1,NULL,'wallpapers',1,0,3,'34,295,261,309','root | Wallpapers | OS specific | Wallpapers Manjaro','root | Wallpapers | OS specific | Wallpapers Manjaro'),(310,835,836,'Wallpapers Firefox','',1,NULL,'wallpapers',1,0,3,'34,295,261,310','root | Wallpapers | OS specific | Wallpapers Firefox','root | Wallpapers | OS specific | Wallpapers Firefox'),(311,837,838,'Wallpapers Windows','',1,NULL,'wallpapers',1,0,3,'34,295,261,311','root | Wallpapers | OS specific | Wallpapers Windows','root | Wallpapers | OS specific | Wallpapers Windows'),(312,839,840,'Wallpapers OSX/Apple','',1,NULL,'wallpapers',1,0,3,'34,295,261,312','root | Wallpapers | OS specific | Wallpapers OSX/Apple','root | Wallpapers | OS specific | Wallpapers OSX/Apple'),(314,841,842,'Wallpapers BSD','',1,NULL,'wallpapers',1,0,3,'34,295,261,314','root | Wallpapers | OS specific | Wallpapers BSD','root | Wallpapers | OS specific | Wallpapers BSD'),(350,843,844,'Wallpapers PCLinuxOS','',1,NULL,'wallpapers',1,0,3,'34,295,261,350','root | Wallpapers | OS specific | Wallpapers PCLinuxOS','root | Wallpapers | OS specific | Wallpapers PCLinuxOS'),(358,845,846,'Wallpapers Mageia','',1,NULL,'wallpapers',1,0,3,'34,295,261,358','root | Wallpapers | OS specific | Wallpapers Mageia','root | Wallpapers | OS specific | Wallpapers Mageia'),(359,847,848,'Wallpapers MATE','',1,NULL,'wallpapers',1,0,3,'34,295,261,359','root | Wallpapers | OS specific | Wallpapers MATE','root | Wallpapers | OS specific | Wallpapers MATE'),(360,849,850,'Wallpapers Linux/Tux','KDE Wallpaper 800x600',1,NULL,'wallpapers',1,0,3,'34,295,261,360','root | Wallpapers | OS specific | Wallpapers Linux/Tux','root | Wallpapers | OS specific | KDE Wallpaper 800x600'),(361,851,852,'Wallpapers Solus','',1,NULL,'wallpapers',1,0,3,'34,295,261,361','root | Wallpapers | OS specific | Wallpapers Solus','root | Wallpapers | OS specific | Wallpapers Solus'),(362,853,854,'Wallpapers GNU','',1,NULL,'wallpapers',1,0,3,'34,295,261,362','root | Wallpapers | OS specific | Wallpapers GNU','root | Wallpapers | OS specific | Wallpapers GNU'),(374,855,856,'Wallpapers Zorin','',1,NULL,'wallpapers',1,0,3,'34,295,261,374','root | Wallpapers | OS specific | Wallpapers Zorin','root | Wallpapers | OS specific | Wallpapers Zorin'),(400,857,858,'Wallpapers LXQt/LXDE','',1,NULL,'wallpapers',1,0,3,'34,295,261,400','root | Wallpapers | OS specific | Wallpapers LXQt/LXDE','root | Wallpapers | OS specific | Wallpapers LXQt/LXDE'),(451,859,860,'Wallpapers Deepin','',1,NULL,'wallpapers',1,0,3,'34,295,261,451','root | Wallpapers | OS specific | Wallpapers Deepin','root | Wallpapers | OS specific | Wallpapers Deepin'),(58,862,863,'Wallpaper Other','',1,0,'wallpapers',1,0,2,'34,295,58','root | Wallpapers | Wallpaper Other','root | Wallpapers | Wallpaper Other'),(296,864,865,'Abstract','KDE Wallpaper 1024x768',1,NULL,'wallpapers',1,0,2,'34,295,296','root | Wallpapers | Abstract','root | Wallpapers | KDE Wallpaper 1024x768'),(297,866,867,'Animals','KDE Wallpaper 1280x1024',1,NULL,'wallpapers',1,0,2,'34,295,297','root | Wallpapers | Animals','root | Wallpapers | KDE Wallpaper 1280x1024'),(298,868,869,'Nature','KDE Wallpaper 1440x900',1,NULL,'wallpapers',1,0,2,'34,295,298','root | Wallpapers | Nature','root | Wallpapers | KDE Wallpaper 1440x900'),(301,870,871,'People','',1,NULL,'wallpapers',1,0,2,'34,295,301','root | Wallpapers | People','root | Wallpapers | People'),(304,872,873,'Buildings','',1,NULL,'wallpapers',1,0,2,'34,295,304','root | Wallpapers | Buildings','root | Wallpapers | Buildings'),(305,874,875,'Landscapes','',1,NULL,'wallpapers',1,0,2,'34,295,305','root | Wallpapers | Landscapes','root | Wallpapers | Landscapes'),(306,876,877,'Mountains','',1,NULL,'wallpapers',1,0,2,'34,295,306','root | Wallpapers | Mountains','root | Wallpapers | Mountains'),(307,878,879,'Beaches and Oceans','KDE Wallpaper 1600x1200',1,NULL,'wallpapers',1,0,2,'34,295,307','root | Wallpapers | Beaches and Oceans','root | Wallpapers | KDE Wallpaper 1600x1200'),(308,880,881,'Bridges','',1,NULL,'wallpapers',1,0,2,'34,295,308','root | Wallpapers | Bridges','root | Wallpapers | Bridges'),(313,882,883,'Manga and Anime','',1,NULL,'wallpapers',1,0,2,'34,295,313','root | Wallpapers | Manga and Anime','root | Wallpapers | Manga and Anime'),(401,884,885,'Cars','',1,NULL,'wallpapers',1,0,2,'34,295,401','root | Wallpapers | Cars','root | Wallpapers | Cars'),(444,886,887,'Mobile Phones','',1,NULL,'wallpapers',1,0,2,'34,295,444','root | Wallpapers | Mobile Phones','root | Wallpapers | Mobile Phones'),(330,889,892,'Comics','',1,NULL,'',1,0,1,'34,330','root | Comics','root | Comics'),(39,890,891,'Comics','Comics',1,0,'',1,0,2,'34,330,39','root | Comics | Comics','root | Comics | Comics'),(403,897,898,'Tutorials','',1,NULL,'',1,1,1,'34,403','root | Tutorials','root | Tutorials'),(404,899,900,'Distros','',1,NULL,'',0.1,1,1,'34,404','root | Distros','root | Distros'),(445,901,902,'UI Concepts','',1,NULL,'',1,1,1,'34,445','root | UI Concepts','root | UI Concepts'),(466,903,904,'Telephone UI','',1,NULL,'',1,1,1,'34,466','root | Telephone UI','root | Telephone UI');
/*!40000 ALTER TABLE `stat_cat_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_daily`
--

DROP TABLE IF EXISTS `stat_daily`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_daily` (
  `daily_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `project_id` int(11) NOT NULL COMMENT 'ID of the project',
  `project_category_id` int(11) DEFAULT '0' COMMENT 'Category',
  `project_type_id` int(11) NOT NULL COMMENT 'type of the project',
  `count_views` int(11) DEFAULT '0',
  `count_plings` int(11) DEFAULT '0',
  `count_updates` int(11) DEFAULT NULL,
  `count_comments` int(11) DEFAULT NULL,
  `count_followers` int(11) DEFAULT NULL,
  `count_supporters` int(11) DEFAULT NULL,
  `count_money` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `year` int(11) DEFAULT NULL COMMENT 'z.B.: 1988',
  `month` int(11) DEFAULT NULL COMMENT 'z.b: 1-12',
  `day` int(11) DEFAULT NULL COMMENT 'z.B. 1-31',
  `year_week` int(11) DEFAULT NULL COMMENT 'z.b.: 201232',
  `ranking_value` float DEFAULT NULL,
  PRIMARY KEY (`daily_id`),
  KEY `indexKeys` (`project_id`,`project_category_id`,`project_type_id`),
  KEY `project_id` (`project_id`),
  KEY `project_category_id` (`project_category_id`),
  KEY `project_type_id` (`project_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Store daily statistic';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stat_daily`
--

LOCK TABLES `stat_daily` WRITE;
/*!40000 ALTER TABLE `stat_daily` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_daily` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_daily_pageviews`
--

DROP TABLE IF EXISTS `stat_daily_pageviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_daily_pageviews` (
  `project_id` int(11) NOT NULL COMMENT 'ID of the project',
  `cnt` int(11) DEFAULT NULL,
  `project_category_id` int(11) NOT NULL,
  `created_at` date DEFAULT NULL,
  KEY `idxProjectId` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stat_daily_pageviews`
--

LOCK TABLES `stat_daily_pageviews` WRITE;
/*!40000 ALTER TABLE `stat_daily_pageviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_daily_pageviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_downloads_half_year`
--

DROP TABLE IF EXISTS `stat_downloads_half_year`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_downloads_half_year` (
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
-- Dumping data for table `stat_downloads_half_year`
--

LOCK TABLES `stat_downloads_half_year` WRITE;
/*!40000 ALTER TABLE `stat_downloads_half_year` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_downloads_half_year` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `stat_downloads_quarter_year`
--

LOCK TABLES `stat_downloads_quarter_year` WRITE;
/*!40000 ALTER TABLE `stat_downloads_quarter_year` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_downloads_quarter_year` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='Counter of project-page views';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stat_page_views`
--

LOCK TABLES `stat_page_views` WRITE;
/*!40000 ALTER TABLE `stat_page_views` DISABLE KEYS */;
INSERT INTO `stat_page_views` VALUES (1,1170226,'2018-05-25 09:48:17','192.168.178.44',24),(2,1170226,'2018-05-25 09:54:52','192.168.178.44',24),(3,1170226,'2018-05-25 10:31:48','192.168.178.44',24),(4,1209930,'2018-05-28 17:40:52','192.168.178.44',24),(5,1209928,'2018-05-28 17:41:13','192.168.178.44',24),(6,1209925,'2018-05-28 17:41:27','192.168.178.44',24);
/*!40000 ALTER TABLE `stat_page_views` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `stat_page_views_mv`
--

LOCK TABLES `stat_page_views_mv` WRITE;
/*!40000 ALTER TABLE `stat_page_views_mv` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_page_views_mv` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stat_page_views_today_mv`
--

LOCK TABLES `stat_page_views_today_mv` WRITE;
/*!40000 ALTER TABLE `stat_page_views_today_mv` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_page_views_today_mv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `stat_plings`
--

DROP TABLE IF EXISTS `stat_plings`;
/*!50001 DROP VIEW IF EXISTS `stat_plings`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `stat_plings` (
  `project_id` tinyint NOT NULL,
  `amount_received` tinyint NOT NULL,
  `count_plings` tinyint NOT NULL,
  `count_plingers` tinyint NOT NULL,
  `latest_pling` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `stat_projects`
--

DROP TABLE IF EXISTS `stat_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_projects` (
  `project_id` int(11) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL DEFAULT '0',
  `content_type` varchar(255) NOT NULL DEFAULT 'text',
  `project_category_id` int(11) NOT NULL DEFAULT '0',
  `hive_category_id` int(11) NOT NULL DEFAULT '0',
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
  `deleted_at` datetime DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL COMMENT 'Member_id of the creator. Important for groups.',
  `facebook_code` text,
  `source_url` text,
  `twitter_code` text,
  `google_code` text,
  `link_1` text,
  `embed_code` text,
  `ppload_collection_id` varchar(255) DEFAULT NULL,
  `validated` int(1) DEFAULT '0',
  `validated_at` datetime DEFAULT NULL,
  `featured` int(1) DEFAULT '0',
  `ghns_excluded` int(1) DEFAULT '0',
  `amount` int(11) DEFAULT NULL,
  `amount_period` varchar(45) DEFAULT NULL,
  `claimable` int(1) DEFAULT NULL,
  `claimed_by_member` int(11) DEFAULT NULL,
  `count_likes` int(11) DEFAULT '0',
  `count_dislikes` int(11) DEFAULT '0',
  `count_comments` int(11) DEFAULT '0',
  `count_downloads_hive` int(11) DEFAULT '0',
  `source_id` int(11) DEFAULT '0',
  `source_pk` int(11) DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `project_validated` int(1) DEFAULT '0',
  `project_uuid` varchar(255) DEFAULT NULL,
  `project_status` int(11) NOT NULL DEFAULT '0',
  `project_created_at` datetime DEFAULT NULL,
  `project_changed_at` datetime DEFAULT NULL,
  `laplace_score` int(11) DEFAULT NULL,
  `member_type` int(1) NOT NULL DEFAULT '0' COMMENT 'Type: 0 = Member, 1 = group',
  `project_member_id` int(10) NOT NULL DEFAULT '0',
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `profile_image_url` varchar(355) DEFAULT '/images/system/default-profile.png' COMMENT 'URL to the profile-image',
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `member_created_at` datetime DEFAULT NULL,
  `paypal_mail` varchar(255) DEFAULT NULL,
  `cat_title` varchar(100) NOT NULL,
  `cat_xdg_type` varchar(50) DEFAULT NULL,
  `cat_name_legacy` varchar(50) DEFAULT NULL,
  `cat_show_description` int(1) NOT NULL DEFAULT '0',
  `amount_received` double(19,2) DEFAULT NULL,
  `count_plings` bigint(21) DEFAULT '0',
  `count_plingers` bigint(21) DEFAULT '0',
  `latest_pling` timestamp NULL DEFAULT NULL COMMENT 'When did paypal say, that this pling was payed successfull',
  `amount_reports` bigint(21),
  `package_types` text CHARACTER SET utf8mb4,
  `package_names` text CHARACTER SET latin1,
  `tags` text CHARACTER SET latin1,
  `count_downloads_quarter` bigint(21) DEFAULT '0',
  `project_license_title` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`project_id`),
  KEY `idx_cat` (`project_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stat_projects`
--

LOCK TABLES `stat_projects` WRITE;
/*!40000 ALTER TABLE `stat_projects` DISABLE KEYS */;
INSERT INTO `stat_projects` VALUES (1170170,24,'text',203,0,100,'d8c6f1ce624d4f789096a0b47f951a24',NULL,1,'Kblocks Snappy','Snap Image for KBlocks','',NULL,NULL,'8/1/c/7/32d154ef84a55bed224a4e45d990c4e074b4.png',NULL,NULL,'2017-02-06 12:19:21','2018-05-24 17:29:28',NULL,24,'','','','','','','1486401592',0,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,0,NULL,NULL,0,'d8c6f1ce624d4f789096a0b47f951a24',100,'2017-02-06 12:19:21','2018-05-24 17:29:28',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','Board','bin','',1,NULL,NULL,NULL,NULL,NULL,'6','Snappy',NULL,NULL,NULL),(1170226,24,'text',205,0,100,'8d0c4f9c5d5e4c198b2ecaf9caffaaa3',NULL,1,'KAtomic Snap','KAtomic KDE Game','',NULL,NULL,'b/f/d/8/f9c7cd91be9773c064f505ca6ed4a06aa4b5.png',NULL,NULL,'2017-02-07 08:51:48','2018-05-24 17:29:28',NULL,24,'','','','','','','1486475516',0,NULL,0,0,0,NULL,NULL,NULL,2,0,NULL,0,0,NULL,NULL,0,'8d0c4f9c5d5e4c198b2ecaf9caffaaa3',100,'2017-02-07 08:51:48','2018-05-24 17:29:28',57,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','Tactics & Strategy','bin','',1,NULL,NULL,NULL,NULL,NULL,'6,7,1','Snappy,Flatpak,AppImage',NULL,NULL,NULL),(1209922,24,'text',58,0,100,'418fd506065345ad92c6aa9b66e33ee6',NULL,1,'Minimal Material Solus Wallpapers','Minimal Material wallpapers for the infamous Solus OS! Day and night versions and a version to look great with the ever popular Adapta theme.\r\nAll wallpapers are 3840 x 2160.\r\nAs always, requests are more that welcome! Give me a shout in the comments or G+. http://google.com/+KarlSchneider1','',NULL,NULL,'9/4/1/c/087f0f78c922c82a48e72685129c0dea8b61.png',NULL,NULL,'2018-05-28 18:08:45','2018-05-28 18:08:45',NULL,24,'','','','','','',NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'418fd506065345ad92c6aa9b66e33ee6',100,'2018-05-28 18:08:45','2018-05-28 18:08:45',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','Wallpaper Other','wallpapers','',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'4k,adapta,linux,material,solus',NULL,NULL),(1209923,24,'text',445,0,100,'3d41b489e3b44e3f80d6f8ddb7b47ee0',NULL,1,'Simple kmenu','idea simple menu for kde inspired by the android menu','',NULL,NULL,'b/d/7/2/0728b9d9dc4b7d1bcb8bcfa7e7b9e14eb9c4.png',NULL,NULL,'2018-05-28 19:12:10','2018-05-28 19:12:10',NULL,24,'','','','','','',NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'3d41b489e3b44e3f80d6f8ddb7b47ee0',100,'2018-05-28 19:12:10','2018-05-28 19:12:10',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','UI Concepts','','',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1209924,24,'text',466,0,100,'e89ea324030e49d9b6445572e8ad25af',NULL,1,'Mobicaching','Mobicaching is a Symbian application dedicated to geocachers.\r\n\r\nCurrently downloads data from:\r\n- Opencaching.nl\r\n- Opencaching.pl\r\n- Opencaching.org.uk\r\n- Opencaching.us\r\n- Opencaching.com\r\n\r\nGeocaches, with their logs and images, may be saved in device\\\\\\\'s memory so there is no need to maintain network connection. There is also possibility to save geocache position as landmark in Nokia Maps.','',NULL,NULL,'2/a/a/9/3c23c0f8fc2cacec64ca508671baef53713b.jpg',NULL,NULL,'2018-05-28 19:15:43','2018-05-28 19:15:43',NULL,24,'','','','','','',NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'e89ea324030e49d9b6445572e8ad25af',100,'2018-05-28 19:15:43','2018-05-28 19:15:43',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','Telephone UI','','',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1209925,24,'text',135,0,100,'0fe8e708989745369dbb46670ce7694f',NULL,1,'Dark-Side','Dark-Side\r\n\r\nOpen image in a new tab and maximize to get a decent view\r\n\r\nDArk-Side is a true dark theme, and comes in all colors as long it is black...\r\n\r\nPlease, try and rate, comment. Any input or criticism is welcome.\r\n\r\nRequirements:\r\n\r\nGTK+ 3.20 or later. Only standard (or Ubuntu) gnome-desktop is supported. Ready for Ubuntu 18.04.\r\n\r\nGTK2 ENGINES REQUIREMENT\r\n\r\n- GTK2 engine Murrine\r\n- GTK2 engine Pixbuf\r\n\r\nFedora/RedHat distros:\r\nyum install gtk-murrine-engine gtk2-engines\r\n\r\nUbuntu/Mint/Debian distros:\r\nsudo apt-get install gtk2-engines-murrine gtk2-engines-pixbuf\r\n\r\nArchLinux:\r\npacman -S gtk-engine-murrine gtk-engines\r\n\r\nWhat to do:\r\n\r\nExtract and put it into the themes directory i.e. ~/.themes/ or /usr/share/themes/ (create it if necessary).Then change the theme via distribution specific tool like Gnome tweak tool or Unity tweak tool, etc. (If you use Snap-packages instead of app\'s from the normal repositories than definitely put the theme to /usr/share/themes/.\r\n\r\nThis theme is based on the dark-theme of the Arc theme, by Horst 3180, under license GPLv3.\r\nhttps://github.com/horst3180/arc-theme','',NULL,NULL,'6/2/5/6/ee5926f842802027dcd64b11587b565e391e.jpg',NULL,NULL,'2018-05-28 19:17:37','2018-05-28 19:17:37',NULL,24,'','','','','','',NULL,0,NULL,1,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'0fe8e708989745369dbb46670ce7694f',100,'2018-05-28 19:17:37','2018-05-28 19:17:37',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','GTK3 Themes','gtk3_themes','GTK 3.x Theme/Style',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1209926,24,'text',174,0,100,'1f88b68472054b1b9d297bffd794215c',NULL,1,'KDE/KaOS 002 Dark Gray Menu Button','KDE/KaOS 002 Dark Gray Menu Button is a great replacement of your present option\r\n\r\nDon\'t be shy, give it a try, hesitation isn\'t good for you, trust me, I\'m a doctor ;-). I believe in you capability to download and set it up, after all what have you got to lose, if you don\'t like it you can always bin it and once you change your mind, bring it back.\r\n\r\nIMPORTANT NOTE: If you voted out of favour because you had a bad day or acted upon feeling like a spoiled toddler, be aware that I consider it as indeed very immature and silly. On contrary, if you liked what you got, definitely feel free to vote + and possibly point others to the product site, you will actually do well if you do this, unlike most who in fact can\'t do anything else than express negativity because they think it matters.','',NULL,NULL,'7/1/1/d/dd5d40f3e3143a5a9209d1fba0223537b843.png',NULL,NULL,'2018-05-28 19:19:41','2018-05-28 19:19:41',NULL,24,'','','','','','',NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'1f88b68472054b1b9d297bffd794215c',100,'2018-05-28 19:19:41','2018-05-28 19:19:41',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','Cliparts',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1209927,24,'text',485,0,100,'2d102a6971f44025b347c69c93720e70',NULL,1,'Helia ( Gtv-Dvb )','Digital TV\r\n* DVB-T2/S2/C, ATSC, DTMB\r\nMedia Player\r\n* IPTV\r\n\r\nGraphical user interface - Gtk+3\r\nAudio & Video & Digital TV - Gstreamer 1.0\r\n\r\nDrag and Drop\r\n* folders\r\n* files\r\n* playlists - M3U, M3U8\r\n\r\nChannels\r\n* scan channels manually\r\n* scan initial file\r\n* convert - dvb_channel.conf ( dvbv5-scan {OPTION...} --output-format=DVBV5 initial file )','',NULL,NULL,'d/3/c/0/38d2e898e2d56b5b13ef2c59108bef4b9044.png',NULL,NULL,'2018-05-28 19:21:57','2018-05-28 19:21:57',NULL,24,'','','','','','',NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'2d102a6971f44025b347c69c93720e70',100,'2018-05-28 19:21:57','2018-05-28 19:21:57',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','TV & Streaming','bin','',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'lgplv3,app,software',NULL,NULL),(1209928,24,'text',404,0,100,'932e83220e3b4415a6dce6b46af01aba',NULL,1,'EQuilibrium Level Three','GNU/Linux distributions EQuilibrium \"Level Three\" New Experience - beta\r\nBrings a whole new experience ... a modern, stable and fast GNU/Linux distribution.\r\nEQuilibrium is designed to suit more to portable computers (such as laptops, ultrabooks etc.) and computers with smaller monitors (up to 19\"). Made is for the people as who seek for a stable operating system, and spend a lot of time on the Internet.\r\n0 comments','',NULL,NULL,'6/e/e/b/3572f33092ba0b8c92b4b79db23cd182b399.png',NULL,NULL,'2018-05-28 19:23:57','2018-05-28 19:23:57',NULL,24,'','','','','','',NULL,0,NULL,1,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'932e83220e3b4415a6dce6b46af01aba',100,'2018-05-28 19:23:57','2018-05-28 19:23:57',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','Distros','','',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1209929,24,'text',324,0,100,'4af51873cb614bc7a1de0a73209099a2',NULL,1,'Twitch.tv playlist parser','Twitch.tv playlist parser\r\n\r\nInstall:\r\n1. I have included a client_id in this script. If it gets blocked in the future, you can generate your own client at https://www.twitch.tv/settings/connections and put it in the file.\r\n2. Put the file in the lua/playlist/ directory:\r\n- On Windows: %APPDATA%/vlc/lua/playlist/\r\n- On Mac: $HOME/Library/Application Support/org.videolan.vlc/lua/playlist/\r\n- On Linux: ~/.local/share/vlc/lua/playlist/\r\n- On Linux (snap package): ~/snap/vlc/current/.local/share/vlc/lua/playlist/\r\nTo install the addon for all users, put the file here instead:\r\n- On Windows: C:/Program Files (x86)/VideoLAN/VLC/lua/playlist/\r\n- On Mac: /Applications/VLC.app/Contents/MacOS/share/lua/playlist/\r\n- On Linux: /usr/lib/vlc/lua/playlist/\r\n- On Linux (snap package): /snap/vlc/current/usr/lib/vlc/lua/playlist/\r\n3. Open a twitch.tv url using \"Open Network Stream...\"\r\n\r\nIf you are using a Mac and have Homebrew installed, you can download and install with one Terminal command:\r\nbrew install --no-sandbox --HEAD stefansundin/tap/vlc-twitch\r\n\r\nIf you are using a Mac without Homebrew, you can still install by running:\r\nmkdir -p \"$HOME/Library/Application Support/org.videolan.vlc/lua/playlist/twitch.lua\"\r\ncurl -o \"$HOME/Library/Application Support/org.videolan.vlc/lua/playlist/twitch.lua\" https://gist.githubusercontent.com/stefansundin/c200324149bb00001fef5a252a120fc2/raw/twitch.lua\r\n\r\nOn Linux, you can download and install by running:\r\nmkdir -p ~/.local/share/vlc/lua/playlist/\r\ncurl -o ~/.local/share/vlc/lua/playlist/twitch.lua https://gist.githubusercontent.com/stefansundin/c200324149bb00001fef5a252a120fc2/raw/twitch.lua\r\n\r\nFeatures:\r\n- Load up a channel and watch live, e.g.: https://www.twitch.tv/speedgaming\r\n- Load an archived video, e.g.: https://www.twitch.tv/videos/113837699\r\n- Load a collection, e.g.: https://www.twitch.tv/videos/137244955?collection=JAFNfSvAtxS25w\r\n- Load a game and get the top streams, e.g.: https://www.twitch.tv/directory/game/Minecraft\r\n- Load a game\'s archived videos, e.g.: https://www.twitch.tv/directory/game/Minecraft/videos/all\r\n- Load a community and get the top streams, e.g.: https://www.twitch.tv/communities/speedrunning\r\n- Load a channel\'s most recent videos, e.g.: https://www.twitch.tv/speedgaming/videos/all\r\n- Load the homepage and get a list of featured streams: https://www.twitch.tv/\r\n- Load Twitch Clips, e.g.: https://clips.twitch.tv/AmazonianKnottyLapwingSwiftRage\r\n- Load a channel\'s clips, e.g.: https://www.twitch.tv/speedgaming/clips\r\n- Load a game\'s clips, e.g.: https://www.twitch.tv/directory/game/Minecraft/clips\r\n- Load the next page.\r\n\r\nIf you are experiencing issues (e.g. seeking), make sure that you are using VLC 3.0. You can also try nightlies: https://nightlies.videolan.org/\r\n\r\nIn order to load VODs with a timestamp in the url (e.g. ?t=1h10m10s), then you must also install the Twitch.tv extension from here: https://gist.githubusercontent.com/stefansundin/c200324149bb00001fef5a252a120fc2/raw/twitch-extension.lua\r\nNote that this extension must be activated in the VLC menu each time VLC is started (if you know of a workaround for this, please let me know in the comments below).\r\n\r\nIf you like this addon, please click the [+] in the top right corner. If you have any issues, please report them in the comments below. Thank you!\r\n\r\nNote: I expect this addon to stop working on Dec. 31, 2018. This is because API v3 will be deprecated at that time. I am not sure it will be possible to fix, but I will try my best.\r\n\r\nEnjoy!!','',NULL,NULL,'5/8/d/5/176b0d3de4744f317075901082d47ebdd70c.png',NULL,NULL,'2018-05-28 19:25:13','2018-05-28 19:25:13',NULL,24,'','','','','','',NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'4af51873cb614bc7a1de0a73209099a2',100,'2018-05-28 19:25:13','2018-05-28 19:25:13',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','VLC Playlist Parsers','','VLC Playlist Parser',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1209930,24,'text',132,0,100,'2c456a3325594efa97a6363f6f776046',NULL,1,'Flat Remix icon theme','Flat Remix icon theme is licensed under the GNU General Public License v3.0\r\n\r\n# New Folder Color support:\r\nhttp://foldercolor.tuxfamily.org\r\n\r\n\r\n## Please up vote or press the plus sign to show your support.\r\n\r\n\r\n# Web Page: https://drasite.com/flat-remix\r\n# Github: https://github.com/daniruiz/Flat-Remix\r\n\r\nFlat remix is a pretty simple icon theme inspired on material design. It is mostly flat with some shadows, highlights and gradients for some depth and uses a colorful palette with nice contrasts.\r\n\r\nFlat Remix GTK:\r\nhttps://www.opendesktop.org/p/1214931/\r\nFlat Remix GNOME theme:\r\nhttps://www.opendesktop.org/p/1013030/\r\nFlat Remix DARK GNOME theme:\r\nhttps://www.opendesktop.org/p/1197717/\r\nFlat Remix miami GNOME theme:\r\nhttps://www.gnome-look.org/p/1205642/\r\nFlat Remix DARK miami GNOME theme:\r\nhttps://www.gnome-look.org/p/1197969/\r\n\r\n---------------------------------------\r\n\r\n# Files\r\nFlat Remix - main icon theme\r\nFlat Remix Dark - for dark interfaces\r\nFlat Remix Light - for light interfaces\r\nFlat Remix git version (master) - latest version fom github (may not be stable)\r\n\r\n---------------------------------------\r\n\r\n# Installation\r\n\r\n1. Download and uncompress the zip file.\r\n2. Move \"Flat Remix\" folder to \".icons\" in your home directory.\r\n3. To set the theme, run the following command in Terminal:\r\n.... # gsettings set org.gnome.desktop.interface icon-theme \"Flat Remix\"\r\n.... or select \"Flat Remix\" as icon theme via distribution specific tweaktool.','',NULL,NULL,'0/4/2/b/9fdfe3c7b33cd2c6805e884ff9a94c87bc80.png',NULL,NULL,'2018-05-28 19:30:29','2018-05-28 19:30:29',NULL,24,'','','','','','',NULL,0,NULL,1,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'2c456a3325594efa97a6363f6f776046',100,'2018-05-28 19:30:29','2018-05-28 19:30:29',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','Icon Themes','icons','KDE Icon Theme',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'gplv3,icon-theme,linux,unix',NULL,NULL),(1209931,24,'text',100,0,100,'0ab532c9b80d43eea224940b4e3f6c4c',NULL,1,'Modified Oxygen-Air Theme','Modified Oxygen-Air KDM Theme. The background is not so good because I have to use a picture of my own.','',NULL,NULL,'3/b/0/f/86e909ed985d17b9e57227f3c7fe884bbac5.png',NULL,NULL,'2018-05-28 19:32:03','2018-05-28 19:32:03',NULL,24,'','','','','','',NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'0ab532c9b80d43eea224940b4e3f6c4c',100,'2018-05-28 19:32:03','2018-05-28 19:32:03',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','KDM4 Themes',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1209932,24,'text',104,0,100,'b1122b29d34e46459232251c9d21629a',NULL,1,'Nilium','A dark theme based on Noc and Helium\r\n\r\nI hope you like it :-)\r\n\r\nWallpaper: https://www.opendesktop.org/p/1227884/\r\nIcons: https://www.opendesktop.org/p/1188266/\r\n\r\nIf you like my work, please press the \"Pling me\" button to make a donation :-)','',NULL,NULL,'d/9/7/c/c31d25a1b18ae458371ebfbb18343045a092.png',NULL,NULL,'2018-05-28 19:34:34','2018-05-28 19:34:34',NULL,24,'','','','','','',NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'b1122b29d34e46459232251c9d21629a',100,'2018-05-28 19:34:34','2018-05-28 19:34:34',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','Plasma Themes','plasma5_desktopthemes','Plasma Theme',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'kde,linux,plasma,theme,unix,cc-by-nc',NULL,NULL),(1209933,24,'text',140,0,100,'d24558026e46467aa50e9aa1664d730d',NULL,1,'Equilux for Openbox','Openbox theme for Equilux by ddnexus.\r\nFor a good effect recommended titelbar\'s font size:\r\nEquilux - 11\r\nEquilux-compact - 10','',NULL,NULL,'9/a/5/4/6ab11d1cec21d5f2113d242e404385561577.png',NULL,NULL,'2018-05-28 19:37:41','2018-05-28 19:37:41',NULL,24,'','','','','','',NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'d24558026e46467aa50e9aa1664d730d',100,'2018-05-28 19:37:41','2018-05-28 19:37:41',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','Openbox Themes','openbox_themes',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1209934,24,'text',139,0,100,'2718548117744a33959037cb1e98bd05',NULL,1,'Equilux Theme','A theme to match the equilux theme','',NULL,NULL,'a/d/e/3/26a121cf17a6c5bfab86a0b04c933b1fb87e.png',NULL,NULL,'2018-05-28 19:39:31','2018-05-28 19:39:31',NULL,24,'','','','','','',NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,0,'2718548117744a33959037cb1e98bd05',100,'2018-05-28 19:39:31','2018-05-28 19:39:31',50,0,24,'dummy','https://cn.pling.com/cache/200x200-2/img/0/a/5/1/5804102a07b7db222c8423de6eba630240e0.png','Berlin','','2013-04-15 13:18:34','','Fluxbox Themes','fluxbox_styles',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `stat_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_ranking_category`
--

DROP TABLE IF EXISTS `stat_ranking_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_ranking_category` (
  `ranking_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_category_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `score` decimal(17,2) DEFAULT NULL,
  `rank` bigint(21) DEFAULT NULL,
  PRIMARY KEY (`ranking_id`),
  UNIQUE KEY `uk_cat_proj` (`project_id`,`project_category_id`),
  KEY `idx_project_cat_id` (`project_category_id`),
  KEY `idx_project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stat_ranking_category`
--

LOCK TABLES `stat_ranking_category` WRITE;
/*!40000 ALTER TABLE `stat_ranking_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_ranking_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_ranking_history`
--

DROP TABLE IF EXISTS `stat_ranking_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_ranking_history` (
  `ranking_history_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `project_id` int(11) NOT NULL COMMENT 'ID of the project',
  `type_id` int(11) DEFAULT NULL,
  `project_category_id` int(11) DEFAULT '0' COMMENT 'Kategorie',
  `count_plings` int(11) DEFAULT '0',
  `count_views` int(11) DEFAULT '0',
  `count_comments` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `year` int(11) DEFAULT NULL COMMENT 'z.B.: 1988',
  `month` int(11) DEFAULT NULL COMMENT 'z.b: 1-12',
  `day` int(11) DEFAULT NULL COMMENT 'z.B. 1-31',
  `year_week` int(11) DEFAULT NULL COMMENT 'z.b.: 201232',
  `ranking` int(11) DEFAULT NULL,
  PRIMARY KEY (`ranking_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Statistic of the ranking-values';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stat_ranking_history`
--

LOCK TABLES `stat_ranking_history` WRITE;
/*!40000 ALTER TABLE `stat_ranking_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_ranking_history` ENABLE KEYS */;
UNLOCK TABLES;

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
  `donation_time` timestamp NULL DEFAULT NULL COMMENT 'When was a project plinged?',
  `active_time` timestamp NULL DEFAULT NULL COMMENT 'When did paypal say, that this donation was payed successfull',
  `delete_time` timestamp NULL DEFAULT NULL,
  `amount` double(10,2) DEFAULT '0.00' COMMENT 'Amount of money',
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support`
--

LOCK TABLES `support` WRITE;
/*!40000 ALTER TABLE `support` DISABLE KEYS */;
/*!40000 ALTER TABLE `support` ENABLE KEYS */;
UNLOCK TABLES;

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
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `idx_name` (`tag_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1354 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag`
--

LOCK TABLES `tag` WRITE;
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
INSERT INTO `tag` VALUES (1,'1024x768',NULL,NULL),(2,'800x600',NULL,NULL),(3,'1600x900',NULL,NULL),(4,'kde',NULL,NULL),(5,'gnome',NULL,NULL),(6,'flowers',NULL,NULL),(7,'background',NULL,NULL),(8,'building',NULL,NULL),(9,'abstract',NULL,NULL),(10,'linux',NULL,NULL),(11,'windows',NULL,NULL),(12,'debian',NULL,NULL),(14,'test',NULL,NULL),(15,'plingtest',NULL,NULL),(16,'great',NULL,NULL),(17,'hallo',NULL,NULL),(18,'cool',NULL,NULL),(20,'qstyle',NULL,NULL),(21,'style',NULL,NULL),(22,'theme',NULL,NULL),(23,'svg',NULL,NULL),(24,'wallpaper',NULL,NULL),(25,'redhat',NULL,NULL),(26,'max',NULL,NULL),(27,'peak',NULL,NULL),(28,'dark',NULL,NULL),(29,'modern',NULL,NULL),(31,'adwaita-dark',NULL,NULL),(32,'gnome-dark',NULL,NULL),(33,'greybird',NULL,NULL),(34,'xfce',NULL,NULL),(35,'light',NULL,NULL),(36,'nature',NULL,NULL),(37,'rode',NULL,NULL),(38,'sky',NULL,NULL),(39,'view',NULL,NULL),(40,'snow',NULL,NULL),(41,'music',NULL,NULL),(42,'sea',NULL,NULL),(48,'morning',NULL,NULL),(50,'dragon',NULL,NULL),(51,'fire',NULL,NULL),(52,'cartoon',NULL,NULL),(56,'schloss',NULL,NULL),(57,'water',NULL,NULL),(58,'ice',NULL,NULL),(59,'winter',NULL,NULL),(61,'fubar',NULL,NULL),(62,'fürst',NULL,NULL),(65,'inking',NULL,NULL),(66,'vlc',NULL,NULL),(67,'cda',NULL,NULL),(68,'cda.pl',NULL,NULL),(69,'filmy',NULL,NULL),(70,'playlist',NULL,NULL),(71,'graphics',NULL,NULL),(72,'paint',NULL,NULL),(73,'draw',NULL,NULL),(74,'images',NULL,NULL),(75,'canyon',NULL,NULL),(76,'darkine',NULL,NULL),(77,'plasma',NULL,NULL),(78,'kvantum',NULL,NULL),(79,'rokin',NULL,NULL),(80,'freewallpaper',NULL,NULL),(81,'saveworld',NULL,NULL),(82,'elephents',NULL,NULL),(83,'splashscreen',NULL,NULL),(84,'splashes',NULL,NULL),(85,'blender',NULL,NULL),(86,'gimp',NULL,NULL),(87,'3d',NULL,NULL),(88,'forest',NULL,NULL),(89,'inkscape',NULL,NULL),(90,'thaipainting',NULL,NULL),(91,'ubuntu',NULL,NULL),(92,'kwin',NULL,NULL),(93,'galaxy',NULL,NULL),(94,'mandriva',NULL,NULL),(95,'puzzle',NULL,NULL),(96,'crossword',NULL,NULL),(97,'puz',NULL,NULL),(98,'bash',NULL,NULL),(99,'nautilus',NULL,NULL),(100,'context-menu',NULL,NULL),(101,'papirus',NULL,NULL),(102,'icon-theme',NULL,NULL),(103,'icons',NULL,NULL),(104,'material',NULL,NULL),(105,'adapta',NULL,NULL),(106,'aurorae',NULL,NULL),(107,'lxde',NULL,NULL),(108,'flat',NULL,NULL),(109,'artistic',NULL,NULL),(110,'colorful',NULL,NULL),(111,'design',NULL,NULL),(112,'space',NULL,NULL),(113,'nebula',NULL,NULL),(114,'planets',NULL,NULL),(115,'moon',NULL,NULL),(116,'stars',NULL,NULL),(117,'vector',NULL,NULL),(118,'illustrated',NULL,NULL),(119,'dictionary',NULL,NULL),(120,'language',NULL,NULL),(121,'spell',NULL,NULL),(122,'lexicon',NULL,NULL),(123,'distrowatch',NULL,NULL),(124,'distro',NULL,NULL),(125,'distribution',NULL,NULL),(126,'memory',NULL,NULL),(127,'pairs',NULL,NULL),(128,'learn',NULL,NULL),(129,'match',NULL,NULL),(130,'ocean',NULL,NULL),(131,'beach',NULL,NULL),(132,'clouds',NULL,NULL),(133,'gradient',NULL,NULL),(134,'themes',NULL,NULL),(135,'apple',NULL,NULL),(136,'macos',NULL,NULL),(137,'plasma-4',NULL,NULL),(138,'plasma-5',NULL,NULL),(139,'kate',NULL,NULL),(140,'c++',NULL,NULL),(141,'midi',NULL,NULL),(142,'virtual',NULL,NULL),(143,'keyboard',NULL,NULL),(144,'piano',NULL,NULL),(145,'controller',NULL,NULL),(146,'android',NULL,NULL),(147,'fuchsia',NULL,NULL),(148,'google',NULL,NULL),(149,'chrome',NULL,NULL),(150,'os',NULL,NULL),(151,'x',NULL,NULL),(152,'10',NULL,NULL),(153,'microsoft',NULL,NULL),(154,'redis',NULL,NULL),(155,'lelveldb',NULL,NULL),(156,'rocksdb',NULL,NULL),(157,'memcached',NULL,NULL),(158,'ssdb',NULL,NULL),(159,'gtk',NULL,NULL),(160,'cinnamon',NULL,NULL),(161,'metacity',NULL,NULL),(162,'purple',NULL,NULL),(163,'orange',NULL,NULL),(164,'green',NULL,NULL),(165,'gold',NULL,NULL),(166,'blue',NULL,NULL),(167,'teal',NULL,NULL),(168,'grey',NULL,NULL),(169,'red',NULL,NULL),(170,'date',NULL,NULL),(171,'creation-date',NULL,NULL),(172,'dolphin',NULL,NULL),(174,'service-menu',NULL,NULL),(175,'mint',NULL,NULL),(176,'kubuntu',NULL,NULL),(177,'suse',NULL,NULL),(178,'opensuse',NULL,NULL),(179,'arch',NULL,NULL),(180,'fedora',NULL,NULL),(181,'gentoo',NULL,NULL),(182,'frugalware',NULL,NULL),(183,'slackware',NULL,NULL),(184,'xubuntu',NULL,NULL),(185,'manjaro',NULL,NULL),(186,'firefox',NULL,NULL),(187,'osx',NULL,NULL),(188,'bsd',NULL,NULL),(189,'pclinuxos',NULL,NULL),(190,'mageia',NULL,NULL),(191,'mate',NULL,NULL),(192,'tux',NULL,NULL),(193,'solus',NULL,NULL),(194,'gnu',NULL,NULL),(195,'zorin',NULL,NULL),(196,'lxqt',NULL,NULL),(197,'deepin',NULL,NULL),(198,'animal',NULL,NULL),(199,'people',NULL,NULL),(200,'landscape',NULL,NULL),(201,'mountain',NULL,NULL),(202,'brigde',NULL,NULL),(203,'manga',NULL,NULL),(204,'anime',NULL,NULL),(205,'car',NULL,NULL),(206,'mobile-phone',NULL,NULL),(207,'wallpaper-other',NULL,NULL),(208,'app',NULL,NULL),(209,'software',NULL,NULL),(210,'audio',NULL,NULL),(211,'audio-other',NULL,NULL),(212,'player',NULL,NULL),(213,'production',NULL,NULL),(214,'radio',NULL,NULL),(215,'tagging',NULL,NULL),(216,'converter',NULL,NULL),(217,'extraction',NULL,NULL),(218,'artwork',NULL,NULL),(219,'painting',NULL,NULL),(220,'clipart',NULL,NULL),(221,'3d-render',NULL,NULL),(222,'animation',NULL,NULL),(223,'drawing',NULL,NULL),(224,'artwork-other',NULL,NULL),(225,'fractal',NULL,NULL),(226,'label',NULL,NULL),(227,'dvd',NULL,NULL),(228,'cd',NULL,NULL),(229,'addon',NULL,NULL),(230,'unix',NULL,NULL),(231,'dock',NULL,NULL),(232,'plasma5',NULL,NULL),(233,'layout',NULL,NULL),(235,'top',NULL,NULL),(237,'parabolic',NULL,NULL),(238,'longhorn',NULL,NULL),(239,'vista',NULL,NULL),(240,'beta',NULL,NULL),(241,'dos',NULL,NULL),(242,'2000',NULL,NULL),(243,'xp',NULL,NULL),(244,'high',NULL,NULL),(245,'sierra',NULL,NULL),(246,'iphone',NULL,NULL),(247,'ios',NULL,NULL),(248,'11',NULL,NULL),(249,'ipad',NULL,NULL),(250,'canonical',NULL,NULL),(251,'unity',NULL,NULL),(252,'8',NULL,NULL),(253,'gnome-shell',NULL,NULL),(254,'metro',NULL,NULL),(255,'website',NULL,NULL),(256,'viewer',NULL,NULL),(257,'slice',NULL,NULL),(258,'web',NULL,NULL),(259,'kde2',NULL,NULL),(260,'kde3',NULL,NULL),(261,'enlightenment',NULL,NULL),(262,'e-module',NULL,NULL),(263,'e-entrance',NULL,NULL),(264,'gtk1',NULL,NULL),(265,'gtk2',NULL,NULL),(266,'gtk3',NULL,NULL),(267,'gnome2',NULL,NULL),(268,'colorscheme',NULL,NULL),(269,'domino-style',NULL,NULL),(270,'qtcurve',NULL,NULL),(271,'be-shell',NULL,NULL),(272,'look-and-feel',NULL,NULL),(273,'extension',NULL,NULL),(274,'karamba',NULL,NULL),(275,'superkaramba',NULL,NULL),(276,'conky',NULL,NULL),(277,'gkrellm',NULL,NULL),(278,'cairo-clock',NULL,NULL),(279,'applet',NULL,NULL),(280,'desklet',NULL,NULL),(281,'widget',NULL,NULL),(282,'menu',NULL,NULL),(283,'clock',NULL,NULL),(284,'multimedia',NULL,NULL),(285,'weather',NULL,NULL),(286,'monitoring',NULL,NULL),(287,'calendar',NULL,NULL),(288,'comic-source',NULL,NULL),(289,'wallpaper-plugin',NULL,NULL),(290,'amor',NULL,NULL),(291,'screenshot',NULL,NULL),(292,'window-manager',NULL,NULL),(293,'budgie',NULL,NULL),(294,'fluxbox',NULL,NULL),(295,'icewm',NULL,NULL),(296,'elementary',NULL,NULL),(297,'openbox',NULL,NULL),(298,'launcher',NULL,NULL),(299,'gnomenu',NULL,NULL),(300,'kbfx',NULL,NULL),(301,'plank',NULL,NULL),(302,'dockbarx',NULL,NULL),(303,'awn',NULL,NULL),(304,'cairo-dock',NULL,NULL),(305,'docky',NULL,NULL),(306,'kicker',NULL,NULL),(307,'latte',NULL,NULL),(308,'kxdocker',NULL,NULL),(309,'bootscreen',NULL,NULL),(310,'grub',NULL,NULL),(311,'burg',NULL,NULL),(312,'plymouth',NULL,NULL),(313,'usplash',NULL,NULL),(314,'xsplash',NULL,NULL),(315,'splashy',NULL,NULL),(316,'gfxboot',NULL,NULL),(317,'screensaver',NULL,NULL),(318,'tutorial',NULL,NULL),(319,'ui',NULL,NULL),(320,'concept',NULL,NULL),(321,'phone',NULL,NULL),(322,'comic',NULL,NULL),(323,'widget-style',NULL,NULL),(324,'cursor',NULL,NULL),(325,'emoticon',NULL,NULL),(326,'logo',NULL,NULL),(327,'iconset',NULL,NULL),(328,'fantasy',NULL,NULL),(329,'sddm',NULL,NULL),(330,'twitch',NULL,NULL),(331,'fruit',NULL,NULL),(332,'basket',NULL,NULL),(333,'food',NULL,NULL),(335,'scenery',NULL,NULL),(336,'sunset',NULL,NULL),(337,'qt5',NULL,NULL),(338,'library',NULL,NULL),(339,'flower',NULL,NULL),(340,'highsierra',NULL,NULL),(341,'yosemite',NULL,NULL),(342,'ambiance',NULL,NULL),(343,'18.04',NULL,NULL),(344,'orangini',NULL,NULL),(345,'arrongin',NULL,NULL),(346,'telinkrin',NULL,NULL),(348,'agplv3','AGPLv3',''),(349,'gplv3','GPLv3',''),(350,'gplv2','GPLv2 only',''),(351,'artistic-2','Artistic 2.0',''),(352,'bsd-license','BSD License',''),(353,'cc0','Creative Commons 0 (Public Domain)','https://creativecommons.org/share-your-work/public-domain/'),(354,'cpl-1','CPL 1.0',''),(355,'cc-by','Creative Commons Attribution','https://creativecommons.org/share-your-work/licensing-types-examples/'),(356,'cc-by-nc','Creative Commons Attribution NonCommercial','https://creativecommons.org/share-your-work/licensing-types-examples/'),(357,'cc-by-nc-nd','Creative Commons Attribution NonCommercial NoDerivatives','https://creativecommons.org/share-your-work/licensing-types-examples/'),(358,'cc-by-nc-sa','Creative Commons Attribution NonCommercial ShareAlike','https://creativecommons.org/share-your-work/licensing-types-examples/'),(359,'ccy-by-nd',NULL,NULL),(360,'cc-by-nd','Creative Commons Attribution NoDerivatives','https://creativecommons.org/share-your-work/licensing-types-examples/'),(361,'cc-by-sa','Creative Commons Attribution ShareAlike','Most used and recommended CC license for artwork:\r\nhttps://creativecommons.org/share-your-work/licensing-types-examples/'),(362,'gfdl','GFDL',''),(363,'gplv2-later','GPLv2 or later',''),(364,'lgplv2','LGPLv2',''),(365,'lgplv3','LGPLv3',''),(366,'qpl','QPL',''),(367,'x11-license','X11 License',''),(368,'mit-license','MIT License',''),(370,'dolpin-service-menu',NULL,NULL),(371,'imgur',NULL,NULL),(372,'dolphin-service-menu',NULL,NULL),(373,'share',NULL,NULL),(374,'redmond',NULL,NULL),(375,'watermark',NULL,NULL),(376,'deskto',NULL,NULL),(377,'win10',NULL,NULL),(378,'lindows',NULL,NULL),(380,'sight',NULL,NULL),(381,'appimage','AppImage',''),(382,'reclinig',NULL,NULL),(383,'chair',NULL,NULL),(384,'art',NULL,NULL),(385,'horse',NULL,NULL),(387,'sakura',NULL,NULL),(388,'tree',NULL,NULL),(389,'seascape',NULL,NULL),(390,'rock',NULL,NULL),(391,'leafs',NULL,NULL),(392,'autumn',NULL,NULL),(393,'mushrooms',NULL,NULL),(394,'ground',NULL,NULL),(395,'bubble',NULL,NULL),(396,'under',NULL,NULL),(398,'seasons',NULL,NULL),(399,'big',NULL,NULL),(400,'frozen',NULL,NULL),(401,'cloud',NULL,NULL),(402,'volcano',NULL,NULL),(403,'rain',NULL,NULL),(404,'wood',NULL,NULL),(405,'cliff',NULL,NULL),(407,'love',NULL,NULL),(408,'cuple',NULL,NULL),(409,'cute',NULL,NULL),(410,'sunsets',NULL,NULL),(411,'beaches',NULL,NULL),(412,'oceans',NULL,NULL),(413,'night',NULL,NULL),(414,'bird',NULL,NULL),(415,'fly',NULL,NULL),(416,'4k',NULL,NULL),(417,'black',NULL,NULL),(418,'robotic',NULL,NULL),(419,'technology',NULL,NULL),(420,'network',NULL,NULL),(421,'api',NULL,NULL),(422,'http',NULL,NULL),(423,'request',NULL,NULL),(424,'api-client',NULL,NULL),(425,'alien',NULL,NULL),(426,'atwork',NULL,NULL),(427,'lone',NULL,NULL),(428,'reddit',NULL,NULL),(429,'internet',NULL,NULL),(430,'rocks',NULL,NULL),(431,'annette_alt',NULL,NULL),(432,'photos',NULL,NULL),(433,'trees',NULL,NULL),(434,'mountains',NULL,NULL),(435,'lake',NULL,NULL),(436,'bridges',NULL,NULL),(437,'river',NULL,NULL),(438,'peace',NULL,NULL),(439,'translator',NULL,NULL),(440,'word',NULL,NULL),(441,'babylon',NULL,NULL),(442,'qdvdauthor',NULL,NULL),(443,'dvdauthor',NULL,NULL),(445,'stardict',NULL,NULL),(446,'slideshow',NULL,NULL),(453,'dermave',NULL,NULL),(454,'spring',NULL,NULL),(455,'path',NULL,NULL),(456,'tulip',NULL,NULL),(457,'peaceful',NULL,NULL),(458,'tees',NULL,NULL),(459,'opensea',NULL,NULL),(460,'shark',NULL,NULL),(461,'vase',NULL,NULL),(462,'authoring',NULL,NULL),(463,'foliages',NULL,NULL),(464,'leaf',NULL,NULL),(465,'leaves',NULL,NULL),(466,'pier',NULL,NULL),(467,'browsers',NULL,NULL),(468,'girl',NULL,NULL),(469,'beauty',NULL,NULL),(470,'camera',NULL,NULL),(471,'wolf',NULL,NULL),(472,'inspire',NULL,NULL),(473,'motivation',NULL,NULL),(474,'rolls',NULL,NULL),(475,'royce',NULL,NULL),(476,'bison',NULL,NULL),(477,'animals',NULL,NULL),(478,'parks',NULL,NULL),(479,'usa',NULL,NULL),(480,'montana',NULL,NULL),(481,'landscapes',NULL,NULL),(482,'hrsoftware',NULL,NULL),(483,'payrollsoftware',NULL,NULL),(484,'humanresourcemanagement',NULL,NULL),(485,'hrms',NULL,NULL),(486,'bitcoin',NULL,NULL),(487,'customer',NULL,NULL),(488,'care',NULL,NULL),(489,'steel',NULL,NULL),(490,'metal',NULL,NULL),(491,'bricks',NULL,NULL),(492,'race',NULL,NULL),(493,'track',NULL,NULL),(494,'waterfall',NULL,NULL),(495,'chm',NULL,NULL),(496,'macosx',NULL,NULL),(497,'dark-theme',NULL,NULL),(498,'dark-gimp',NULL,NULL),(499,'dark-inkscape',NULL,NULL),(500,'color',NULL,NULL),(501,'grass',NULL,NULL),(502,'beclock',NULL,NULL),(503,'plasmoid',NULL,NULL),(504,'clocks',NULL,NULL),(505,'time',NULL,NULL),(506,'xfce4',NULL,NULL),(507,'xfwm',NULL,NULL),(508,'desktop',NULL,NULL),(509,'4',NULL,NULL),(510,'server',NULL,NULL),(511,'8.1',NULL,NULL),(512,'ninja',NULL,NULL),(513,'chamaleon',NULL,NULL),(514,'colors',NULL,NULL),(515,'macos11',NULL,NULL),(516,'deepinos',NULL,NULL),(517,'deepinlinux',NULL,NULL),(518,'road',NULL,NULL),(519,'beuty',NULL,NULL),(520,'beauti',NULL,NULL),(521,'soyeux',NULL,NULL),(522,'lotus',NULL,NULL),(523,'screen',NULL,NULL),(524,'looksky',NULL,NULL),(525,'plnet',NULL,NULL),(526,'colourful',NULL,NULL),(527,'city',NULL,NULL),(528,'mountins',NULL,NULL),(529,'fitness',NULL,NULL),(530,'lover',NULL,NULL),(531,'body',NULL,NULL),(532,'sexy',NULL,NULL),(533,'dragons',NULL,NULL),(534,'game',NULL,NULL),(535,'of',NULL,NULL),(536,'thrones',NULL,NULL),(537,'got',NULL,NULL),(538,'houses',NULL,NULL),(539,'burjkhalifa',NULL,NULL),(540,'dubai',NULL,NULL),(541,'fish',NULL,NULL),(542,'smoke',NULL,NULL),(543,'butterfly',NULL,NULL),(544,'african',NULL,NULL),(545,'elephant',NULL,NULL),(546,'gain',NULL,NULL),(547,'boot',NULL,NULL),(548,'materia',NULL,NULL),(549,'bootsplash',NULL,NULL),(550,'mobile',NULL,NULL),(551,'bangalore',NULL,NULL),(552,'eventvenue',NULL,NULL),(553,'ios11',NULL,NULL),(554,'imac',NULL,NULL),(555,'popos',NULL,NULL),(556,'system76',NULL,NULL),(557,'hooli',NULL,NULL),(558,'flatpat',NULL,NULL),(559,'adwaita',NULL,NULL),(560,'arc',NULL,NULL),(561,'arcosx',NULL,NULL),(562,'oranchelo',NULL,NULL),(563,'retro',NULL,NULL),(564,'shadow',NULL,NULL),(565,'kisskool',NULL,NULL),(566,'accent',NULL,NULL),(567,'bar',NULL,NULL),(568,'table',NULL,NULL),(569,'vitax',NULL,NULL),(570,'geometric',NULL,NULL),(571,'scandinavian',NULL,NULL),(572,'polygonal',NULL,NULL),(573,'colibribird',NULL,NULL),(574,'yakuake',NULL,NULL),(575,'sun',NULL,NULL),(576,'helium',NULL,NULL),(577,'sonic',NULL,NULL),(578,'17.10',NULL,NULL),(579,'gui',NULL,NULL),(580,'admin',NULL,NULL),(581,'tool',NULL,NULL),(582,'manager',NULL,NULL),(583,'streaming',NULL,NULL),(584,'mostar',NULL,NULL),(585,'balkans',NULL,NULL),(586,'cde',NULL,NULL),(587,'motif',NULL,NULL),(588,'starwars',NULL,NULL),(589,'darth',NULL,NULL),(590,'vader',NULL,NULL),(591,'jedi',NULL,NULL),(592,'sith',NULL,NULL),(593,'waves',NULL,NULL),(594,'sunlight',NULL,NULL),(595,'seashore',NULL,NULL),(596,'country',NULL,NULL),(597,'skyline',NULL,NULL),(598,'lighthouse',NULL,NULL),(599,'lamp',NULL,NULL),(600,'lightbulb',NULL,NULL),(601,'boat',NULL,NULL),(602,'coffee',NULL,NULL),(603,'dog',NULL,NULL),(604,'globe',NULL,NULL),(605,'airballon',NULL,NULL),(606,'hot',NULL,NULL),(607,'air',NULL,NULL),(608,'paris',NULL,NULL),(609,'birds',NULL,NULL),(610,'strangulation',NULL,NULL),(611,'sense',NULL,NULL),(612,'house',NULL,NULL),(613,'ballon',NULL,NULL),(614,'debian-package','Debian (.deb)',''),(615,'suse-rpm','open/Suse (RPM)',''),(616,'redhat-rpm','RedHat (RPM)',''),(617,'arch-package','Arch (tar.gz)',''),(618,'flatpak','Flatpak',''),(619,'snap-package','Snap Package',''),(620,'swing',NULL,NULL),(621,'bee',NULL,NULL),(622,'wall',NULL,NULL),(623,'paintings',NULL,NULL),(624,'plants',NULL,NULL),(625,'nilium',NULL,NULL),(626,'skin',NULL,NULL),(627,'flying',NULL,NULL),(628,'breeze',NULL,NULL),(629,'translucent',NULL,NULL),(630,'xenlism',NULL,NULL),(631,'minimalism',NULL,NULL),(632,'luna',NULL,NULL),(633,'earth',NULL,NULL),(634,'person',NULL,NULL),(635,'ducks',NULL,NULL),(636,'oak',NULL,NULL),(637,'old',NULL,NULL),(638,'white',NULL,NULL),(639,'zen',NULL,NULL),(640,'buddha',NULL,NULL),(641,'meditation',NULL,NULL),(642,'maditation',NULL,NULL),(643,'buddhism',NULL,NULL),(644,'scribus',NULL,NULL),(645,'accounting_software',NULL,NULL),(646,'online_accounting',NULL,NULL),(647,'lubuntu',NULL,NULL),(648,'eyes',NULL,NULL),(649,'friendly',NULL,NULL),(650,'qsvgstyle',NULL,NULL),(651,'man',NULL,NULL),(652,'juntra',NULL,NULL),(653,'lightning',NULL,NULL),(654,'gis',NULL,NULL),(655,'gps',NULL,NULL),(656,'geo',NULL,NULL),(657,'gpx',NULL,NULL),(658,'garmin',NULL,NULL),(659,'keto',NULL,NULL),(660,'bridge',NULL,NULL),(661,'healthy',NULL,NULL),(662,'king',NULL,NULL),(663,'instant',NULL,NULL),(664,'messenger',NULL,NULL),(665,'lan',NULL,NULL),(666,'chatting',NULL,NULL),(667,'messanger',NULL,NULL),(668,'cointreesupportphonenumber',NULL,NULL),(669,'wallpapers',NULL,NULL),(670,'wild',NULL,NULL),(671,'roses',NULL,NULL),(672,'rosas',NULL,NULL),(673,'flor',NULL,NULL),(674,'rosa',NULL,NULL),(675,'rose',NULL,NULL),(676,'matrix',NULL,NULL),(677,'neo',NULL,NULL),(678,'cairo',NULL,NULL),(679,'world',NULL,NULL),(680,'times',NULL,NULL),(681,'zones',NULL,NULL),(682,'shores',NULL,NULL),(683,'woods',NULL,NULL),(684,'apps',NULL,NULL),(689,'glass',NULL,NULL),(690,'x86','x86 - 32bit',''),(691,'x86-64','x86 - 64bit',''),(692,'armhf','arm - 32bit',''),(693,'arm64','arm - 64bit',''),(694,'apk','Android (APK)',''),(695,'osx-compatible','OS-X compatible',''),(696,'source-package','Source Code Package',''),(697,'windows-binary','Windows Binary',''),(698,'electron-app','Electron App',''),(699,'creative',NULL,NULL),(700,'abstracr',NULL,NULL),(701,'thumbnails',NULL,NULL),(702,'backup',NULL,NULL),(703,'restore',NULL,NULL),(704,'free',NULL,NULL),(705,'wallper',NULL,NULL),(706,'desktopwallper',NULL,NULL),(707,'picture',NULL,NULL),(708,'spider',NULL,NULL),(709,'photo',NULL,NULL),(710,'turmeric',NULL,NULL),(711,'dry',NULL,NULL),(712,'#flat',NULL,NULL),(713,'#svg',NULL,NULL),(714,'bug',NULL,NULL),(715,'planet',NULL,NULL),(716,'#metallic',NULL,NULL),(717,'studio',NULL,NULL),(718,'emerald',NULL,NULL),(719,'#gradient',NULL,NULL),(720,'#icon',NULL,NULL),(721,'#tiling',NULL,NULL),(722,'stone',NULL,NULL),(723,'danube',NULL,NULL),(724,'srem',NULL,NULL),(725,'equilibrium',NULL,NULL),(726,'spices',NULL,NULL),(727,'berry',NULL,NULL),(728,'mac',NULL,NULL),(729,'rounded',NULL,NULL),(730,'t4g',NULL,NULL),(731,'ship',NULL,NULL),(732,'pdf',NULL,NULL),(733,'servicemenu',NULL,NULL),(734,'cla',NULL,NULL),(735,'slim',NULL,NULL),(736,'cbdoil',NULL,NULL),(737,'number',NULL,NULL),(738,'support',NULL,NULL),(739,'windowmaker',NULL,NULL),(740,'gnustep',NULL,NULL),(741,'openstep',NULL,NULL),(742,'nextstep',NULL,NULL),(743,'skies',NULL,NULL),(744,'wind',NULL,NULL),(745,'login',NULL,NULL),(746,'smashrepairs',NULL,NULL),(747,'panelbeaters',NULL,NULL),(748,'#planta',NULL,NULL),(749,'#verde',NULL,NULL),(750,'#green',NULL,NULL),(751,'#sul',NULL,NULL),(752,'#aranha',NULL,NULL),(753,'#banana',NULL,NULL),(754,'#spider',NULL,NULL),(755,'#folha',NULL,NULL),(756,'long',NULL,NULL),(757,'highway',NULL,NULL),(758,'wave',NULL,NULL),(759,'waving',NULL,NULL),(760,'beautiful',NULL,NULL),(761,'colorfu',NULL,NULL),(762,'artisitc',NULL,NULL),(763,'sunrise',NULL,NULL),(764,'uploader',NULL,NULL),(765,'photography',NULL,NULL),(766,'facebook',NULL,NULL),(767,'vk',NULL,NULL),(768,'deviantart',NULL,NULL),(769,'qrender',NULL,NULL),(770,'fallout',NULL,NULL),(771,'pixelfun3',NULL,NULL),(772,'moons',NULL,NULL),(774,'glazy',NULL,NULL),(775,'shiny',NULL,NULL),(776,'clearlooks',NULL,NULL),(777,'port',NULL,NULL),(778,'belt',NULL,NULL),(781,'nimbus',NULL,NULL),(783,'simple',NULL,NULL),(784,'elegant',NULL,NULL),(786,'aquarium',NULL,NULL),(787,'summer',NULL,NULL),(788,'professional',NULL,NULL),(789,'metallic',NULL,NULL),(790,'palm',NULL,NULL),(791,'sand',NULL,NULL),(792,'hill',NULL,NULL),(793,'solaris',NULL,NULL),(794,'oracle',NULL,NULL),(795,'kim',NULL,NULL),(796,'image',NULL,NULL),(797,'rotate',NULL,NULL),(798,'resize',NULL,NULL),(799,'pixelfun',NULL,NULL),(800,'small',NULL,NULL),(801,'goldfish',NULL,NULL),(802,'swimming',NULL,NULL),(803,'iceberg',NULL,NULL),(804,'plant',NULL,NULL),(805,'emulation',NULL,NULL),(806,'roadrunner',NULL,NULL),(807,'sign',NULL,NULL),(808,'in',NULL,NULL),(809,'rr',NULL,NULL),(810,'batman',NULL,NULL),(811,'joker',NULL,NULL),(812,'suicide',NULL,NULL),(813,'squad',NULL,NULL),(814,'postgresql',NULL,NULL),(815,'mysql',NULL,NULL),(816,'python',NULL,NULL),(817,'hidpi',NULL,NULL),(818,'plastic',NULL,NULL),(820,'pond',NULL,NULL),(821,'weaved',NULL,NULL),(822,'moutains',NULL,NULL),(823,'field',NULL,NULL),(824,'sunny',NULL,NULL),(825,'pontes',NULL,NULL),(826,'natureza',NULL,NULL),(827,'diode',NULL,NULL),(828,'pioneer',NULL,NULL),(829,'minimalist',NULL,NULL),(830,'rss',NULL,NULL),(831,'tango',NULL,NULL),(832,'pixel',NULL,NULL),(833,'newyork',NULL,NULL),(834,'sharp',NULL,NULL),(835,'classic',NULL,NULL),(836,'locksmith',NULL,NULL),(837,'panel',NULL,NULL),(838,'encfs',NULL,NULL),(839,'kde5',NULL,NULL),(840,'weight',NULL,NULL),(841,'loss',NULL,NULL),(842,'forskolin',NULL,NULL),(843,'stones',NULL,NULL),(844,'fishing',NULL,NULL),(845,'vidhigra',NULL,NULL),(846,'vegetables',NULL,NULL),(847,'health',NULL,NULL),(848,'fresh',NULL,NULL),(849,'java',NULL,NULL),(850,'snake',NULL,NULL),(851,'rainbow',NULL,NULL),(852,'cell',NULL,NULL),(853,'life',NULL,NULL),(854,'san',NULL,NULL),(855,'steghide',NULL,NULL),(856,'arvada',NULL,NULL),(857,'qt4',NULL,NULL),(858,'hangman',NULL,NULL),(859,'abstrac',NULL,NULL),(860,'midnight',NULL,NULL),(861,'starcraft',NULL,NULL),(862,'nocturnal',NULL,NULL),(863,'neuro',NULL,NULL),(864,'res-q',NULL,NULL),(865,'brain',NULL,NULL),(867,'bumblebee',NULL,NULL),(868,'nvidia',NULL,NULL),(869,'glow',NULL,NULL),(870,'rapid',NULL,NULL),(871,'tone',NULL,NULL),(872,'cat',NULL,NULL),(873,'zenex',NULL,NULL),(874,'cbd',NULL,NULL),(875,'tunnel',NULL,NULL),(876,'glowing',NULL,NULL),(877,'clear',NULL,NULL),(878,'lady',NULL,NULL),(879,'silver',NULL,NULL),(880,'evening',NULL,NULL),(881,'valley',NULL,NULL),(882,'eruption',NULL,NULL),(883,'elementary-x',NULL,NULL),(884,'elementaryos',NULL,NULL),(885,'elementary-os-theme',NULL,NULL),(886,'redspider',NULL,NULL),(887,'php',NULL,NULL),(888,'rocket',NULL,NULL),(889,'dinosaur',NULL,NULL),(890,'lion',NULL,NULL),(891,'gray',NULL,NULL),(892,'virtualization',NULL,NULL),(893,'hypervisor',NULL,NULL),(894,'virtualbox',NULL,NULL),(895,'vmware',NULL,NULL),(896,'phpvirtualbox',NULL,NULL),(897,'fusion',NULL,NULL),(898,'video',NULL,NULL),(899,'mp4',NULL,NULL),(900,'telegram',NULL,NULL),(901,'icon',NULL,NULL),(902,'plasma-shell',NULL,NULL),(903,'plasma-desktop',NULL,NULL),(904,'csd',NULL,NULL),(905,'force',NULL,NULL),(906,'ssd',NULL,NULL),(907,'headerbar',NULL,NULL),(908,'ultra',NULL,NULL),(909,'castle',NULL,NULL),(911,'bokeh',NULL,NULL),(912,'dpi',NULL,NULL),(913,'retina',NULL,NULL),(914,'highdpi',NULL,NULL),(915,'bibata',NULL,NULL),(916,'xfwm4',NULL,NULL),(917,'slave',NULL,NULL),(918,'nixos',NULL,NULL),(919,'noblur',NULL,NULL),(920,'vera',NULL,NULL),(921,'openmandriva',NULL,NULL),(922,'gnome-desktop',NULL,NULL),(923,'gnome-icons',NULL,NULL),(924,'simply',NULL,NULL),(925,'sunbeam',NULL,NULL),(926,'fostering',NULL,NULL),(927,'foster',NULL,NULL),(928,'chilidern',NULL,NULL),(929,'purefit',NULL,NULL),(930,'hash',NULL,NULL),(931,'md5',NULL,NULL),(932,'crypto',NULL,NULL),(933,'street',NULL,NULL),(934,'lights',NULL,NULL),(935,'church',NULL,NULL),(936,'interior',NULL,NULL),(937,'banana',NULL,NULL),(938,'shine',NULL,NULL),(939,'bright',NULL,NULL),(940,'trim',NULL,NULL),(941,'rape',NULL,NULL),(942,'fbreader',NULL,NULL),(943,'icon-set',NULL,NULL),(944,'steelrx',NULL,NULL),(945,'male',NULL,NULL),(946,'enhancement',NULL,NULL),(947,'plasmas-shell',NULL,NULL),(948,'hd',NULL,NULL),(949,'flow',NULL,NULL),(950,'clean',NULL,NULL),(951,'seven',NULL,NULL),(952,'airplane',NULL,NULL),(953,'war',NULL,NULL),(954,'alian',NULL,NULL),(955,'lines',NULL,NULL),(957,'splash',NULL,NULL),(958,'2.10',NULL,NULL),(959,'magma',NULL,NULL),(960,'piranha',NULL,NULL),(961,'flame',NULL,NULL),(962,'konsole',NULL,NULL),(963,'pastel',NULL,NULL),(964,'hope',NULL,NULL),(965,'hopeful',NULL,NULL),(966,'pornhub',NULL,NULL),(967,'parser',NULL,NULL),(968,'garden',NULL,NULL),(969,'horuse',NULL,NULL),(970,'fight',NULL,NULL),(971,'evining',NULL,NULL),(972,'minimal',NULL,NULL),(974,'monotone',NULL,NULL),(975,'symbolic',NULL,NULL),(976,'linux-mint',NULL,NULL),(977,'triangles',NULL,NULL),(978,'raven',NULL,NULL),(979,'full',NULL,NULL),(980,'rotation',NULL,NULL),(981,'create',NULL,NULL),(982,'arts',NULL,NULL),(984,'transcode',NULL,NULL),(985,'trangle',NULL,NULL),(986,'mask',NULL,NULL),(987,'face',NULL,NULL),(988,'balls',NULL,NULL),(989,'cretive',NULL,NULL),(990,'tail',NULL,NULL),(991,'logs',NULL,NULL),(992,'future',NULL,NULL),(993,'futuristic',NULL,NULL),(998,'suru-icons',NULL,NULL),(1002,'suru++',NULL,NULL),(1003,'suru-plus',NULL,NULL),(1004,'chapters',NULL,NULL),(1005,'intro',NULL,NULL),(1006,'skip',NULL,NULL),(1007,'shore',NULL,NULL),(1008,'banks',NULL,NULL),(1010,'matcha',NULL,NULL),(1012,'systemtray',NULL,NULL),(1013,'wildfire',NULL,NULL),(1015,'chemical',NULL,NULL),(1016,'elements',NULL,NULL),(1017,'spots',NULL,NULL),(1018,'multi',NULL,NULL),(1019,'radiation',NULL,NULL),(1020,'symbol',NULL,NULL),(1021,'abstraction',NULL,NULL),(1022,'cars',NULL,NULL),(1023,'traffic',NULL,NULL),(1024,'display',NULL,NULL),(1025,'zip',NULL,NULL),(1026,'rar',NULL,NULL),(1027,'archiver',NULL,NULL),(1028,'corrupter',NULL,NULL),(1029,'corruptor',NULL,NULL),(1030,'calendis',NULL,NULL),(1031,'tea',NULL,NULL),(1032,'celestial',NULL,NULL),(1033,'storm',NULL,NULL),(1034,'figure',NULL,NULL),(1035,'crystals',NULL,NULL),(1036,'debris',NULL,NULL),(1037,'explosion',NULL,NULL),(1038,'liquid',NULL,NULL),(1039,'colored',NULL,NULL),(1040,'day',NULL,NULL),(1041,'faenza',NULL,NULL),(1043,'abtract',NULL,NULL),(1044,'poly',NULL,NULL),(1045,'diet',NULL,NULL),(1046,'review',NULL,NULL),(1047,'cube',NULL,NULL),(1048,'paper',NULL,NULL),(1049,'cut',NULL,NULL),(1050,'strips',NULL,NULL),(1051,'form',NULL,NULL),(1052,'cliffs',NULL,NULL),(1053,'destruction',NULL,NULL),(1054,'snail',NULL,NULL),(1055,'shell',NULL,NULL),(1056,'entrance',NULL,NULL),(1057,'stairs',NULL,NULL),(1058,'creartive',NULL,NULL),(1059,'cobalt',NULL,NULL),(1061,'linuxmint',NULL,NULL),(1062,'lm',NULL,NULL),(1063,'meme',NULL,NULL),(1064,'memes',NULL,NULL),(1065,'peaks',NULL,NULL),(1066,'algae',NULL,NULL),(1067,'macro',NULL,NULL),(1068,'close',NULL,NULL),(1069,'up',NULL,NULL),(1070,'nest',NULL,NULL),(1072,'pink',NULL,NULL),(1073,'gay',NULL,NULL),(1074,'works',NULL,NULL),(1075,'pictures',NULL,NULL),(1076,'alpha',NULL,NULL),(1077,'unlimited',NULL,NULL),(1078,'fit',NULL,NULL),(1079,'forskoli',NULL,NULL),(1080,'stream',NULL,NULL),(1081,'trail',NULL,NULL),(1082,'games',NULL,NULL),(1083,'numbers',NULL,NULL),(1084,'animated',NULL,NULL),(1085,'buildings',NULL,NULL),(1086,'town',NULL,NULL),(1087,'village',NULL,NULL),(1088,'various',NULL,NULL),(1089,'transparent',NULL,NULL),(1090,'term',NULL,NULL),(1091,'terminal',NULL,NULL),(1092,'color-scheme',NULL,NULL),(1093,'internal',NULL,NULL),(1094,'team',NULL,NULL),(1095,'download',NULL,NULL),(1096,'chat',NULL,NULL),(1097,'button',NULL,NULL),(1098,'light-house',NULL,NULL),(1099,'pidin',NULL,NULL),(1100,'adium',NULL,NULL),(1101,'oxygen',NULL,NULL),(1103,'x11',NULL,NULL),(1104,'cursors',NULL,NULL),(1105,'kdeneon',NULL,NULL),(1106,'konqui',NULL,NULL),(1107,'metadata',NULL,NULL),(1109,'kaos',NULL,NULL),(1110,'surface',NULL,NULL),(1111,'spiral',NULL,NULL),(1112,'render',NULL,NULL),(1113,'radiance',NULL,NULL),(1114,'shape',NULL,NULL),(1115,'electric',NULL,NULL),(1116,'current',NULL,NULL),(1117,'glitter',NULL,NULL),(1118,'rope',NULL,NULL),(1119,'wings',NULL,NULL),(1120,'ball',NULL,NULL),(1121,'infinity',NULL,NULL),(1122,'dots',NULL,NULL),(1123,'headphones',NULL,NULL),(1124,'neon',NULL,NULL),(1125,'book',NULL,NULL),(1126,'pen',NULL,NULL),(1128,'coral',NULL,NULL),(1129,'sarong',NULL,NULL),(1130,'thaiart',NULL,NULL),(1131,'anamax',NULL,NULL),(1132,'adventure',NULL,NULL),(1133,'sketch',NULL,NULL),(1134,'walk',NULL,NULL),(1135,'reader',NULL,NULL),(1136,'mountiains',NULL,NULL),(1137,'pendulum',NULL,NULL),(1138,'silico',NULL,NULL),(1139,'sumbol',NULL,NULL),(1140,'drops',NULL,NULL),(1141,'buds',NULL,NULL),(1142,'dandelion',NULL,NULL),(1143,'sparks',NULL,NULL),(1144,'wearable',NULL,NULL),(1145,'wear',NULL,NULL),(1146,'watchos',NULL,NULL),(1147,'watch',NULL,NULL),(1148,'iwatch',NULL,NULL),(1149,'circle',NULL,NULL),(1150,'line',NULL,NULL),(1151,'crystal',NULL,NULL),(1152,'polygon',NULL,NULL),(1153,'insubstantial',NULL,NULL),(1154,'turquoise',NULL,NULL),(1155,'funny',NULL,NULL),(1156,'lol',NULL,NULL),(1157,'dentist-maple-grove-mn',NULL,NULL),(1158,'best-dentist-in-crystal',NULL,NULL),(1159,'dentist-in-crystal',NULL,NULL),(1160,'dentist-crystal-mn',NULL,NULL),(1161,'illustrators',NULL,NULL),(1162,'matte',NULL,NULL),(1163,'vfx',NULL,NULL),(1164,'artist',NULL,NULL),(1165,'starry',NULL,NULL),(1166,'how-to-learn-arabic',NULL,NULL),(1167,'learn-arabic-online',NULL,NULL),(1168,'tajweed-rules',NULL,NULL),(1169,'bayyinah-tv',NULL,NULL),(1170,'learn-quran-online',NULL,NULL),(1171,'kdm4',NULL,NULL),(1172,'cave',NULL,NULL),(1173,'daylight',NULL,NULL),(1174,'diamond',NULL,NULL),(1175,'joke',NULL,NULL),(1176,'landing',NULL,NULL),(1177,'wallpapaers',NULL,NULL),(1178,'folders',NULL,NULL),(1179,'darker',NULL,NULL),(1180,'gun',NULL,NULL),(1181,'fog',NULL,NULL),(1182,'medical-consent-form',NULL,NULL),(1183,'health-history-form',NULL,NULL),(1184,'patient-registration-form',NULL,NULL),(1185,'patient-intake-form',NULL,NULL),(1186,'new-patient-registration-form',NULL,NULL),(1187,'home-care-software',NULL,NULL),(1188,'electronic-visit-verification',NULL,NULL),(1189,'home-health-agency-software',NULL,NULL),(1190,'home-health-care-software',NULL,NULL),(1191,'homecare-software',NULL,NULL),(1192,'seaside',NULL,NULL),(1193,'yellow',NULL,NULL),(1194,'coast',NULL,NULL),(1195,'outofthesandbox',NULL,NULL),(1196,'outofthesandboxcoupons',NULL,NULL),(1197,'outofthesandboxdiscounts',NULL,NULL),(1198,'outofthesandboxcouponcodes',NULL,NULL),(1199,'outofthesandboxpromos',NULL,NULL),(1200,'meteo',NULL,NULL),(1201,'beos',NULL,NULL),(1202,'haiku',NULL,NULL),(1203,'aesthetic',NULL,NULL),(1204,'mushroom',NULL,NULL),(1205,'instafilm',NULL,NULL),(1206,'film',NULL,NULL),(1207,'spave',NULL,NULL),(1208,'gtk+',NULL,NULL),(1211,'insect',NULL,NULL),(1212,'plums',NULL,NULL),(1213,'kdeconnect',NULL,NULL),(1214,'sms',NULL,NULL),(1215,'connect',NULL,NULL),(1216,'pyramids',NULL,NULL),(1217,'slides',NULL,NULL),(1218,'cogs',NULL,NULL),(1219,'gears',NULL,NULL),(1220,'chemistry',NULL,NULL),(1223,'marble',NULL,NULL),(1225,'wonderful',NULL,NULL),(1226,'shapes',NULL,NULL),(1227,'solid',NULL,NULL),(1228,'colour',NULL,NULL),(1229,'cabin',NULL,NULL),(1230,'mill',NULL,NULL),(1231,'constellation',NULL,NULL),(1232,'cerberus',NULL,NULL),(1233,'rising',NULL,NULL),(1234,'golden',NULL,NULL),(1235,'dialysis-center-chicago',NULL,NULL),(1236,'forest-park-dialysis',NULL,NULL),(1237,'home-dialysis-chicago',NULL,NULL),(1238,'forest-park-kidney-center',NULL,NULL),(1239,'oak-park-dialysis-center',NULL,NULL),(1240,'axon',NULL,NULL),(1241,'reaxion',NULL,NULL),(1242,'voilet',NULL,NULL),(1243,'dna',NULL,NULL),(1244,'wide',NULL,NULL),(1245,'eagle',NULL,NULL),(1246,'better',NULL,NULL),(1247,'idea',NULL,NULL),(1248,'to',NULL,NULL),(1249,'genuine',NULL,NULL),(1250,'factory',NULL,NULL),(1251,'plane',NULL,NULL),(1252,'mathematics',NULL,NULL),(1253,'jmol',NULL,NULL),(1254,'wayzata-orthodontics',NULL,NULL),(1255,'wayzata-periodontics',NULL,NULL),(1256,'wayzata-dental',NULL,NULL),(1257,'dentist-wayzata-mn',NULL,NULL),(1258,'dental-office-wayzata-mn',NULL,NULL),(1259,'apache-license','Apache License',''),(1260,'bloomington-family-dentistry',NULL,NULL),(1261,'bloomington-dentistry',NULL,NULL),(1262,'dental-implants-in-bloomington-mn',NULL,NULL),(1263,'dental-clinic-richfield-mn',NULL,NULL),(1264,'dentist-in-bloomington-mn',NULL,NULL),(1265,'detox',NULL,NULL),(1266,'best',NULL,NULL),(1267,'aqua',NULL,NULL),(1268,'abastract',NULL,NULL),(1269,'todo',NULL,NULL),(1270,'new',NULL,NULL),(1271,'gnome-light',NULL,NULL),(1272,'lakeview-health-center',NULL,NULL),(1273,'urgent-care-avondale',NULL,NULL),(1274,'urgent-care-lakeview',NULL,NULL),(1275,'lakeview-immediate-care',NULL,NULL),(1276,'lincoln-park-urgent-care',NULL,NULL),(1277,'wil',NULL,NULL),(1278,'cosmetic-dentist-monroe-ny',NULL,NULL),(1279,'dentist-in-howell-nj',NULL,NULL),(1280,'dentist-in-west-milford-nj',NULL,NULL),(1281,'dentist-in-hamburg-nj',NULL,NULL),(1282,'dentist-in-goshen-ny',NULL,NULL),(1283,'fireworks',NULL,NULL),(1284,'graphic',NULL,NULL),(1285,'abstrct',NULL,NULL),(1286,'soft',NULL,NULL),(1287,'digital',NULL,NULL),(1288,'eye',NULL,NULL),(1289,'aurora',NULL,NULL),(1290,'panther',NULL,NULL),(1291,'twm',NULL,NULL),(1292,'tron',NULL,NULL),(1293,'die',NULL,NULL),(1294,'hard',NULL,NULL),(1295,'weston',NULL,NULL),(1296,'ffmpeg',NULL,NULL),(1297,'qt',NULL,NULL),(1298,'cross-platform',NULL,NULL),(1299,'convert',NULL,NULL),(1300,'airport-transportation-minneapolis',NULL,NULL),(1301,'msp-airport-transportation',NULL,NULL),(1302,'saint-paul-airport-taxi',NULL,NULL),(1303,'msp-cab-service',NULL,NULL),(1304,'airport-cab-minneapolis',NULL,NULL),(1305,'orthodontist-in-milwaukee',NULL,NULL),(1306,'dental-implants-milwaukee',NULL,NULL),(1307,'teeth-whitening-milwaukee',NULL,NULL),(1308,'hdr',NULL,NULL),(1309,'fhd',NULL,NULL),(1310,'dentist-chanhassen-mn',NULL,NULL),(1311,'eden-prairie-dental',NULL,NULL),(1312,'dentist-in-eden-prairie-mn',NULL,NULL),(1313,'rehab',NULL,NULL),(1314,'round',NULL,NULL),(1315,'balloon',NULL,NULL),(1316,'pruvit',NULL,NULL),(1317,'palm-tree',NULL,NULL),(1318,'greenlyte',NULL,NULL),(1319,'rpsc',NULL,NULL),(1320,'rpsc_latest_jobs',NULL,NULL),(1321,'rpsc_vice_principal_jobs',NULL,NULL),(1322,'rpsc_vice_principal_vacancy',NULL,NULL),(1323,'vortex',NULL,NULL),(1324,'vortext',NULL,NULL),(1325,'rx',NULL,NULL),(1326,'dffdc',NULL,NULL),(1327,'plate',NULL,NULL),(1328,'dawn',NULL,NULL),(1329,'tops',NULL,NULL),(1330,'conzelman',NULL,NULL),(1331,'turning',NULL,NULL),(1332,'kintamani',NULL,NULL),(1333,'indonesia',NULL,NULL),(1334,'everest',NULL,NULL),(1335,'range',NULL,NULL),(1336,'anstract',NULL,NULL),(1337,'textured',NULL,NULL),(1339,'cara',NULL,NULL),(1340,'menggugurkan',NULL,NULL),(1341,'kandungan',NULL,NULL),(1342,'1',NULL,NULL),(1343,'bulan',NULL,NULL),(1344,'grunge',NULL,NULL),(1345,'travel',NULL,NULL),(1346,'gdm',NULL,NULL),(1347,'grungy',NULL,NULL),(1348,'dentist-in-berwyn-il',NULL,NULL),(1349,'berwyn-dentist',NULL,NULL),(1350,'dentist-in-cicero-il',NULL,NULL),(1351,'cicero-dental',NULL,NULL),(1352,'dental-clinic-in-cicero-il',NULL,NULL),(1353,'rivers',NULL,NULL);
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag_group`
--

DROP TABLE IF EXISTS `tag_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(45) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_group`
--

LOCK TABLES `tag_group` WRITE;
/*!40000 ALTER TABLE `tag_group` DISABLE KEYS */;
INSERT INTO `tag_group` VALUES (1,'resolution'),(2,'gui'),(3,'motive'),(4,'OS'),(5,'user-tags'),(6,'category-tags'),(7,'license-tags'),(8,'file-packagetype-tags'),(9,'file-architecture-tags');
/*!40000 ALTER TABLE `tag_group` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=1441 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_group_item`
--

LOCK TABLES `tag_group_item` WRITE;
/*!40000 ALTER TABLE `tag_group_item` DISABLE KEYS */;
INSERT INTO `tag_group_item` VALUES (1,1,1),(2,1,2),(3,1,3),(4,2,4),(5,2,5),(6,3,6),(7,3,7),(8,3,8),(9,3,9),(10,4,10),(11,4,11),(12,4,12),(13,5,14),(14,5,15),(15,5,16),(16,5,17),(17,5,18),(19,5,20),(20,5,21),(21,5,22),(22,5,23),(23,5,24),(24,5,25),(25,5,26),(26,5,27),(27,5,28),(28,5,29),(30,5,31),(31,5,32),(32,5,33),(33,5,34),(34,5,35),(35,5,36),(36,5,37),(37,5,38),(38,5,39),(39,5,40),(40,5,41),(41,5,42),(47,5,48),(49,5,50),(50,5,51),(51,5,52),(55,5,56),(56,5,57),(57,5,58),(58,5,59),(59,5,8),(61,5,61),(62,5,62),(64,5,5),(66,5,65),(67,5,66),(68,5,67),(69,5,68),(70,5,69),(71,5,70),(72,5,71),(73,5,72),(74,5,73),(75,5,74),(76,5,75),(77,5,76),(78,5,77),(79,5,78),(80,5,79),(81,5,80),(82,5,81),(83,5,82),(84,5,83),(85,5,84),(86,5,85),(87,5,86),(88,5,87),(89,5,88),(90,5,89),(91,5,90),(92,5,91),(93,5,92),(94,5,93),(95,5,94),(96,5,95),(97,5,96),(98,5,97),(99,5,4),(100,5,10),(101,5,98),(102,5,99),(103,5,100),(104,5,101),(105,5,102),(106,5,103),(107,5,104),(108,5,105),(109,5,106),(110,5,107),(111,5,108),(112,5,9),(113,5,109),(114,5,110),(115,5,111),(116,5,112),(117,5,113),(118,5,114),(119,5,115),(120,5,116),(121,5,117),(122,5,118),(123,5,119),(124,5,120),(125,5,121),(126,5,122),(127,5,123),(128,5,124),(129,5,125),(130,5,126),(131,5,127),(132,5,128),(133,5,129),(134,5,130),(135,5,131),(136,5,132),(137,5,133),(138,5,134),(139,5,135),(140,5,136),(142,6,5),(143,6,77),(144,6,137),(145,6,138),(146,5,139),(147,5,140),(148,5,141),(149,5,142),(150,5,143),(151,5,144),(152,5,145),(153,5,146),(154,5,147),(155,5,148),(156,5,149),(157,5,150),(158,5,151),(159,5,11),(160,5,152),(161,5,153),(162,5,154),(163,5,155),(164,5,156),(165,5,157),(166,5,158),(167,5,159),(168,5,160),(169,5,161),(170,5,162),(171,5,163),(172,5,164),(173,5,165),(174,5,166),(175,5,167),(176,5,168),(177,5,169),(178,5,170),(179,5,171),(180,5,172),(182,5,174),(183,6,24),(184,6,175),(185,6,91),(186,6,176),(187,6,177),(188,6,178),(189,6,179),(190,6,180),(191,6,94),(192,6,181),(193,6,182),(194,6,183),(195,6,34),(196,6,184),(197,6,12),(198,6,185),(199,6,186),(200,6,11),(201,6,187),(202,6,135),(203,6,188),(204,6,189),(205,6,190),(206,6,191),(207,6,10),(208,6,192),(209,6,193),(210,6,194),(211,6,195),(212,6,196),(213,6,107),(214,6,197),(215,6,9),(216,6,198),(217,6,36),(218,6,199),(219,6,8),(220,6,200),(221,6,201),(222,6,131),(223,6,130),(224,6,202),(225,6,203),(226,6,204),(227,6,205),(228,6,206),(229,6,4),(230,6,207),(231,6,208),(232,6,209),(233,6,210),(234,6,211),(235,6,212),(236,6,213),(237,6,214),(238,6,215),(239,6,216),(240,6,217),(241,6,218),(242,6,219),(243,6,220),(244,6,221),(245,6,222),(246,6,223),(247,6,224),(248,6,225),(249,6,226),(250,6,227),(251,6,228),(252,6,229),(253,6,22),(254,6,230),(255,5,231),(256,5,232),(257,5,233),(259,5,235),(261,5,237),(262,5,238),(263,5,239),(264,5,240),(265,5,241),(266,5,242),(267,5,243),(268,5,244),(269,5,245),(270,5,187),(271,5,246),(272,5,247),(273,5,248),(274,5,249),(275,5,250),(276,5,251),(277,5,252),(278,5,253),(279,5,254),(280,5,255),(281,5,256),(282,5,257),(283,5,258),(284,6,160),(285,6,259),(286,6,260),(287,6,261),(288,6,7),(289,6,262),(290,6,263),(291,6,83),(292,6,264),(293,6,265),(294,6,266),(295,6,253),(296,6,267),(297,6,268),(298,6,269),(299,6,270),(300,6,78),(301,6,271),(302,6,272),(303,6,273),(304,6,274),(305,6,275),(306,6,276),(307,6,277),(308,6,278),(309,6,279),(310,6,280),(311,6,281),(312,6,282),(313,6,283),(314,6,284),(315,6,285),(316,6,286),(317,6,287),(318,6,288),(319,6,289),(320,6,290),(321,6,291),(322,6,251),(323,6,292),(324,6,293),(325,6,294),(326,6,295),(327,6,296),(328,6,297),(329,6,231),(330,6,298),(331,6,299),(332,6,300),(333,6,301),(334,6,302),(335,6,303),(336,6,304),(337,6,305),(338,6,306),(339,6,307),(340,6,308),(341,6,309),(342,6,310),(343,6,311),(344,6,312),(345,6,313),(346,6,314),(347,6,315),(348,6,316),(349,6,317),(350,6,124),(351,6,318),(352,6,319),(353,6,320),(354,6,321),(355,6,322),(356,6,323),(357,6,324),(358,6,102),(359,6,325),(360,6,326),(361,6,327),(362,5,328),(363,5,218),(364,5,329),(365,5,330),(366,5,331),(367,5,332),(368,5,333),(370,5,335),(371,5,200),(372,5,336),(373,5,337),(374,5,338),(375,5,339),(376,5,340),(377,5,341),(378,5,342),(379,5,343),(380,5,344),(381,5,345),(382,5,346),(384,7,348),(385,7,349),(386,7,350),(387,7,351),(389,7,352),(390,7,353),(391,7,354),(392,7,355),(393,7,356),(394,7,357),(395,7,358),(397,7,360),(398,7,361),(399,7,362),(400,7,363),(401,7,364),(402,7,365),(403,7,366),(404,7,367),(405,7,368),(407,5,7),(408,5,370),(409,5,284),(410,5,371),(411,5,372),(412,5,373),(413,5,374),(414,5,273),(415,5,375),(416,5,326),(417,5,376),(418,5,377),(419,5,378),(421,5,380),(422,5,381),(423,5,382),(424,5,383),(425,5,384),(426,5,385),(428,5,387),(429,5,388),(430,5,389),(431,5,390),(432,5,391),(433,5,392),(434,5,393),(435,5,394),(436,5,395),(437,5,396),(439,5,398),(440,5,399),(441,5,400),(442,5,401),(443,5,402),(444,5,403),(445,5,404),(446,5,405),(448,5,407),(449,5,408),(450,5,409),(451,5,410),(452,5,411),(453,5,412),(454,5,413),(455,5,414),(456,5,415),(457,5,201),(458,5,416),(459,5,417),(460,5,418),(461,5,419),(462,5,420),(463,5,421),(464,5,422),(465,5,423),(466,5,424),(467,5,425),(468,5,426),(469,5,198),(470,5,427),(471,5,428),(472,5,429),(473,5,430),(474,5,431),(475,5,432),(476,5,433),(477,5,6),(478,5,434),(479,5,435),(480,5,436),(481,5,437),(482,5,438),(483,5,439),(484,5,440),(485,5,441),(486,5,442),(487,5,443),(489,5,445),(490,5,446),(492,5,175),(493,5,266),(494,5,265),(500,5,453),(501,5,454),(502,5,455),(503,5,456),(504,5,457),(505,5,458),(506,5,459),(507,5,460),(508,5,461),(509,5,462),(510,5,463),(511,5,464),(512,5,465),(513,5,466),(514,5,467),(515,5,208),(516,5,298),(517,5,468),(518,5,469),(519,5,470),(520,5,471),(521,5,472),(522,5,473),(523,5,205),(524,5,474),(525,5,475),(526,5,476),(527,5,477),(528,5,478),(529,5,479),(530,5,480),(531,5,481),(532,5,482),(533,5,483),(534,5,484),(535,5,485),(536,5,486),(537,5,487),(538,5,488),(539,5,489),(540,5,490),(541,5,491),(542,5,492),(543,5,493),(544,5,494),(545,5,495),(546,5,310),(547,5,496),(548,5,497),(549,5,498),(550,5,499),(551,5,500),(552,5,501),(553,5,502),(554,5,503),(555,5,504),(556,5,505),(557,5,506),(558,5,507),(559,5,508),(560,5,509),(561,5,510),(562,5,511),(563,5,512),(564,5,513),(565,5,514),(566,5,276),(567,5,515),(568,5,197),(569,5,516),(570,5,517),(571,5,518),(572,5,519),(573,5,520),(574,5,521),(575,5,522),(576,5,523),(577,5,524),(578,5,525),(579,5,526),(580,5,527),(581,5,528),(582,5,529),(583,5,530),(584,5,531),(585,5,532),(586,5,533),(587,5,534),(588,5,535),(589,5,536),(590,5,537),(591,5,538),(592,5,539),(593,5,540),(594,5,541),(595,5,542),(596,5,543),(597,5,544),(598,5,545),(599,5,546),(600,5,547),(601,5,548),(602,5,549),(603,5,230),(604,5,550),(605,5,321),(606,5,551),(607,5,552),(608,5,553),(609,5,554),(610,5,555),(611,5,556),(612,5,557),(613,5,558),(614,5,559),(615,5,560),(616,5,561),(617,5,320),(618,5,562),(619,5,563),(620,5,564),(621,5,565),(622,5,566),(623,5,567),(624,5,568),(625,5,569),(626,5,570),(627,5,571),(628,5,572),(629,5,573),(630,5,268),(631,5,574),(632,5,575),(633,5,576),(634,5,577),(635,5,578),(636,5,579),(637,5,580),(638,5,581),(639,5,582),(640,5,583),(641,5,584),(642,5,585),(643,5,586),(644,5,587),(645,5,588),(646,5,589),(647,5,590),(648,5,591),(649,5,592),(650,5,593),(651,5,594),(652,5,595),(653,5,596),(654,5,597),(655,5,598),(656,5,599),(657,5,600),(658,5,601),(659,5,602),(660,5,603),(661,5,604),(662,5,605),(663,5,606),(664,5,607),(665,5,608),(666,5,609),(667,5,610),(668,5,611),(669,5,612),(670,5,613),(671,8,381),(672,8,614),(673,8,615),(674,8,616),(675,8,617),(676,8,618),(677,8,619),(678,5,620),(679,5,621),(680,5,622),(681,5,623),(682,5,193),(683,5,624),(684,5,625),(685,5,626),(686,5,627),(687,5,628),(688,5,629),(689,5,630),(690,5,631),(691,5,632),(692,5,633),(693,5,634),(694,5,635),(695,5,636),(696,5,637),(697,5,638),(698,5,639),(699,5,640),(700,5,641),(701,5,642),(702,5,643),(703,5,644),(704,5,645),(705,5,646),(706,5,647),(707,5,282),(708,5,648),(709,5,649),(710,5,650),(711,5,651),(712,5,652),(713,5,653),(714,5,654),(715,5,655),(716,5,656),(717,5,657),(718,5,658),(719,5,659),(720,5,660),(721,5,661),(722,5,662),(723,5,663),(724,5,664),(725,5,665),(726,5,666),(727,5,667),(728,5,668),(729,5,669),(730,5,670),(731,5,671),(732,5,672),(733,5,673),(734,5,674),(735,5,675),(736,5,676),(737,5,677),(738,5,678),(739,5,283),(740,5,679),(741,5,680),(742,5,681),(743,5,682),(744,5,683),(745,5,684),(750,5,689),(751,9,690),(752,9,691),(753,9,692),(754,9,693),(755,8,694),(756,8,695),(757,8,696),(758,8,697),(759,8,698),(760,5,699),(761,5,700),(762,5,701),(763,5,702),(764,5,703),(765,5,704),(766,5,705),(767,5,706),(768,5,707),(769,5,708),(770,5,185),(771,5,179),(772,5,709),(773,5,710),(774,5,711),(775,5,191),(776,5,712),(777,5,713),(778,5,714),(779,5,715),(780,5,716),(781,5,717),(782,5,718),(783,5,719),(784,5,720),(785,5,721),(786,5,722),(787,5,723),(788,5,724),(789,5,725),(790,5,726),(791,5,727),(792,5,728),(793,5,729),(794,5,730),(795,5,731),(796,5,732),(797,5,733),(798,5,734),(799,5,735),(800,5,736),(801,5,737),(802,5,738),(803,5,739),(804,5,740),(805,5,741),(806,5,742),(807,5,743),(808,5,744),(809,5,745),(810,5,746),(811,5,747),(812,5,748),(813,5,749),(814,5,750),(815,5,751),(816,5,752),(817,5,753),(818,5,754),(819,5,755),(820,5,756),(821,5,757),(822,5,758),(823,5,759),(824,5,760),(825,5,761),(826,5,762),(827,5,763),(828,5,764),(829,5,765),(830,5,766),(831,5,767),(832,5,768),(833,5,769),(834,5,770),(835,5,771),(836,5,324),(837,5,772),(839,5,774),(840,5,775),(841,5,776),(842,5,777),(843,5,778),(846,5,781),(848,5,783),(849,5,784),(851,5,786),(852,5,787),(853,5,788),(854,5,789),(855,5,790),(856,5,791),(857,5,792),(858,5,793),(859,5,794),(860,5,795),(861,5,796),(862,5,797),(863,5,798),(864,5,799),(865,5,800),(866,5,801),(867,5,802),(868,5,803),(869,5,804),(870,5,805),(871,5,806),(872,5,807),(873,5,808),(874,5,809),(875,5,810),(876,5,811),(877,5,812),(878,5,813),(879,5,814),(880,5,815),(881,5,816),(882,5,817),(883,5,818),(885,5,285),(886,5,820),(887,5,821),(888,5,822),(889,5,823),(890,5,824),(891,5,825),(892,5,826),(893,5,827),(894,5,828),(895,5,829),(896,5,830),(897,5,831),(898,5,832),(899,5,833),(900,5,834),(901,5,835),(902,5,836),(903,5,837),(904,5,838),(905,5,839),(906,5,178),(907,5,176),(908,5,840),(909,5,841),(910,5,842),(911,5,843),(912,5,844),(913,5,845),(914,5,846),(915,5,847),(916,5,848),(917,5,849),(918,5,850),(919,5,196),(920,5,851),(921,5,852),(922,5,853),(923,5,854),(924,5,855),(925,5,177),(926,5,856),(927,5,857),(928,5,858),(929,5,859),(930,5,860),(931,5,861),(932,5,862),(933,5,863),(934,5,864),(935,5,865),(936,5,12),(938,5,867),(939,5,868),(940,5,869),(941,5,870),(942,5,871),(943,5,872),(944,5,873),(945,5,874),(946,5,875),(947,5,876),(948,5,877),(949,5,878),(950,5,879),(951,5,880),(952,5,881),(953,5,882),(954,5,883),(955,5,884),(956,5,885),(957,5,886),(958,5,887),(959,5,888),(960,5,889),(961,5,890),(962,5,891),(963,5,892),(964,5,893),(965,5,894),(966,5,895),(967,5,896),(968,5,897),(969,5,898),(970,5,899),(971,5,318),(972,5,900),(973,5,901),(974,5,902),(975,5,903),(976,5,904),(977,5,905),(978,5,906),(979,5,907),(980,5,908),(981,5,909),(983,5,911),(984,5,912),(985,5,913),(986,5,914),(987,5,915),(988,5,916),(989,5,917),(990,5,918),(991,5,919),(992,5,920),(993,5,921),(994,5,184),(995,5,922),(996,5,923),(997,5,924),(998,5,925),(999,5,926),(1000,5,927),(1001,5,928),(1002,5,929),(1003,5,930),(1004,5,931),(1005,5,932),(1006,5,933),(1007,5,934),(1008,5,935),(1009,5,936),(1010,5,937),(1011,5,938),(1012,5,939),(1013,5,940),(1014,5,941),(1015,5,942),(1016,5,943),(1017,5,944),(1018,5,945),(1019,5,946),(1020,5,947),(1021,5,948),(1022,5,949),(1023,5,950),(1024,5,951),(1025,5,952),(1026,5,953),(1027,5,954),(1028,5,955),(1030,5,957),(1031,5,958),(1032,5,959),(1033,5,960),(1034,5,961),(1035,5,962),(1036,5,963),(1037,5,964),(1038,5,965),(1039,5,966),(1040,5,967),(1041,5,968),(1042,5,291),(1043,5,969),(1044,5,970),(1045,5,971),(1046,5,972),(1048,5,974),(1049,5,975),(1050,5,976),(1051,5,977),(1052,5,978),(1053,5,979),(1054,5,980),(1055,5,981),(1056,5,982),(1058,5,281),(1059,5,984),(1060,5,985),(1061,5,986),(1062,5,987),(1063,5,988),(1064,5,989),(1065,5,990),(1066,5,991),(1067,5,280),(1068,5,992),(1069,5,993),(1074,5,998),(1078,5,1002),(1079,5,1003),(1080,5,1004),(1081,5,1005),(1082,5,1006),(1083,5,1007),(1084,5,1008),(1086,5,1010),(1088,5,1012),(1089,5,1013),(1091,5,1015),(1092,5,1016),(1093,5,1017),(1094,5,1018),(1095,5,1019),(1096,5,1020),(1097,5,1021),(1098,5,219),(1099,5,1022),(1100,5,1023),(1101,5,1024),(1102,5,1025),(1103,5,1026),(1104,5,1027),(1105,5,1028),(1106,5,1029),(1107,5,1030),(1108,5,1031),(1109,5,1032),(1110,5,1033),(1111,5,297),(1112,5,1034),(1113,5,1035),(1114,5,1036),(1115,5,1037),(1116,5,1038),(1117,5,1039),(1118,5,1040),(1119,5,1041),(1121,5,1043),(1122,5,1044),(1123,5,1045),(1124,5,1046),(1125,5,1047),(1126,5,1048),(1127,5,1049),(1128,5,1050),(1129,5,1051),(1130,5,1052),(1131,5,1053),(1132,5,1054),(1133,5,1055),(1134,5,1056),(1135,5,1057),(1136,5,1058),(1137,5,1059),(1139,5,1061),(1140,5,1062),(1141,5,1063),(1142,5,1064),(1143,5,1065),(1144,5,1066),(1145,5,1067),(1146,5,1068),(1147,5,1069),(1148,5,1070),(1150,5,1072),(1151,5,1073),(1152,5,1074),(1153,5,1075),(1154,5,1076),(1155,5,1077),(1156,5,1078),(1157,5,1079),(1158,5,1080),(1159,5,1081),(1160,5,1082),(1161,5,1083),(1162,5,1084),(1163,5,1085),(1164,5,1086),(1165,5,1087),(1166,5,1088),(1167,5,1089),(1168,5,1090),(1169,5,1091),(1170,5,1092),(1171,5,1093),(1172,5,1094),(1173,5,1095),(1174,5,1096),(1175,5,1097),(1176,5,1098),(1177,5,1099),(1178,5,1100),(1179,5,1101),(1181,5,1103),(1182,5,1104),(1183,5,1105),(1184,5,1106),(1185,5,1107),(1187,5,1109),(1188,5,1110),(1189,5,1111),(1190,5,1112),(1191,5,1113),(1192,5,1114),(1193,5,1115),(1194,5,1116),(1195,5,1117),(1196,5,1118),(1197,5,1119),(1198,5,1120),(1199,5,1121),(1200,5,1122),(1201,5,1123),(1202,5,1124),(1203,5,1125),(1204,5,1126),(1206,5,1128),(1207,5,1129),(1208,5,1130),(1209,5,1131),(1210,5,1132),(1211,5,1133),(1212,5,1134),(1213,5,322),(1214,5,1135),(1215,5,1136),(1216,5,1137),(1217,5,1138),(1218,5,1139),(1219,5,1140),(1220,5,1141),(1221,5,1142),(1222,5,1143),(1223,5,1144),(1224,5,1145),(1225,5,1146),(1226,5,1147),(1227,5,1148),(1228,5,1149),(1229,5,1150),(1230,5,1151),(1231,5,1152),(1232,5,1153),(1233,5,1154),(1234,5,1155),(1235,5,1156),(1236,5,1157),(1237,5,1158),(1238,5,1159),(1239,5,1160),(1240,5,1161),(1241,5,1162),(1242,5,1163),(1243,5,1164),(1244,5,1165),(1245,5,1166),(1246,5,1167),(1247,5,1168),(1248,5,1169),(1249,5,1170),(1250,5,1171),(1251,5,1172),(1252,5,1173),(1253,5,1174),(1254,5,1175),(1255,5,1176),(1256,5,1177),(1257,5,1178),(1258,5,1179),(1259,5,1180),(1260,5,1181),(1261,5,1182),(1262,5,1183),(1263,5,1184),(1264,5,1185),(1265,5,1186),(1266,5,1187),(1267,5,1188),(1268,5,1189),(1269,5,1190),(1270,5,1191),(1271,5,1192),(1272,5,1193),(1273,5,1194),(1274,5,1195),(1275,5,1196),(1276,5,1197),(1277,5,1198),(1278,5,1199),(1279,5,1200),(1280,5,1201),(1281,5,1202),(1282,5,1203),(1283,5,1204),(1284,5,1205),(1285,5,1206),(1286,5,1207),(1287,5,1208),(1290,5,1211),(1291,5,1212),(1292,5,1213),(1293,5,1214),(1294,5,1215),(1295,5,1216),(1296,5,1217),(1297,5,1218),(1298,5,1219),(1299,5,1220),(1300,5,225),(1303,5,1223),(1305,5,1225),(1306,5,1226),(1307,5,1227),(1308,5,1228),(1309,5,1229),(1310,5,1230),(1311,5,1231),(1312,5,1232),(1313,5,1233),(1314,5,1234),(1315,5,1235),(1316,5,1236),(1317,5,1237),(1318,5,1238),(1319,5,1239),(1320,5,1240),(1321,5,1241),(1322,5,1242),(1323,5,1243),(1324,5,1244),(1325,5,1245),(1326,5,1246),(1327,5,1247),(1328,5,1248),(1329,5,1249),(1330,5,1250),(1331,5,1251),(1332,5,1252),(1333,5,1253),(1334,5,1254),(1335,5,1255),(1336,5,1256),(1337,5,1257),(1338,5,1258),(1339,7,1259),(1340,5,1260),(1341,5,1261),(1342,5,1262),(1343,5,1263),(1344,5,1264),(1345,5,1265),(1346,5,1266),(1347,5,1267),(1348,5,1268),(1349,5,1269),(1350,5,1270),(1351,5,1271),(1352,5,1272),(1353,5,1273),(1354,5,1274),(1355,5,1275),(1356,5,1276),(1357,5,1277),(1358,5,1278),(1359,5,1279),(1360,5,1280),(1361,5,1281),(1362,5,1282),(1363,5,1283),(1364,5,1284),(1365,5,1285),(1366,5,1286),(1367,5,1287),(1368,5,1288),(1369,5,1289),(1370,5,1290),(1371,5,1291),(1372,5,1292),(1373,5,1293),(1374,5,1294),(1375,5,1295),(1376,5,1296),(1377,5,1297),(1378,5,1298),(1379,5,1299),(1380,5,210),(1381,5,1300),(1382,5,1301),(1383,5,1302),(1384,5,1303),(1385,5,1304),(1386,5,1305),(1387,5,1306),(1388,5,1307),(1389,5,1308),(1390,5,1309),(1391,5,1310),(1392,5,1311),(1393,5,1312),(1394,5,1313),(1395,5,1314),(1396,5,1315),(1397,5,1316),(1398,5,1317),(1399,5,1318),(1400,5,1319),(1401,5,1320),(1402,5,1321),(1403,5,1322),(1404,5,1323),(1405,5,1324),(1406,5,1325),(1407,5,1326),(1408,5,1327),(1409,5,1328),(1410,5,1329),(1411,5,1330),(1412,5,1331),(1413,5,1332),(1414,5,1333),(1415,5,1334),(1416,5,1335),(1417,5,1336),(1418,5,1337),(1419,5,312),(1421,5,1339),(1422,5,1340),(1423,5,1341),(1424,5,1342),(1425,5,1343),(1426,5,1344),(1427,5,1345),(1428,5,1346),(1429,5,1347),(1430,5,270),(1431,5,293),(1432,5,1348),(1433,5,1349),(1434,5,1350),(1435,5,1351),(1436,5,1352),(1437,5,1353),(1438,5,365),(1439,5,209),(1440,5,349);
/*!40000 ALTER TABLE `tag_group_item` ENABLE KEYS */;
UNLOCK TABLES;

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
  `tag_parent_object_id` int(11) DEFAULT NULL,
  `tag_object_id` int(11) NOT NULL,
  `tag_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tag_changed` datetime DEFAULT NULL,
  PRIMARY KEY (`tag_item_id`),
  UNIQUE KEY `tags_unique` (`tag_id`,`tag_type_id`,`tag_object_id`,`tag_group_id`),
  KEY `tags_idx` (`tag_id`),
  KEY `tag_object` (`tag_object_id`,`tag_created`,`tag_changed`,`tag_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_object`
--

LOCK TABLES `tag_object` WRITE;
/*!40000 ALTER TABLE `tag_object` DISABLE KEYS */;
INSERT INTO `tag_object` VALUES (1,416,1,5,NULL,1209922,'2018-05-28 16:08:47','2018-05-28 18:08:47'),(2,105,1,5,NULL,1209922,'2018-05-28 16:08:47','2018-05-28 18:08:47'),(3,10,1,5,NULL,1209922,'2018-05-28 16:08:47','2018-05-28 18:08:47'),(4,104,1,5,NULL,1209922,'2018-05-28 16:08:47','2018-05-28 18:08:47'),(5,193,1,5,NULL,1209922,'2018-05-28 16:08:47','2018-05-28 18:08:47'),(6,365,1,5,NULL,1209927,'2018-05-28 17:27:19','2018-05-28 19:27:19'),(7,208,1,5,NULL,1209927,'2018-05-28 17:27:19','2018-05-28 19:27:19'),(8,209,1,5,NULL,1209927,'2018-05-28 17:27:19','2018-05-28 19:27:19'),(9,349,1,5,NULL,1209930,'2018-05-28 17:30:31','2018-05-28 19:30:31'),(10,102,1,5,NULL,1209930,'2018-05-28 17:30:31','2018-05-28 19:30:31'),(11,10,1,5,NULL,1209930,'2018-05-28 17:30:31','2018-05-28 19:30:31'),(12,230,1,5,NULL,1209930,'2018-05-28 17:30:31','2018-05-28 19:30:31'),(13,4,1,5,NULL,1209932,'2018-05-28 17:34:37','2018-05-28 19:34:37'),(14,10,1,5,NULL,1209932,'2018-05-28 17:34:37','2018-05-28 19:34:37'),(15,77,1,5,NULL,1209932,'2018-05-28 17:34:37','2018-05-28 19:34:37'),(16,22,1,5,NULL,1209932,'2018-05-28 17:34:37','2018-05-28 19:34:37'),(17,230,1,5,NULL,1209932,'2018-05-28 17:34:37','2018-05-28 19:34:37'),(18,356,1,7,NULL,1209932,'2018-05-28 17:34:37','2018-05-28 19:34:37');
/*!40000 ALTER TABLE `tag_object` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `tag_object_BEFORE_INSERT` BEFORE INSERT ON `tag_object` FOR EACH ROW
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
-- Dumping data for table `tag_type`
--

LOCK TABLES `tag_type` WRITE;
/*!40000 ALTER TABLE `tag_type` DISABLE KEYS */;
INSERT INTO `tag_type` VALUES (1,'project'),(2,'member'),(3,'file'),(4,'download'),(5,'image'),(6,'video'),(7,'comment'),(8,'activity');
/*!40000 ALTER TABLE `tag_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `view_reported_projects`
--

DROP TABLE IF EXISTS `view_reported_projects`;
/*!50001 DROP VIEW IF EXISTS `view_reported_projects`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `view_reported_projects` (
  `project_id` tinyint NOT NULL,
  `amount_reports` tinyint NOT NULL,
  `latest_report` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `stat_plings`
--

/*!50001 DROP TABLE IF EXISTS `stat_plings`*/;
/*!50001 DROP VIEW IF EXISTS `stat_plings`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=TEMPTABLE */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `stat_plings` AS select `plings`.`project_id` AS `project_id`,sum(`plings`.`amount`) AS `amount_received`,count(1) AS `count_plings`,count(distinct `plings`.`member_id`) AS `count_plingers`,max(`plings`.`active_time`) AS `latest_pling` from `plings` where (`plings`.`status_id` = 2) group by `plings`.`project_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_reported_projects`
--

/*!50001 DROP TABLE IF EXISTS `view_reported_projects`*/;
/*!50001 DROP VIEW IF EXISTS `view_reported_projects`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=TEMPTABLE */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
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

-- Dump completed on 2018-05-29  9:45:15
