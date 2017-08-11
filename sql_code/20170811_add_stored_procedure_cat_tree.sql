USE `pling`;
DROP procedure IF EXISTS `generate_stat_cat_tree`;

DELIMITER $$
CREATE DEFINER=CURRENT_USER PROCEDURE `generate_stat_cat_tree`()
  BEGIN

    DROP TABLE IF EXISTS tmp_stat_cat_tree;
    CREATE TABLE tmp_stat_cat_tree
    (
      `project_category_id` int(11) NOT NULL,
      `lft` int(11) NOT NULL,
      `rgt` int(11) NOT NULL,
      `title` varchar(100) NOT NULL,
      `name_legacy` varchar(50) NULL,
      `is_active` int(1),
      `orderPos` int(11) NULL,
      `depth` int(11) NOT NULL,
      `ancestor_id_path` varchar(100),
      `ancestor_path` varchar(256),
      `ancestor_path_legacy` varchar(256),
      PRIMARY KEY `primary` (project_category_id, lft, rgt)
    )
      ENGINE Memory
      AS
        SELECT
          pc.project_category_id,
          pc.lft,
          pc.rgt,
          pc.title,
          pc.name_legacy,
          pc.is_active,
          pc.orderPos,
          count(pc.lft) - 1                                        AS depth,
          GROUP_CONCAT(pc2.project_category_id ORDER BY pc2.lft)   AS ancestor_id_path,
          GROUP_CONCAT(pc2.title ORDER BY pc2.lft SEPARATOR ' | ') AS ancestor_path,
          GROUP_CONCAT(IF(LENGTH(TRIM(pc2.name_legacy))>0,pc2.name_legacy,pc2.title) ORDER BY pc2.lft SEPARATOR ' | ') AS ancestor_path_legacy
        FROM project_category AS pc, project_category AS pc2
        WHERE (pc.lft BETWEEN pc2.lft AND pc2.rgt) AND pc.is_active = 1 and pc2.is_active = 1
        GROUP BY pc.lft -- HAVING depth >= 1
        ORDER BY pc.lft, pc.orderPos
    ;

    IF EXISTS(SELECT table_name
              FROM INFORMATION_SCHEMA.TABLES
              WHERE table_schema = DATABASE()
                    AND table_name = 'stat_cat_tree')

    THEN

      RENAME TABLE stat_cat_tree TO old_stat_cat_tree, tmp_stat_cat_tree TO stat_cat_tree;

    ELSE

      RENAME TABLE tmp_stat_cat_tree TO stat_cat_tree;

    END IF;


    DROP TABLE IF EXISTS old_stat_cat_tree;

  END$$
DELIMITER ;

DROP EVENT IF EXISTS `e_generate_stat_cat_tree`;
CREATE
  DEFINER = CURRENT_USER
EVENT IF NOT EXISTS `e_generate_stat_cat_tree`
  ON SCHEDULE
    EVERY 60 MINUTE STARTS DATE_FORMAT(NOW(),'%Y-%m-%d 05:00:00')
  ON COMPLETION PRESERVE
  -- DISABLE ON SLAVE
  COMMENT 'Regenerates generate_stat_cat_tree table'
DO
  CALL pling.generate_stat_cat_tree();