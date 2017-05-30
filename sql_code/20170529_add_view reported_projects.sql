USE `pling`;
CREATE OR REPLACE
  ALGORITHM = UNDEFINED
  DEFINER = CURRENT_USER
  SQL SECURITY DEFINER
VIEW `view_reported_projects` AS
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

CREATE OR REPLACE
  ALGORITHM = TEMPTABLE
  DEFINER = CURRENT_USER
  SQL SECURITY DEFINER
VIEW `stat_projects_v` AS
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
    `member`.`type` AS `member_type`,
    `member`.`member_id` AS `project_member_id`,
    `project`.`changed_at` AS `project_changed_at`,
    (ROUND(((`project`.`count_likes` + 6) / ((`project`.`count_likes` + `project`.`count_dislikes`) + 12)), 2) * 100) AS `laplace_score`,
    `member`.`username` AS `username`,
    `member`.`profile_image_url` AS `profile_image_url`,
    `member`.`city` AS `city`,
    `member`.`country` AS `country`,
    `member`.`created_at` AS `member_created_at`,
    `member`.`paypal_mail` AS `paypal_mail`,
    `project_category`.`title` AS `cat_title`,
    `project_category`.`xdg_type` AS `cat_xdg_type`,
    `project_category`.`name_legacy` AS `cat_name_legacy`,
    `stat_plings`.`amount_received` AS `amount_received`,
    `stat_plings`.`count_plings` AS `count_plings`,
    `stat_plings`.`count_plingers` AS `count_plingers`,
    `stat_plings`.`latest_pling` AS `latest_pling`,
    `view_reported_projects`.`amount_reports` AS `amount_reports`
  FROM
    `project`
    JOIN
    `member` ON ((`member`.`member_id` = `project`.`member_id`))
    JOIN
    `project_category` ON ((`project`.`project_category_id` = `project_category`.`project_category_id`))
    LEFT JOIN
    `stat_plings` ON ((`stat_plings`.`project_id` = `project`.`project_id`))
    LEFT JOIN
    `view_reported_projects` ON ((`view_reported_projects`.`project_id` = `project`.`project_id`))
  WHERE
    ((`member`.`is_deleted` = 0)
     AND (`member`.`is_active` = 1)
     AND (`project`.`type_id` = 1)
     AND (`project`.`status` = 100))
;
