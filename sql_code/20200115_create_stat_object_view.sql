USE `pling`;

DROP TABLE IF EXISTS `stat_object_view`;
CREATE TABLE `stat_object_view` (
                                    `object_id` int(11) NOT NULL,
                                    `object_type` int(11) NOT NULL,
                                    `seen_at` datetime NOT NULL,
                                    `ip_inet` varbinary(16) NOT NULL,
                                    `member_id_viewer` int(11) DEFAULT '0',
                                    `ipv6` varchar(50) DEFAULT '',
                                    `ipv4` varchar(50) DEFAULT '',
                                    `fingerprint` varchar(50) DEFAULT '',
                                    `user_agent` varchar(255) DEFAULT '',
                                    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                                    PRIMARY KEY (`object_id`,`object_type`,`seen_at`,`ip_inet`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
    PARTITION BY HASH (EXTRACT(YEAR_MONTH FROM (`seen_at`)))
        PARTITIONS 48
;

INSERT stat_object_view SELECT object_id,object_type,from_unixtime(`seen_at` * 300) as seen_at, ip_inet, member_id_viewer, ipv6, ipv4, fingerprint, user_agent, created_at FROM stat_page_impression;

DROP TABLE IF EXISTS `stat_object_download`;
CREATE TABLE `stat_object_download` (
                                      `object_id` INT(11) NOT NULL,
                                      `object_type` INT(11) NOT NULL,
                                      `seen_at` DATETIME NOT NULL,
                                      `ip_inet` VARBINARY(16) NOT NULL,
                                      `member_id_viewer` INT(11) DEFAULT '0',
                                      `ipv6` VARCHAR(50) DEFAULT '',
                                      `ipv4` VARCHAR(50) DEFAULT '',
                                      `fingerprint` VARCHAR(50) DEFAULT '',
                                      `user_agent` VARCHAR(255) DEFAULT '',
                                      `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                                      PRIMARY KEY (`object_id` , `object_type` , `seen_at` , `ip_inet`)
)  ENGINE=INNODB DEFAULT CHARSET=LATIN1 PARTITION BY HASH (EXTRACT(YEAR_MONTH FROM (`seen_at`))) PARTITIONS 48
;

INSERT stat_object_download SELECT object_id,object_type, seen_at, ip_inet, member_id_viewer, ipv6, ipv4, fingerprint, user_agent, created_at FROM stat_file_download;
