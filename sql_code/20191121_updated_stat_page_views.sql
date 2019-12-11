USE `pling`;

CREATE TABLE IF NOT EXISTS `stat_page_impression`
(
    `object_id`        int(11)       NOT NULL,
    `object_type`      int(11)       NOT NULL,
    `seen_at`          int(11)       NOT NULL,
    `ip_inet`          varbinary(16) NOT NULL,
    `member_id_viewer` int(11)            DEFAULT NULL,
    `ipv6`             varchar(50)        DEFAULT NULL,
    `ipv4`             varchar(50)        DEFAULT NULL,
    `fingerprint`      varchar(50)        DEFAULT NULL,
    `user_agent`       varchar(255)       DEFAULT NULL,
    `created_at`       timestamp     NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`object_id`, `object_type`, `seen_at`, `ip_inet`)
) ENGINE = InnoDB
  DEFAULT CHARSET = `latin1`;



# #insert/update page views into table stat_page_views_mv
# USE `pling`;
#
# #Update mechanism
# START TRANSACTION;
#
# DROP TABLE IF EXISTS `stat_page_views_mv_new`;
# CREATE TABLE `stat_page_views_mv_new` LIKE `stat_page_views_mv`;
#
# INSERT INTO `stat_page_views_mv_new`
# SELECT `stat_page_views`.`project_id`         AS `project_id`,
#        COUNT(1)                               AS `count_views`,
#        COUNT(DISTINCT `stat_page_views`.`ip`) AS `count_visitor`,
#        MAX(`stat_page_views`.`created_at`)    AS `last_view`
# FROM `stat_page_views`
# WHERE `stat_page_views`.`created_at` >= CONCAT(CURDATE(), ' 00:00:00')
# GROUP BY `stat_page_views`.`project_id`;
#
# RENAME TABLE `stat_page_views_mv` TO `stat_page_views_mv_old`;
# RENAME TABLE `stat_page_views_mv_new` TO `stat_page_views_mv`;
# DROP TABLE IF EXISTS `stat_page_views_mv_old`;
#
# COMMIT;
#
