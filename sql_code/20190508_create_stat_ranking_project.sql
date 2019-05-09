DROP PROCEDURE IF EXISTS `generate_stat_rating_project`;

DELIMITER $$
CREATE PROCEDURE `generate_stat_rating_project`()
  BEGIN
    DROP TABLE IF EXISTS `tmp_stat_rating_project`;
    CREATE TABLE `tmp_stat_rating_project`
    (
      `project_id`  int(11) NOT NULL,
      `likes`       int(11) NOT NULL,
      `dislikes`    int(11) NOT NULL,
      `votes_total` int(11) NOT NULL,
      `score`       int(11) NOT NULL,
      PRIMARY KEY `primary` (`project_id`)
    )
      AS
        SELECT `pr`.`project_id`,
          sum(`pr`.`user_like`)                                          AS `likes`,
          sum(`pr`.`user_dislike`)                                       AS `dislikes`,
          sum(`pr`.`user_like`) + sum(`pr`.`user_dislike`)               AS `votes_total`,
          laplace_score(sum(`pr`.`user_like`), sum(`pr`.`user_dislike`)) AS `score`
        FROM `project_rating` AS `pr`
        WHERE `pr`.`rating_active` = 1
              AND `pr`.`comment_id` = 0
        GROUP BY `pr`.`project_id`;

    IF EXISTS(SELECT `table_name`
              FROM `INFORMATION_SCHEMA`.`TABLES`
              WHERE `table_schema` = DATABASE()
                    AND `table_name` = 'stat_rating_project')
    THEN
      RENAME TABLE `stat_rating_project` TO `old_stat_rating_project`, `tmp_stat_rating_project` TO `stat_rating_project`;

    ELSE
      RENAME TABLE `tmp_stat_rating_project` TO `stat_rating_project`;

    END IF;


    DROP TABLE IF EXISTS `old_stat_rating_project`;
  END$$

DELIMITER ;


CREATE DEFINER = CURRENT_USER EVENT `e_generate_stat_rating_project` ON SCHEDULE
  EVERY '5' MINUTE
    STARTS '2019-05-08 05:00:00'
  ON COMPLETION PRESERVE
  ENABLE
  COMMENT 'Regenerates stat_rating_project table'
DO
  CALL generate_stat_rating_project();