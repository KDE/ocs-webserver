USE `pling`;
DROP procedure IF EXISTS `solr_query_import`;

DELIMITER $$
USE `pling`$$
CREATE PROCEDURE `solr_query_import` ()
  BEGIN
    SELECT
      project_id,
      project.member_id                                                                                                 AS project_member_id,
      project.project_category_id                                                                                       AS project_category_id,
      project.title                                                                                                     AS project_title,
      description,
      image_small,
      member.username,
      member.firstname,
      member.lastname,
      pc.title                                                                                                          AS cat_title,
      `project`.`count_likes`                                                                                           AS `count_likes`,
      `project`.`count_dislikes`                                                                                        AS `count_dislikes`,
      (ROUND(((`project`.`count_likes` + 6) / ((`project`.`count_likes` + `project`.`count_dislikes`) + 12)), 2) *
       100)                                                                                                             AS `laplace_score`,
      project.created_at,
      project.changed_at
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN project_category AS pc ON pc.project_category_id = project.project_category_id
    WHERE project.`status` = 100 AND project.`type_id` = 1 AND member.`is_active` = 1 AND pc.`is_active` = 1;
  END$$

DELIMITER ;

USE `pling`;
DROP procedure IF EXISTS `solr_query_delta_import`;

DELIMITER $$
USE `pling`$$
CREATE PROCEDURE `solr_query_delta_import` (IN projectID INT(11))
  BEGIN
    SELECT
      project_id,
      project.member_id                                                                                                 AS project_member_id,
      project.project_category_id                                                                                       AS project_category_id,
      project.title                                                                                                     AS project_title,
      description,
      image_small,
      member.username,
      member.firstname,
      member.lastname,
      pc.title                                                                                                          AS cat_title,
      `project`.`count_likes`                                                                                           AS `count_likes`,
      `project`.`count_dislikes`                                                                                        AS `count_dislikes`,
      (ROUND(((`project`.`count_likes` + 6) / ((`project`.`count_likes` + `project`.`count_dislikes`) + 12)), 2) *
       100)                                                                                                             AS `laplace_score`,
      project.created_at,
      project.changed_at
    FROM project
      JOIN member ON member.member_id = project.member_id
      JOIN project_category AS pc ON pc.project_category_id = project.project_category_id
    WHERE project_id = projectID;
  END$$

DELIMITER ;

USE `pling`;
DROP procedure IF EXISTS `solr_query_delta`;

DELIMITER $$
USE `pling`$$
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


USE `pling`;
DROP procedure IF EXISTS `solr_query_deleted_pk`;

DELIMITER $$
USE `pling`$$
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

USE `pling`;

DELIMITER $$

DROP TRIGGER IF EXISTS pling.project_BEFORE_UPDATE$$
USE `pling`$$
CREATE DEFINER=`root`@`%` TRIGGER `pling`.`project_BEFORE_UPDATE` BEFORE UPDATE ON `project` FOR EACH ROW
  BEGIN
    SET NEW.changed_at = NOW();
  END$$
DELIMITER ;

USE `pling`;

DELIMITER $$

USE `pling`;

DELIMITER $$

DROP TRIGGER IF EXISTS pling.member_BEFORE_UPDATE$$
USE `pling`$$
CREATE DEFINER = CURRENT_USER TRIGGER `pling`.`member_BEFORE_UPDATE` BEFORE UPDATE ON `member` FOR EACH ROW
  BEGIN
    SET NEW.changed_at = NOW();
  END$$
DELIMITER ;

USE `pling`;

DELIMITER $$

DROP TRIGGER IF EXISTS pling.project_category_BEFORE_INSERT$$
USE `pling`$$
CREATE DEFINER = CURRENT_USER TRIGGER `pling`.`project_category_BEFORE_INSERT` BEFORE INSERT ON `project_category` FOR EACH ROW
  BEGIN
    IF NEW.created_at IS NULL THEN
      SET NEW.created_at = NOW();
    END IF;
  END$$
DELIMITER ;
USE `pling`;

DELIMITER $$

DROP TRIGGER IF EXISTS pling.project_category_BEFORE_UPDATE$$
USE `pling`$$
CREATE DEFINER = CURRENT_USER TRIGGER `pling`.`project_category_BEFORE_UPDATE` BEFORE UPDATE ON `project_category` FOR EACH ROW
  BEGIN
    SET NEW.changed_at = NOW();
  END$$
DELIMITER ;
