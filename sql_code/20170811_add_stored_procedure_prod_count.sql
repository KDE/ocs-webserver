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
          parent.project_category_id,
          ppt.package_type_id,
          COUNT(DISTINCT project.project_id) AS count_product
        FROM
          stat_cat_tree AS node,
          stat_cat_tree AS parent,
          stat_projects AS project
          LEFT JOIN project_package_type AS ppt ON project.project_id = ppt.project_id
        WHERE
          node.lft BETWEEN parent.lft AND parent.rgt
          AND node.project_category_id = project.project_category_id AND node.is_active = 1
        --        AND ppt.package_type_id = 5
        --        AND find_in_set(1, package_types)
        GROUP BY parent.project_category_id, ppt.package_type_id
        ORDER BY parent.lft;

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