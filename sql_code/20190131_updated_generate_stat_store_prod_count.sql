DROP PROCEDURE IF EXISTS `generate_stat_store_prod_count`;

DELIMITER $$
CREATE DEFINER = CURRENT_USER PROCEDURE `generate_stat_store_prod_count`()
  BEGIN

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
      ENGINE MEMORY
      AS
        SELECT
          `sct2`.`project_category_id`,
          NULL                             AS `tag_id`,
          count(DISTINCT `p`.`project_id`) AS `count_product`,
          null as `stores`
        FROM `stat_cat_tree` AS `sct1`
          JOIN `stat_cat_tree` AS `sct2` ON `sct1`.`lft` BETWEEN `sct2`.`lft` AND `sct2`.`rgt`
          LEFT JOIN `stat_projects` AS `p` ON `p`.`project_category_id` = `sct1`.`project_category_id`
        WHERE `p`.`amount_reports` IS NULL
        GROUP BY `sct2`.`project_category_id`

        UNION

        SELECT
          `sct2`.`project_category_id`,
          `tsst`.`tag_ids`                                                    AS `tag_id`,
          count(DISTINCT `p`.`project_id`)                                    AS `count_product`,
          group_concat(DISTINCT `tsst`.`store_id` ORDER BY `tsst`.`store_id`) AS `stores`
        FROM `stat_cat_tree` AS `sct1`
          JOIN `stat_cat_tree` AS `sct2` ON `sct1`.`lft` BETWEEN `sct2`.`lft` AND `sct2`.`rgt`
          JOIN `stat_projects` AS `p` ON `p`.`project_category_id` = `sct1`.`project_category_id`
          JOIN `tmp_stat_store_tagids` AS `tsst` ON `p`.`tag_ids`REGEXP CONCAT('[[:<:]](',REPLACE(tsst.tag_ids,',',')[[:>:]].+[[:<:]]('),')[[:>:]]')
        GROUP BY `sct2`.`project_category_id`, `tsst`.`tag_ids`;


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

  END$$

DELIMITER ;