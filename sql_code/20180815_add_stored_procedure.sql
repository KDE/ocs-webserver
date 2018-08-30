DELIMITER $$
CREATE DEFINER=CURRENT_USER PROCEDURE `fetchCatTreeForStore`(IN STORE_ID int(11))
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
      JOIN `stat_cat_prod_count` AS `scpc` ON `sct`.`project_category_id` = `scpc`.`project_category_id` AND `scpc`.`package_type_id` is null
    WHERE cfc.store_id = STORE_ID
    ORDER BY cfc.`order`, sct.lft;
  END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=CURRENT_USER PROCEDURE `fetchCatTreeWithPackage`(IN STORE_ID int(11), IN PACKAGE_TYPE int(11))
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
  END$$
DELIMITER ;
