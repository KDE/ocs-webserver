DROP PROCEDURE IF EXISTS `generate_stat_cat_prod_count`;

DELIMITER $$

CREATE PROCEDURE `generate_stat_cat_prod_count`()
BEGIN

    DROP TABLE IF EXISTS `tmp_stat_cat_prod_count`;
    CREATE TABLE `tmp_stat_cat_prod_count`
    (
        `project_category_id` int(11) NOT NULL,
        `tag_id`              int(11) NULL,
        `count_product`       int(11) NULL,
        INDEX `idx_tag` (`project_category_id`, `tag_id`)
    )
        ENGINE MEMORY
    AS
    SELECT `sct2`.`project_category_id`,
           NULL                             AS `tag_id`,
           count(DISTINCT `p`.`project_id`) AS `count_product`
    FROM `stat_cat_tree` AS `sct1`
             JOIN `stat_cat_tree` AS `sct2` ON `sct1`.`lft` BETWEEN `sct2`.`lft` AND `sct2`.`rgt`
             LEFT JOIN `stat_projects` AS `p` ON `p`.`project_category_id` = `sct1`.`project_category_id`
    WHERE `p`.`amount_reports` IS NULL
    GROUP BY `sct2`.`project_category_id`

    UNION

    SELECT `sct2`.`project_category_id`,
           `tg`.`tag_ids`                   AS `tag_id`,
           count(DISTINCT `p`.`project_id`) AS `count_product`
    FROM `stat_cat_tree` AS `sct1`
             JOIN `stat_cat_tree` AS `sct2` ON `sct1`.`lft` BETWEEN `sct2`.`lft` AND `sct2`.`rgt`
             JOIN `stat_projects` AS `p` ON `p`.`project_category_id` = `sct1`.`project_category_id`
             JOIN (SELECT `cs`.`store_id`, GROUP_CONCAT(`ct`.`tag_id`) AS `tag_ids`
                   FROM `config_store` `cs`
                            JOIN `config_store_tag` `ct` ON `ct`.`store_id` = `cs`.`store_id` AND `ct`.`is_active` = 1
                   GROUP BY `cs`.`store_id`) `tg`
             JOIN (
        SELECT DISTINCT `project_id`, `tag_ids`
        FROM `stat_project_tagids`
                 JOIN (SELECT `cs`.`store_id`, GROUP_CONCAT(`ct`.`tag_id`) AS `tag_ids`
                       FROM `config_store` `cs`
                                JOIN `config_store_tag` `ct`
                                     ON `ct`.`store_id` = `cs`.`store_id` AND `ct`.`is_active` = 1
                       GROUP BY `cs`.`store_id`) `tg`
        WHERE `tag_id` IN (`tg`.`tag_ids`)
    ) AS `store_tags` ON `p`.`project_id` = `store_tags`.`project_id` AND `store_tags`.`tag_ids` = `tg`.`tag_ids`
             JOIN `tag_object` AS `ppt` ON
            ((`ppt`.`tag_parent_object_id` = `p`.`project_id` AND `ppt`.`tag_type_id` = 3) OR
             (`ppt`.`tag_object_id` = `p`.`project_id`)) AND `ppt`.`is_deleted` = 0
             JOIN `ppload`.`ppload_files` AS `files` ON `files`.`id` = `ppt`.`tag_object_id` AND `files`.`active` = 1
    WHERE `p`.`amount_reports` IS NULL
    GROUP BY `sct2`.`lft`, `tg`.`tag_ids`;

    IF EXISTS(SELECT `table_name`
              FROM `INFORMATION_SCHEMA`.`TABLES`
              WHERE `table_schema` = DATABASE()
                AND `table_name` = 'stat_cat_prod_count')
    THEN
        RENAME TABLE `stat_cat_prod_count` TO `old_stat_cat_prod_count`, `tmp_stat_cat_prod_count` TO `stat_cat_prod_count`;

    ELSE
        RENAME TABLE `tmp_stat_cat_prod_count` TO `stat_cat_prod_count`;

    END IF;


    DROP TABLE IF EXISTS `old_stat_cat_prod_count`;

END$$

DELIMITER ;


CALL `generate_stat_cat_prod_count`;



DROP PROCEDURE IF EXISTS `generate_stat_cat_prod_count_w_spam`;

DELIMITER $$

