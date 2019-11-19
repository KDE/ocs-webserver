
DELIMITER $$
CREATE DEFINER=`root`@`%` PROCEDURE `generate_stat_project`()
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
    
    DROP TABLE IF EXISTS tmp_project_plings;
    CREATE TEMPORARY TABLE tmp_project_plings
    (PRIMARY KEY `primary` (project_id))
      ENGINE MyISAM
      AS
         select project_id,count(1) as count_plings
         from project_plings
         where is_active = 1 and is_deleted=0
         group by project_id;

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
          IFNULL(`stat_rating_project`.`likes`, 0) AS `count_likes`,
          IFNULL(`stat_rating_project`.`dislikes`, 0) AS `count_dislikes`,           
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
          IFNULL(`stat_rating_project`.`score`, 50) AS `laplace_score_old`,
          IFNULL(`stat_rating_project`.`score_with_pling`, 500) AS `laplace_score`,   
          IFNULL(`stat_rating_project`.`score_test`, 500) AS `laplace_score_test`,       
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
          `tmp_project_plings`.`count_plings` AS `count_plings`,                   
          `trp`.`amount_reports` AS `amount_reports`,
          `tppt`.`package_type_id_list` AS `package_types`,
          `tppt`.`package_name_list` AS `package_names`,
          `t`.`tag_names` AS `tags`,
          `t`.`tag_ids` AS `tag_ids`,
          `sdqy`.amount AS count_downloads_quarter,
          `project_license`.title AS project_license_title,
          (select count(1) from `project_follower` where `project_follower`.`project_id` = `project`.`project_id`) as count_follower
        FROM
          `project`
          JOIN `member` ON `member`.`member_id` = `project`.`member_id`
          JOIN `project_category` ON `project`.`project_category_id` = `project_category`.`project_category_id`
          LEFT JOIN `tmp_project_plings` ON `tmp_project_plings`.`project_id` = `project`.`project_id`
          LEFT JOIN `tmp_reported_projects` AS trp ON `trp`.`project_id` = `project`.`project_id`
          LEFT JOIN `tmp_project_package_types` AS tppt ON tppt.project_id = `project`.project_id
          LEFT JOIN `tmp_project_tags` AS t ON t.`tag_project_id` = project.`project_id`
          LEFT JOIN `stat_downloads_quarter_year` AS sdqy ON sdqy.project_id = project.project_id
          LEFT JOIN `project_license` ON project_license.project_license_id = project.project_license_id
          LEFT JOIN `stat_rating_project` ON stat_rating_project.project_id = project.project_id
          
        WHERE
          `member`.`is_deleted` = 0
          AND `member`.`is_active` = 1
          AND (`project`.`type_id` = 1 OR `project`.`type_id` = 3)
          AND `project`.`status` = 100
          AND `project_category`.`is_active` = 1
    ;
    
    RENAME TABLE stat_projects TO old_stat_projects, tmp_stat_projects TO stat_projects;

    DROP TABLE IF EXISTS old_stat_projects;
  END$$
DELIMITER ;
