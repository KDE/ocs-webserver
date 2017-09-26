
USE `pling`;

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(45) NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `idx_name` (`tag_name`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `tag_group`;
CREATE TABLE `tag_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(45) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `tag_group_item`;
CREATE TABLE `tag_group_item` (
  `tag_group_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_group_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`tag_group_item_id`),
  KEY `tag_group_idx` (`tag_group_id`),
  KEY `tag_idx` (`tag_id`),
  CONSTRAINT `tag` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`tag_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `tag_group` FOREIGN KEY (`tag_group_id`) REFERENCES `tag_group` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `tag_object`;
CREATE TABLE `tag_object` (
  `tag_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `tag_type_id` int(11) NOT NULL,
  `tag_object_id` int(11) NOT NULL,
  `tag_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tag_changed` datetime DEFAULT NULL,
  PRIMARY KEY (`tag_item_id`),
  UNIQUE KEY `tags_unique` (`tag_id`,`tag_type_id`,`tag_object_id`),
  KEY `tags_idx` (`tag_id`),
  KEY `types_idx` (`tag_type_id`)
) ENGINE=InnoDB;

DELIMITER $$
DROP TRIGGER IF EXISTS tag_object_BEFORE_INSERT$$
CREATE DEFINER = CURRENT_USER TRIGGER `tag_object_BEFORE_INSERT` BEFORE INSERT ON `tag_object` FOR EACH ROW
  BEGIN
    IF NEW.tag_changed IS NULL THEN
      SET NEW.tag_changed = NOW();
    END IF;
  END$$
DELIMITER ;

DROP TABLE IF EXISTS `tag_type`;
CREATE TABLE `tag_type` (
  `tag_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_type_name` varchar(45) NOT NULL,
  PRIMARY KEY (`tag_type_id`)
) ENGINE=InnoDB;

INSERT INTO `tag_type` (`tag_type_id`, `tag_type_name`) VALUES ('1', 'project');
INSERT INTO `tag_type` (`tag_type_id`, `tag_type_name`) VALUES ('2', 'member');
INSERT INTO `tag_type` (`tag_type_id`, `tag_type_name`) VALUES ('3', 'file');
INSERT INTO `tag_type` (`tag_type_id`, `tag_type_name`) VALUES ('4', 'download');
INSERT INTO `tag_type` (`tag_type_id`, `tag_type_name`) VALUES ('5', 'image');
INSERT INTO `tag_type` (`tag_type_id`, `tag_type_name`) VALUES ('6', 'video');
INSERT INTO `tag_type` (`tag_type_id`, `tag_type_name`) VALUES ('7', 'comment');
INSERT INTO `tag_type` (`tag_type_id`, `tag_type_name`) VALUES ('8', 'activity');

DROP procedure IF EXISTS `solr_query_import`;
DELIMITER $$
CREATE PROCEDURE `solr_query_import` ()
  BEGIN
    DROP TABLE IF EXISTS tmp_project_tags;
    CREATE TEMPORARY TABLE tmp_project_tags AS
      SELECT GROUP_CONCAT(tag.tag_name) AS tag_names, tgo.tag_object_id AS tag_project_id
      FROM tag_object AS tgo
        JOIN tag ON tag.tag_id = tgo.tag_id
      WHERE tag_type_id = 1
      GROUP BY tgo.tag_object_id
      ORDER BY tgo.tag_object_id;

    DROP TABLE IF EXISTS tmp_cat_tree;
    CREATE TEMPORARY TABLE tmp_cat_tree AS
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
    CREATE TEMPORARY TABLE tmp_cat_store AS
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
      project.created_at,
      project.changed_at,
      tcs.stores,
      tcs.ancestor_id_path        AS `cat_id_ancestor_path`,
      sppt.package_type_id_list   AS `package_ids`,
      sppt.package_name_list      AS `package_names`,
      t.tag_names                 AS `tags`
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN tmp_cat_store AS tcs ON project.project_category_id = tcs.project_category_id
      LEFT JOIN solr_project_package_types AS sppt ON sppt.package_project_id = project.project_id
      LEFT JOIN tmp_project_tags AS t ON t.tag_project_id = project.project_id
    WHERE project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND tcs.`is_active` = 1;
  END$$
DELIMITER ;

