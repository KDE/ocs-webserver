USE `pling-import`;
DROP procedure IF EXISTS `solr_query_import`;
DELIMITER $$
USE `pling-import`$$
CREATE PROCEDURE `solr_query_import` ()
  BEGIN
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
      (ROUND(((`project`.`count_likes` + 6) / ((`project`.`count_likes` + `project`.`count_dislikes`) + 12)), 2) *
       100)                       AS `laplace_score`,
      project.created_at,
      project.changed_at,
      tcs.stores,
      tcs.ancestor_id_path        AS `cat_id_ancestor_path`
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN tmp_cat_store AS tcs ON project.project_category_id = tcs.project_category_id
    WHERE project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND tcs.`is_active` = 1;
  END$$
DELIMITER ;

USE `pling-import`;
DROP procedure IF EXISTS `solr_query_delta_import`;
DELIMITER $$
USE `pling-import`$$
CREATE PROCEDURE `solr_query_delta_import` (IN projectID INT(11))
  BEGIN
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
      (ROUND(((`project`.`count_likes` + 6) / ((`project`.`count_likes` + `project`.`count_dislikes`) + 12)), 2) *
       100)                       AS `laplace_score`,
      project.created_at,
      project.changed_at,
      tcs.stores,
      tcs.ancestor_id_path        AS `cat_id_ancestor_path`
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN tmp_cat_store AS tcs ON project.project_category_id = tcs.project_category_id
    WHERE project_id = projectID;
  END$$
DELIMITER ;

USE `pling-import`;
DROP procedure IF EXISTS `solr_query_delta`;
DELIMITER $$
USE `pling-import`$$
CREATE PROCEDURE `solr_query_delta` (IN lastIndexed varchar(255))
  BEGIN
    SELECT project_id
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN project_category AS pc ON pc.project_category_id = project.project_category_id
    WHERE project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND pc.`is_active` = 1 AND
          project.changed_at > lastIndexed;
  END$$
DELIMITER ;


USE `pling-import`;
DROP procedure IF EXISTS `solr_query_deleted_pk`;
DELIMITER $$
USE `pling-import`$$
CREATE PROCEDURE `solr_query_deleted_pk` (IN lastIndexed VARCHAR(255))
  BEGIN
    SELECT project_id
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN project_category AS pc ON pc.project_category_id = project.project_category_id
    WHERE project.deleted_at > lastIndexed OR member.deleted_at > lastIndexed OR
          (project.changed_at > lastIndexed AND project.status < 100);
  END$$
DELIMITER ;

USE `pling-import`;
DELIMITER $$
DROP TRIGGER IF EXISTS project_BEFORE_UPDATE$$
USE `pling-import`$$
CREATE DEFINER=CURRENT_USER TRIGGER `project_BEFORE_UPDATE` BEFORE UPDATE ON `project` FOR EACH ROW
  BEGIN
    SET NEW.changed_at = NOW();
  END$$
DELIMITER ;


USE `pling-import`;
DELIMITER $$
DROP TRIGGER IF EXISTS member_BEFORE_UPDATE$$
USE `pling-import`$$
CREATE DEFINER = CURRENT_USER TRIGGER `member_BEFORE_UPDATE` BEFORE UPDATE ON `member` FOR EACH ROW
  BEGIN
    SET NEW.changed_at = NOW();
  END$$
DELIMITER ;

USE `pling-import`;
DELIMITER $$
DROP TRIGGER IF EXISTS project_category_BEFORE_INSERT$$
USE `pling-import`$$
CREATE DEFINER = CURRENT_USER TRIGGER `project_category_BEFORE_INSERT` BEFORE INSERT ON `project_category` FOR EACH ROW
  BEGIN
    IF NEW.created_at IS NULL THEN
      SET NEW.created_at = NOW();
    END IF;
  END$$
DELIMITER ;

USE `pling-import`;
DELIMITER $$
DROP TRIGGER IF EXISTS project_category_BEFORE_UPDATE$$
USE `pling-import`$$
CREATE DEFINER = CURRENT_USER TRIGGER `project_category_BEFORE_UPDATE` BEFORE UPDATE ON `project_category` FOR EACH ROW
  BEGIN
    SET NEW.changed_at = NOW();
  END$$
DELIMITER ;