CREATE PROCEDURE `generate_stat_cat_prod_count_w_spam`()
BEGIN

    DROP TABLE IF EXISTS `tmp_stat_cat_prod_count_w_spam`;
    CREATE TABLE `tmp_stat_cat_prod_count_w_spam`
    (
        `project_category_id` int(11) NOT NULL,
        `tag_id`              int(11) NULL,
        `count_product`       int(11) NULL,
        INDEX `idx_tag` (`project_category_id`, `tag_id`)
    )
        ENGINE MEMORY
    AS
    SELECT `sct2`.`project_category_id`,
           NULL                             AS `tag_id`,
           count(DISTINCT `p`.`project_id`) AS `count_product`
    FROM `stat_cat_tree` AS `sct1`
             JOIN `stat_cat_tree` AS `sct2` ON `sct1`.`lft` BETWEEN `sct2`.`lft` AND `sct2`.`rgt`
             LEFT JOIN `stat_projects` AS `p` ON `p`.`project_category_id` = `sct1`.`project_category_id`
    GROUP BY `sct2`.`project_category_id`

    UNION

    SELECT `sct2`.`project_category_id`,
           `tg`.`tag_ids`                   AS `tag_id`,
           count(DISTINCT `p`.`project_id`) AS `count_product`
    FROM `stat_cat_tree` AS `sct1`
             JOIN `stat_cat_tree` AS `sct2` ON `sct1`.`lft` BETWEEN `sct2`.`lft` AND `sct2`.`rgt`
             JOIN `stat_projects` AS `p` ON `p`.`project_category_id` = `sct1`.`project_category_id`
             JOIN (SELECT `cs`.`store_id`, GROUP_CONCAT(`ct`.`tag_id`) AS `tag_ids`
                   FROM `config_store` `cs`
                            JOIN `config_store_tag` `ct` ON `ct`.`store_id` = `cs`.`store_id` AND `ct`.`is_active` = 1
                   GROUP BY `cs`.`store_id`) `tg`
             JOIN (
        SELECT DISTINCT `project_id`, `tag_ids`
        FROM `stat_project_tagids`
                 JOIN (SELECT `cs`.`store_id`, GROUP_CONCAT(`ct`.`tag_id`) AS `tag_ids`
                       FROM `config_store` `cs`
                                JOIN `config_store_tag` `ct`
                                     ON `ct`.`store_id` = `cs`.`store_id` AND `ct`.`is_active` = 1
                       GROUP BY `cs`.`store_id`) `tg`
        WHERE `tag_id` IN (`tg`.`tag_ids`)
    ) AS `store_tags` ON `p`.`project_id` = `store_tags`.`project_id` AND `store_tags`.`tag_ids` = `tg`.`tag_ids`
             JOIN `tag_object` AS `ppt` ON
            ((`ppt`.`tag_parent_object_id` = `p`.`project_id` AND `ppt`.`tag_type_id` = 3) OR
             (`ppt`.`tag_object_id` = `p`.`project_id`)) AND `ppt`.`is_deleted` = 0
             JOIN `ppload`.`ppload_files` AS `files` ON `files`.`id` = `ppt`.`tag_object_id` AND `files`.`active` = 1

    GROUP BY `sct2`.`lft`, `tg`.`tag_ids`;

    IF EXISTS(SELECT `table_name`
              FROM `INFORMATION_SCHEMA`.`TABLES`
              WHERE `table_schema` = DATABASE()
                AND `table_name` = 'stat_cat_prod_count_w_spam')
    THEN
        RENAME TABLE `stat_cat_prod_count_w_spam` TO `old_stat_cat_prod_count_w_spam`, `tmp_stat_cat_prod_count_w_spam` TO `stat_cat_prod_count_w_spam`;

    ELSE
        RENAME TABLE `tmp_stat_cat_prod_count_w_spam` TO `stat_cat_prod_count_w_spam`;

    END IF;


    DROP TABLE IF EXISTS `old_stat_cat_prod_count_w_spam`;

END$$

DELIMITER ;


DROP PROCEDURE IF EXISTS `fetchCatTreeWithTags`;

DELIMITER $$

CREATE PROCEDURE `fetchCatTreeWithTags`(IN `STORE_ID` int(11),
                                        IN `TAGS` VARCHAR(255))