DROP procedure IF EXISTS `solr_query_delta_import`;
DELIMITER $$
CREATE PROCEDURE `solr_query_delta_import` (IN projectID INT(11))
  BEGIN
    DROP TABLE IF EXISTS tmp_project_tags;
    CREATE TEMPORARY TABLE tmp_project_tags AS
      SELECT GROUP_CONCAT(tag.tag_name) AS tag_names, tgo.tag_object_id AS tag_project_id
      FROM tag_object AS tgo
        JOIN tag ON tag.tag_id = tgo.tag_id
      WHERE tag_type_id = 1
      GROUP BY tgo.tag_object_id
      ORDER BY tgo.tag_object_id;

    DROP TABLE IF EXISTS tmp_cat_tree;
    CREATE TEMPORARY TABLE tmp_cat_tree AS
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
    CREATE TEMPORARY TABLE tmp_cat_store AS
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
      project.created_at,
      project.changed_at,
      tcs.stores,
      tcs.ancestor_id_path        AS `cat_id_ancestor_path`,
      sppt.package_type_id_list   AS `package_ids`,
      sppt.package_name_list      AS `package_names`,
      t.tag_names                 AS `tags`
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN tmp_cat_store AS tcs ON project.project_category_id = tcs.project_category_id
      LEFT JOIN solr_project_package_types AS sppt ON sppt.package_project_id = project.project_id
      LEFT JOIN tmp_project_tags AS t ON t.tag_project_id = project.project_id
    WHERE project_id = projectID;
  END$$
DELIMITER ;

DROP procedure IF EXISTS `solr_query_delta`;
DELIMITER $$
CREATE PROCEDURE `solr_query_delta` (IN lastIndexed varchar(255))
  BEGIN
    SELECT DISTINCT project_id
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN project_category AS pc ON pc.project_category_id = project.project_category_id
      LEFT JOIN tag_object AS tgo ON tgo.tag_object_id = project.project_id AND tgo.tag_type_id = 1
    WHERE (project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND pc.`is_active` = 1 AND project.changed_at > lastIndexed)
          OR (project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND pc.`is_active` = 1 AND (tgo.tag_created > lastIndexed OR tgo.tag_changed > lastIndexed))
    ;
  END$$
DELIMITER ;


DROP procedure IF EXISTS `generate_stat_project`;
DELIMITER $$
CREATE DEFINER=CURRENT_USER PROCEDURE `generate_stat_project`()
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
          (`reports_project`.`is_deleted` = 0)
        GROUP BY `reports_project`.`project_id`
    ;

    DROP TABLE IF EXISTS tmp_project_package_types;
    CREATE TEMPORARY TABLE tmp_project_package_types
    (PRIMARY KEY `primary` (project_id))
      ENGINE MyISAM
      AS
        SELECT
          project_id,
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

    DROP TABLE IF EXISTS tmp_project_tags;
    CREATE TEMPORARY TABLE tmp_project_tags
    (PRIMARY KEY `primary` (tag_project_id))
      ENGINE MyISAM
      AS
        SELECT GROUP_CONCAT(tag.tag_name) AS tag_names, tgo.tag_object_id AS tag_project_id
        FROM tag_object AS tgo
          JOIN tag ON tag.tag_id = tgo.tag_id
        WHERE tag_type_id = 1
        GROUP BY tgo.tag_object_id
        ORDER BY tgo.tag_object_id;


    DROP TABLE IF EXISTS tmp_stat_projects;
    CREATE TABLE tmp_stat_projects
    (PRIMARY KEY `primary` (`project_id`), INDEX `idx_cat` (`project_category_id`))
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
          `t`.`tag_names` AS `tags`
        FROM
          `project`
          JOIN `member` ON `member`.`member_id` = `project`.`member_id`
          JOIN `project_category` ON `project`.`project_category_id` = `project_category`.`project_category_id`
          LEFT JOIN `stat_plings` ON `stat_plings`.`project_id` = `project`.`project_id`
          LEFT JOIN `tmp_reported_projects` AS trp ON `trp`.`project_id` = `project`.`project_id`
          LEFT JOIN `tmp_project_package_types` AS tppt ON tppt.project_id = `project`.project_id
          LEFT JOIN `tmp_project_tags` AS t ON t.`tag_project_id` = project.`project_id`
        WHERE
          `member`.`is_deleted` = 0
          AND `member`.`is_active` = 1
          AND `project`.`type_id` = 1
          AND `project`.`status` = 100
          AND `project_category`.`is_active` = 1
    ;

    RENAME TABLE stat_projects TO old_stat_projects, tmp_stat_projects TO stat_projects;

    DROP TABLE IF EXISTS old_stat_projects;
  END$$

DELIMITER ;
