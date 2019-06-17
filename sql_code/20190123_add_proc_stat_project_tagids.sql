DROP PROCEDURE IF EXISTS `generate_stat_project_ids`;

DELIMITER $$
CREATE PROCEDURE `generate_stat_project_ids`()
BEGIN
    DROP TABLE IF EXISTS `tmp_stat_project_tagids`;
    CREATE TABLE `tmp_stat_project_tagids`
    (
        INDEX `idx_tag_id` (`tag_id`),
        INDEX `idx_project_id` (`project_id`)
    )
        ENGINE MyISAM
    AS

    SELECT DISTINCT `tag_id`, `project_id`
    FROM (
             SELECT DISTINCT `tag`.`tag_id`, `tgo`.`tag_object_id` AS `project_id`
             FROM `tag_object` AS `tgo`
                      JOIN `tag` ON `tag`.`tag_id` = `tgo`.`tag_id`
             WHERE `tag_type_id` = 1 #project
               AND `tgo`.`is_deleted` = 0
             UNION ALL
             SELECT DISTINCT `tag`.`tag_id`, `tgo`.`tag_parent_object_id` AS `project_id`
             FROM `tag_object` AS `tgo`
                      JOIN `tag` ON `tag`.`tag_id` = `tgo`.`tag_id`
                      JOIN `ppload`.`ppload_files` `files` ON `files`.`id` = `tgo`.`tag_object_id`
             WHERE `tag_type_id` = 3 #file
               AND `files`.`active` = 1
               AND `tgo`.`is_deleted` = 0
         ) `A`;
    RENAME TABLE `stat_project_tagids` TO `old_stat_project_tagids`, `tmp_stat_project_tagids` TO `stat_project_tagids`;
    DROP TABLE IF EXISTS `old_stat_project_tagids`;

END$$

DELIMITER ;

CREATE EVENT `e_generate_stat_project_ids`
    ON SCHEDULE
        EVERY 15 MINUTE STARTS '2019-01-23 15:43:00'
    ON COMPLETION NOT PRESERVE
    ENABLE
    COMMENT ''
    DO
    BEGIN
        CALL generate_stat_project_ids();
    END