USE `pling`;
DROP PROCEDURE IF EXISTS `generate_stat_views_today`;

DELIMITER $$
USE `pling`$$
CREATE DEFINER = CURRENT_USER PROCEDURE `generate_stat_views_today`()
  BEGIN

    DROP TABLE IF EXISTS `temp_stat_views_today`;

    CREATE TABLE `temp_stat_views_today` (
      `id`            INT      NOT NULL AUTO_INCREMENT,
      `project_id`    INT(11)  NOT NULL,
      `count_views`   INT(11)  NULL     DEFAULT 0,
      `count_visitor` INT(11)  NULL     DEFAULT 0,
      `last_view`     DATETIME NULL     DEFAULT NULL,
      PRIMARY KEY (`id`),
      INDEX `idx_project` (`project_id` ASC)
    )
      ENGINE = MyISAM
    AS
      SELECT
        project_id,
        COUNT(*)                               AS count_views,
        COUNT(DISTINCT `stat_page_views`.`ip`) AS `count_visitor`,
        MAX(`stat_page_views`.`created_at`)    AS `last_view`
      FROM stat_page_views
      WHERE (stat_page_views.`created_at`
      BETWEEN DATE_FORMAT(NOW(), '%Y-%m-%d 00:00') AND DATE_FORMAT(NOW(), '%Y-%m-%d 23:59')
      )
      GROUP BY project_id;

    IF EXISTS(SELECT table_name
              FROM INFORMATION_SCHEMA.TABLES
              WHERE table_schema = DATABASE()
                    AND table_name = 'stat_page_views_today_mv')

    THEN

      ALTER TABLE `stat_page_views_today_mv`
        RENAME TO `old_stat_views_today_mv`;

    END IF;

    ALTER TABLE `temp_stat_views_today`
      RENAME TO `stat_page_views_today_mv`;

    DROP TABLE IF EXISTS `old_stat_views_today_mv`;

  END $$

DELIMITER ;