BEGIN
    DROP TABLE IF EXISTS `tmp_store_cat_tags`;
    CREATE TEMPORARY TABLE `tmp_store_cat_tags`
    (
        INDEX `idx_cat_id` (`project_category_id`)
    )
        ENGINE MEMORY
    AS
    SELECT `csc`.`store_id`, `csc`.`project_category_id`, `csc`.`order`, `pc`.`title`, `pc`.`lft`, `pc`.`rgt`
    FROM `config_store_category` AS `csc`
             JOIN `project_category` AS `pc` ON `pc`.`project_category_id` = `csc`.`project_category_id`
    WHERE `csc`.`store_id` = `STORE_ID`
    GROUP BY `csc`.`store_category_id`
    ORDER BY `csc`.`order`, `pc`.`title`;

    SET @`NEW_ORDER` := 0;

    UPDATE `tmp_store_cat_tags` SET `order` = (@`NEW_ORDER` := @`NEW_ORDER` + 10);

    SELECT `sct`.`lft`,
           `sct`.`rgt`,
           `sct`.`project_category_id`             AS `id`,
           `sct`.`title`,
           `scpc`.`count_product`                  AS `product_count`,
           `sct`.`xdg_type`,
           `sct`.`name_legacy`,
           if(`sct`.`rgt` - `sct`.`lft` = 1, 0, 1) AS `has_children`,
           (SELECT `project_category_id`
            FROM `stat_cat_tree` AS `sct2`
            WHERE `sct2`.`lft` < `sct`.`lft`
              AND `sct2`.`rgt` > `sct`.`rgt`
            ORDER BY `sct2`.`rgt` - `sct`.`rgt`
            LIMIT 1)                               AS `parent_id`
    FROM `tmp_store_cat_tags` AS `cfc`
             JOIN `stat_cat_tree` AS `sct` ON find_in_set(`cfc`.`project_category_id`, `sct`.`ancestor_id_path`)
             JOIN `stat_cat_prod_count` AS `scpc`
                  ON `sct`.`project_category_id` = `scpc`.`project_category_id` AND FIND_IN_SET(`scpc`.`tag_id`, `TAGS`)
    WHERE `cfc`.`store_id` = `STORE_ID`
    ORDER BY `cfc`.`order`, `sct`.`lft`;
END$$
DELIMITER ;


DROP PROCEDURE `generate_stat_project`;

DELIMITER $$

