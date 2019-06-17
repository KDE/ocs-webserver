CREATE DEFINER =`root`@`%` EVENT `e_generate_stat_projects_source_url`
    ON SCHEDULE
        EVERY 5 MINUTE STARTS '2018-11-19 11:57:15'
    ON COMPLETION NOT PRESERVE
    ENABLE
    COMMENT ''
    DO
    BEGIN

        CREATE TABLE `stat_projects_source_url_tmp`
        (
            PRIMARY KEY `primary` (`project_id`),
            INDEX `idx_proj` (`project_id`),
            INDEX `idx_member` (`member_id`),
            INDEX `idx_source_url` (`source_url`(50))
        )
            ENGINE MyISAM
        AS
        SELECT `p`.`project_id`,
               `p`.`member_id`,
               TRIM(TRAILING '/' FROM `p`.`source_url`) AS `source_url`,
               `p`.`created_at`,
               `p`.`changed_at`
        FROM `stat_projects` `p`
        WHERE `p`.`source_url` IS NOT NULL
          AND `p`.`source_url` <> ''
          AND `p`.`status` = 100;
        RENAME TABLE `stat_projects_source_url` TO `stat_projects_source_url_old`;
        RENAME TABLE `stat_projects_source_url_tmp` TO `stat_projects_source_url`;
        DROP TABLE `stat_projects_source_url_old`;

    END