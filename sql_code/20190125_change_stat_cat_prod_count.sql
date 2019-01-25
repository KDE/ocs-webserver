DROP PROCEDURE IF EXISTS `generate_stat_cat_prod_count`;

DELIMITER $$

CREATE PROCEDURE `generate_stat_cat_prod_count`()
BEGIN

    DROP TABLE IF EXISTS tmp_stat_cat_prod_count;
    CREATE TABLE tmp_stat_cat_prod_count
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
          ppt.tag_id as tag_id,
          count(distinct p.project_id) as count_product
        FROM stat_cat_tree as sct1
          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt
          JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id
          JOIN tag_object AS ppt ON ppt.tag_parent_object_id = p.project_id AND ppt.tag_type_id = 3 AND ppt.is_deleted = 0
          JOIN ppload.ppload_files AS files ON files.id = ppt.tag_object_id AND files.active = 1
        GROUP BY sct2.lft, ppt.tag_id
        
        UNION

        SELECT
          sct2.project_category_id,
          ppt.tag_id as tag_id,
          count(distinct p.project_id) as count_product
        FROM stat_cat_tree as sct1
          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt
          JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id
          JOIN tag_object AS ppt ON ppt.tag_object_id = p.project_id AND ppt.is_deleted = 0
          JOIN ppload.ppload_files AS files ON files.id = ppt.tag_object_id AND files.active = 1
        GROUP BY sct2.lft, ppt.tag_id
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

END$$

DELIMITER ;


CALL generate_stat_cat_prod_count;


DROP PROCEDURE IF EXISTS `fetchCatTreeWithTags`;

DELIMITER $$

CREATE PROCEDURE `fetchCatTreeWithTags`(
	IN `STORE_ID` int(11),
	IN `TAGS` VARCHAR(255)
)
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
      JOIN `stat_cat_prod_count` AS `scpc` ON `sct`.`project_category_id` = `scpc`.`project_category_id` AND FIND_IN_SET(`scpc`.`tag_id`,TAGS)
    WHERE `cfc`.`store_id` = STORE_ID
    ORDER BY `cfc`.`order`, `sct`.`lft`;
  END$$
DELIMITER ;