CREATE PROCEDURE `generate_stat_project`()
BEGIN
    DROP TABLE IF EXISTS `tmp_reported_projects`;
    CREATE TEMPORARY TABLE `tmp_reported_projects`
    (
        PRIMARY KEY `primary` (`project_id`)
    )
    AS
    SELECT `reports_project`.`project_id`        AS `project_id`,
           COUNT(`reports_project`.`project_id`) AS `amount_reports`,
           MAX(`reports_project`.`created_at`)   AS `latest_report`
    FROM `reports_project`
    WHERE (`reports_project`.`is_deleted` = 0 AND `reports_project`.`report_type` = 0)
    GROUP BY `reports_project`.`project_id`;

    DROP TABLE IF EXISTS `tmp_project_package_types`;
    CREATE TEMPORARY TABLE `tmp_project_package_types`
    (
        PRIMARY KEY `primary` (`project_id`)
    )
        ENGINE MyISAM
    AS
    SELECT `tag_object`.`tag_parent_object_id`          AS `project_id`,
           GROUP_CONCAT(DISTINCT `tag_object`.`tag_id`) AS `package_type_id_list`,
           GROUP_CONCAT(DISTINCT `tag`.`tag_fullname`)  AS `package_name_list`
    FROM `tag_object`
             JOIN
         `tag` ON `tag_object`.`tag_id` = `tag`.`tag_id`
             JOIN
         `ppload`.`ppload_files` `files` ON `files`.`id` = `tag_object`.`tag_object_id`
    WHERE `tag_object`.`tag_group_id` = 8
      AND `tag_object`.`is_deleted` = 0
      AND `files`.`active` = 1
    GROUP BY `tag_object`.`tag_parent_object_id`;

    DROP TABLE IF EXISTS `tmp_project_tags`;
    CREATE TEMPORARY TABLE `tmp_project_tags`
    (
        PRIMARY KEY `primary` (`tag_project_id`)
    )
        ENGINE MyISAM
    AS
    SELECT GROUP_CONCAT(`tag_name`) AS `tag_names`, `tag_project_id`
    FROM (
             SELECT DISTINCT `tag`.`tag_name`, `tgo`.`tag_object_id` AS `tag_project_id`
             FROM `tag_object` AS `tgo`
                      JOIN `tag` ON `tag`.`tag_id` = `tgo`.`tag_id`
             WHERE `tag_type_id` = 1 #project
               AND `tgo`.`is_deleted` = 0
             UNION ALL
             SELECT DISTINCT `tag`.`tag_name`, `tgo`.`tag_parent_object_id` AS `tag_project_id`
             FROM `tag_object` AS `tgo`
                      JOIN `tag` ON `tag`.`tag_id` = `tgo`.`tag_id`
                      JOIN `ppload`.`ppload_files` `files` ON `files`.`id` = `tgo`.`tag_object_id`
             WHERE `tag_type_id` = 3 #file
               AND `files`.`active` = 1
               AND `tgo`.`is_deleted` = 0
         ) `A`
    GROUP BY `tag_project_id`
    ORDER BY `tag_project_id`;


    DROP TABLE IF EXISTS `tmp_project_tagids`;
    CREATE TEMPORARY TABLE `tmp_project_tagids`
    (
        PRIMARY KEY `primary` (`tag_project_id`)
    )
        ENGINE MyISAM
    AS
    SELECT GROUP_CONCAT(`tag_id`) AS `tag_ids`, `tag_project_id`
    FROM (
             SELECT DISTINCT `tag`.`tag_id`, `tgo`.`tag_object_id` AS `tag_project_id`
             FROM `tag_object` AS `tgo`
                      JOIN `tag` ON `tag`.`tag_id` = `tgo`.`tag_id`
             WHERE `tag_type_id` = 1 #project
               AND `tgo`.`is_deleted` = 0
             UNION ALL
             SELECT DISTINCT `tag`.`tag_id`, `tgo`.`tag_parent_object_id` AS `tag_project_id`
             FROM `tag_object` AS `tgo`
                      JOIN `tag` ON `tag`.`tag_id` = `tgo`.`tag_id`
                      JOIN `ppload`.`ppload_files` `files` ON `files`.`id` = `tgo`.`tag_object_id`
             WHERE `tag_type_id` = 3 #file
               AND `files`.`active` = 1
               AND `tgo`.`is_deleted` = 0
         ) `A`
    GROUP BY `tag_project_id`
    ORDER BY `tag_project_id`;


    DROP TABLE IF EXISTS `tmp_stat_projects`;
    CREATE TABLE `tmp_stat_projects`
    (
        PRIMARY KEY `primary` (`project_id`),
        INDEX `idx_cat` (`project_category_id`),
        INDEX `idx_member` (`member_id`),
        INDEX `idx_source_url` (`source_url`(50))
    )
        ENGINE MyISAM
    AS
    SELECT `project`.`project_id`                                             AS `project_id`,
           `project`.`member_id`                                              AS `member_id`,
           `project`.`content_type`                                           AS `content_type`,
           `project`.`project_category_id`                                    AS `project_category_id`,
           `project`.`hive_category_id`                                       AS `hive_category_id`,
           `project`.`status`                                                 AS `status`,
           `project`.`uuid`                                                   AS `uuid`,
           `project`.`pid`                                                    AS `pid`,
           `project`.`type_id`                                                AS `type_id`,
           `project`.`title`                                                  AS `title`,
           `project`.`description`                                            AS `description`,
           `project`.`version`                                                AS `version`,
           `project`.`project_license_id`                                     AS `project_license_id`,
           `project`.`image_big`                                              AS `image_big`,
           `project`.`image_small`                                            AS `image_small`,
           `project`.`start_date`                                             AS `start_date`,
           `project`.`content_url`                                            AS `content_url`,
           `project`.`created_at`                                             AS `created_at`,
           `project`.`changed_at`                                             AS `changed_at`,
           `project`.`deleted_at`                                             AS `deleted_at`,
           `project`.`creator_id`                                             AS `creator_id`,
           `project`.`facebook_code`                                          AS `facebook_code`,
           `project`.`source_url`                                             AS `source_url`,
           `project`.`twitter_code`                                           AS `twitter_code`,
           `project`.`google_code`                                            AS `google_code`,
           `project`.`link_1`                                                 AS `link_1`,
           `project`.`embed_code`                                             AS `embed_code`,
           `project`.`ppload_collection_id`                                   AS `ppload_collection_id`,
           `project`.`validated`                                              AS `validated`,
           `project`.`validated_at`                                           AS `validated_at`,
           `project`.`featured`                                               AS `featured`,
           `project`.`ghns_excluded`                                          AS `ghns_excluded`,
           `project`.`amount`                                                 AS `amount`,
           `project`.`amount_period`                                          AS `amount_period`,
           `project`.`claimable`                                              AS `claimable`,
           `project`.`claimed_by_member`                                      AS `claimed_by_member`,
           `project`.`count_likes`                                            AS `count_likes`,
           `project`.`count_dislikes`                                         AS `count_dislikes`,
           `project`.`count_comments`                                         AS `count_comments`,
           `project`.`count_downloads_hive`                                   AS `count_downloads_hive`,
           `project`.`source_id`                                              AS `source_id`,
           `project`.`source_pk`                                              AS `source_pk`,
           `project`.`source_type`                                            AS `source_type`,
           `project`.`validated`                                              AS `project_validated`,
           `project`.`uuid`                                                   AS `project_uuid`,
           `project`.`status`                                                 AS `project_status`,
           `project`.`created_at`                                             AS `project_created_at`,
           `project`.`changed_at`                                             AS `project_changed_at`,
           laplace_score(`project`.`count_likes`, `project`.`count_dislikes`) AS `laplace_score`,
           `member`.`type`                                                    AS `member_type`,
           `member`.`member_id`                                               AS `project_member_id`,
           `member`.`username`                                                AS `username`,
           `member`.`profile_image_url`                                       AS `profile_image_url`,
           `member`.`city`                                                    AS `city`,
           `member`.`country`                                                 AS `country`,
           `member`.`created_at`                                              AS `member_created_at`,
           `member`.`paypal_mail`                                             AS `paypal_mail`,
           `project_category`.`title`                                         AS `cat_title`,
           `project_category`.`xdg_type`                                      AS `cat_xdg_type`,
           `project_category`.`name_legacy`                                   AS `cat_name_legacy`,
           `project_category`.`show_description`                              AS `cat_show_description`,
           `stat_plings`.`amount_received`                                    AS `amount_received`,
           `stat_plings`.`count_plings`                                       AS `count_plings`,
           `stat_plings`.`count_plingers`                                     AS `count_plingers`,
           `stat_plings`.`latest_pling`                                       AS `latest_pling`,
           `trp`.`amount_reports`                                             AS `amount_reports`,
           `tppt`.`package_type_id_list`                                      AS `package_types`,
           `tppt`.`package_name_list`                                         AS `package_names`,
           `t`.`tag_names`                                                    AS `tags`,
           `t2`.`tag_ids`                                                     AS `tag_ids`,
           `sdqy`.`amount`                                                    AS `count_downloads_quarter`,
           `project_license`.`title`                                          AS `project_license_title`
    FROM `project`
             JOIN `member` ON `member`.`member_id` = `project`.`member_id`
             JOIN `project_category` ON `project`.`project_category_id` = `project_category`.`project_category_id`
             LEFT JOIN `stat_plings` ON `stat_plings`.`project_id` = `project`.`project_id`
             LEFT JOIN `tmp_reported_projects` AS `trp` ON `trp`.`project_id` = `project`.`project_id`
             LEFT JOIN `tmp_project_package_types` AS `tppt` ON `tppt`.`project_id` = `project`.`project_id`
             LEFT JOIN `tmp_project_tags` AS `t` ON `t`.`tag_project_id` = `project`.`project_id`
             LEFT JOIN `tmp_project_tagids` AS `t2` ON `t2`.`tag_project_id` = `project`.`project_id`
             LEFT JOIN `stat_downloads_quarter_year` AS `sdqy` ON `sdqy`.`project_id` = `project`.`project_id`
             LEFT JOIN `project_license` ON `project_license`.`project_license_id` = `project`.`project_license_id`
    WHERE `member`.`is_deleted` = 0
      AND `member`.`is_active` = 1
      AND `project`.`type_id` = 1
      AND `project`.`status` = 100
      AND `project_category`.`is_active` = 1;

    RENAME TABLE `stat_projects` TO `old_stat_projects`, `tmp_stat_projects` TO `stat_projects`;

    DROP TABLE IF EXISTS `old_stat_projects`;
END$$

DELIMITER ;


