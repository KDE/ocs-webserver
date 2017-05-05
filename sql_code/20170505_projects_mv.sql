#create view and tables
DROP VIEW stat_projects_v;
CREATE VIEW stat_projects_v AS
SELECT 
	`project`.`project_id` AS `project_id`,
	`project`.`member_id` AS `member_id`,
	`project`.`content_type` AS `content_type`,
	`project`.`project_category_id` AS `project_category_id`,
	`project`.`hive_category_id` AS `hive_category_id`,
	`project`.`is_active` AS `is_active`,
	`project`.`is_deleted` AS `is_deleted`,
	`project`.`status` AS `status`,
	`project`.`uuid` AS `uuid`,
	`project`.`pid` AS `pid`,
	`project`.`type_id` AS `type_id`,
	`project`.`title` AS `title`,
	`project`.`description` AS `description`,
	`project`.`version` AS `version`,
	`project`.`image_big` AS `image_big`,
	`project`.`image_small` AS `image_small`,
	`project`.`start_date` AS `start_date`,
	`project`.`content_url` AS `content_url`,
	`project`.`created_at` AS `created_at`,
	`project`.`changed_at` AS `changed_at`,
	`project`.`deleted_at` AS `deleted_at`,
	`project`.`creator_id` AS `creator_id`,
	`project`.`facebook_code` AS `facebook_code`,
	`project`.`github_code` AS `github_code`,
	`project`.`twitter_code` AS `twitter_code`,
	`project`.`google_code` AS `google_code`,
	`project`.`link_1` AS `link_1`,
	`project`.`embed_code` AS `embed_code`,
	`project`.`ppload_collection_id` AS `ppload_collection_id`,
	`project`.`validated` AS `validated`,
	`project`.`validated_at` AS `validated_at`,
	`project`.`featured` AS `featured`,
	`project`.`approved` AS `approved`,
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
	`member`.`type` AS `member_type`,
	`member`.`member_id` AS `project_member_id`,
	`project`.`changed_at` AS `project_changed_at`,
	(ROUND(((`project`.`count_likes` + 6) / ((`project`.`count_likes` + `project`.`count_dislikes`) + 12)),2) * 100) AS `laplace_score`,
	`member`.`username` AS `username`,
	`member`.`profile_image_url` AS `profile_image_url`,
	`member`.`city` AS `city`,
	`member`.`country` AS `country`,
	`member`.`created_at` AS `member_created_at`,
	`member`.`paypal_mail` AS `paypal_mail`,
	`project_category`.`title` AS `cat_title`,
	`stat_plings`.`amount_received` AS `amount_received`,
	`stat_plings`.`count_plings` AS `count_plings`,
	`stat_plings`.`count_plingers` AS `count_plingers`,
	`stat_plings`.`latest_pling` AS `latest_pling`
FROM (((`project`
JOIN `member` ON(((`project`.`member_id` = `member`.`member_id`) AND (`member`.`is_active` = 1) AND (`member`.`is_deleted` = 0))))
JOIN `project_category` ON((`project`.`project_category_id` = `project_category`.`project_category_id`)))
LEFT JOIN `stat_plings` ON((`project`.`project_id` = `stat_plings`.`project_id`)))
WHERE ((`project`.`status` = 100) AND (`project`.`type_id` = 1))
;

CREATE TABLE `stat_projects` (
	`project_id` INT(11) NOT NULL DEFAULT '0',
	`member_id` INT(11) NOT NULL DEFAULT '0',
	`content_type` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`project_category_id` INT(11) NOT NULL DEFAULT '0',
	`hive_category_id` INT(11) NOT NULL DEFAULT '0',
	`is_active` INT(1) NOT NULL DEFAULT '0',
	`is_deleted` INT(1) NOT NULL DEFAULT '0',
	`status` INT(11) NOT NULL DEFAULT '0',
	`uuid` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`pid` INT(11) NULL DEFAULT NULL COMMENT 'ParentId',
	`type_id` INT(11) NULL DEFAULT NULL COMMENT '0 = DummyProject, 1 = Project, 2 = Update',
	`title` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`description` TEXT NULL COLLATE 'utf8_general_ci',
	`version` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`image_big` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`image_small` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`start_date` DATETIME NULL DEFAULT NULL,
	`content_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`changed_at` DATETIME NULL DEFAULT NULL,
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`creator_id` INT(11) NULL DEFAULT NULL COMMENT 'Member_id of the creator. Importent for groups.',
	`facebook_code` TEXT NULL COLLATE 'utf8_general_ci',
	`github_code` TEXT NULL COLLATE 'utf8_general_ci',
	`twitter_code` TEXT NULL COLLATE 'utf8_general_ci',
	`google_code` TEXT NULL COLLATE 'utf8_general_ci',
	`link_1` TEXT NULL COLLATE 'utf8_general_ci',
	`embed_code` TEXT NULL COLLATE 'utf8_general_ci',
	`ppload_collection_id` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`validated` INT(1) NULL DEFAULT NULL,
	`validated_at` DATETIME NULL DEFAULT NULL,
	`featured` INT(1) NULL DEFAULT NULL,
	`approved` INT(1) NULL DEFAULT NULL,
	`amount` INT(11) NULL DEFAULT NULL,
	`amount_period` VARCHAR(45) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`claimable` INT(1) NULL DEFAULT NULL,
	`claimed_by_member` INT(11) NULL DEFAULT NULL,
	`count_likes` INT(11) NULL DEFAULT NULL,
	`count_dislikes` INT(11) NULL DEFAULT NULL,
	`count_comments` INT(11) NULL DEFAULT NULL,
	`count_downloads_hive` INT(11) NULL DEFAULT NULL,
	`source_id` INT(11) NULL DEFAULT NULL,
	`source_pk` INT(11) NULL DEFAULT NULL,
	`source_type` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`project_validated` INT(1) NULL DEFAULT NULL,
	`project_uuid` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`project_status` INT(11) NOT NULL DEFAULT '0',
	`project_created_at` DATETIME NULL DEFAULT NULL,
	`member_type` INT(1) NOT NULL DEFAULT '0' COMMENT 'Type: 0 = Member, 1 = group',
	`project_member_id` INT(10) NOT NULL DEFAULT '0',
	`project_changed_at` DATETIME NULL DEFAULT NULL,
	`laplace_score` DECIMAL(17,2) NULL DEFAULT NULL,
	`username` VARCHAR(255) NOT NULL COLLATE 'utf8_bin',
	`profile_image_url` VARCHAR(355) NULL DEFAULT NULL COMMENT 'URL to the profile-image' COLLATE 'utf8_general_ci',
	`city` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`country` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`member_created_at` DATETIME NULL DEFAULT NULL,
	`paypal_mail` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`cat_title` VARCHAR(100) NOT NULL COLLATE 'utf8_general_ci',
	`amount_received` DOUBLE(19,2) NULL DEFAULT NULL,
	`count_plings` BIGINT(21) NULL DEFAULT NULL,
	`count_plingers` BIGINT(21) NULL DEFAULT NULL,
	`latest_pling` TIMESTAMP NULL DEFAULT NULL COMMENT 'When did paypal say, that this pling was payed successfull',
	INDEX `idx_project_cat_id` (`project_category_id`)
)
ENGINE=InnoDB
;




#Update machanism
START TRANSACTION;
CREATE TABLE stat_projects_new LIKE stat_projects;
INSERT INTO stat_projects_new (select * from stat_projects_v);
RENAME TABLE `stat_projects` TO `stat_projects_old`;
RENAME TABLE `stat_projects_new` TO `stat_projects`;
DROP TABLE `stat_projects_old`;
COMMIT;


