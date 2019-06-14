CREATE TABLE `stat_cnt_projects_catid_memberid` AS
SELECT `project_category_id`, `member_id`, count(1) AS `cnt`
FROM `project` `pp`
WHERE `pp`.`status` = 100
  AND `pp`.`type_id` = 1
GROUP BY `project_category_id`, `member_id`;

ALTER TABLE `stat_cnt_projects_catid_memberid`
    ADD INDEX `idx_project_category_id` (`project_category_id`),
    ADD INDEX `idx_member_id` (`member_id`);


TRUNCATE TABLE `stat_cnt_projects_catid_memberid`;

INSERT INTO `stat_cnt_projects_catid_memberid`
SELECT `project_category_id`, `member_id`, count(1) AS `cnt`
FROM `project` `pp`
WHERE `pp`.`status` = 100
  AND `pp`.`type_id` = 1
GROUP BY `project_category_id`, `member_id`;



CREATE EVENT `e_generate_stat_cnt_projects_catid_memberid`
    ON SCHEDULE
        EVERY 1 DAY STARTS '2019-01-15 03:30:00'
    ON COMPLETION NOT PRESERVE
    ENABLE
    COMMENT ''
    DO
    BEGIN
        TRUNCATE TABLE `stat_cnt_projects_catid_memberid`;

        INSERT INTO `stat_cnt_projects_catid_memberid`
        SELECT `project_category_id`, `member_id`, count(1) AS `cnt`
        FROM `project` `pp`
        WHERE `pp`.`status` = 100
          AND `pp`.`type_id` = 1
        GROUP BY `project_category_id`, `member_id`;

    END;




	
