USE `pling`;
DROP procedure IF EXISTS `generate_stat_cat_prod_count`;

DELIMITER $$
USE `pling`$$
CREATE DEFINER=CURRENT_USER PROCEDURE `generate_stat_cat_prod_count`()
  BEGIN

    DROP TABLE IF EXISTS tmp_stat_cat_prod_count;
    CREATE TABLE tmp_stat_cat_prod_count
    (
      `project_category_id` int(11) NOT NULL,
      `package_type_id` int(11) NULL,
      `count_product` int(11) NULL,
      INDEX `idx_package` (`project_category_id`,`package_type_id`)
    )
      ENGINE Memory
      AS
        SELECT
          sct2.project_category_id,
          NULL as package_type_id,
          count(p.project_id) as count_product
        FROM stat_cat_tree as sct1
          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt
          LEFT JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id
        GROUP BY sct2.project_category_id

        UNION

        SELECT
          sct2.project_category_id,
          ppt.package_type_id,
          count(p.project_id) as count_product
        FROM stat_cat_tree as sct1
          JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt
          JOIN stat_projects as p ON p.project_category_id = sct1.project_category_id
          JOIN project_package_type AS ppt ON ppt.project_id = p.project_id
        GROUP BY sct2.lft, ppt.package_type_id
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

DROP EVENT IF EXISTS `e_generate_stat_cat_prod_count`;
CREATE
  DEFINER = CURRENT_USER
EVENT IF NOT EXISTS `e_generate_stat_cat_prod_count`
  ON SCHEDULE
    EVERY 2 MINUTE STARTS DATE_FORMAT(NOW(),'%Y-%m-%d 05:00:00')
  ON COMPLETION PRESERVE
  -- DISABLE ON SLAVE
  COMMENT 'Regenerates generate_stat_cat_prod_count table'
DO
  CALL pling.generate_stat_cat_prod_count();