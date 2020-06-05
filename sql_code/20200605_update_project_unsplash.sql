USE `pling`;
DROP procedure IF EXISTS `update_project_unsplash`;

DELIMITER $$
USE `pling`$$
CREATE DEFINER=CURRENT_USER PROCEDURE `update_project_unsplash`()
BEGIN
  SET @cur_date = DATE_FORMAT(CURDATE(), '%Y%m%d');
  SET @str_sql = CONCAT('CREATE TABLE prj_unsplash_', @cur_date, ' AS SELECT project_id, status, 20 AS status_new, source_url FROM project where source_url like "%unsplash.com%" and status > 40;');
#  SELECT @str_sql;
  PREPARE stmt FROM @str_sql;
  EXECUTE stmt;

  SET @str_update = CONCAT("update project join prj_unsplash_",@cur_date, " as prj on prj.project_id = project.project_id set project.status = prj.status_new;");
  PREPARE stmt FROM @str_update;
  EXECUTE stmt;
END$$

DELIMITER ;