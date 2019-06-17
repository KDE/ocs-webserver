CREATE TABLE `stat_page_views_48h` LIKE `stat_page_views`;

DELIMITER $$
DROP TRIGGER IF EXISTS `stat_page_views_AFTER_INSERT`$$
CREATE TRIGGER `stat_page_views_AFTER_INSERT`
    AFTER INSERT
    ON `stat_page_views`
    FOR EACH ROW
BEGIN
    #insert also into table stat_page_views_48h
    INSERT INTO `stat_page_views_48h` (`stat_page_views_id`, `project_id`, `ip`, `member_id`, `created_at`)
    VALUES (`new`.`stat_page_views_id`, `new`.`project_id`, `new`.`ip`, `new`.`member_id`, `new`.`created_at`);
END$$


DROP EVENT IF EXISTS `e_generate_page_views_48h`;
CREATE EVENT `e_generate_page_views_48h`
    ON SCHEDULE
        EVERY 1 DAY STARTS '2018-11-20 05:00:00'
    ON COMPLETION PRESERVE
    ENABLE
    COMMENT 'Delete old page_view data from table stat_page_views_48h'
    DO
    DELETE
    FROM `stat_page_views_48h`
    WHERE `created_at` <= subdate(now(), 2);


DELIMITER ;