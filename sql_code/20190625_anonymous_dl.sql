CREATE TABLE `member_download_anonymous`
(
    `id`                   INT(11)             NOT NULL AUTO_INCREMENT,
    `user`          VARCHAR(255)        NOT NULL,
    `project_id`           INT(11)             NOT NULL,
    `file_id`              INT(11)             NOT NULL,
    `downloaded_timestamp` TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_time` (`downloaded_timestamp`),
    INDEX `idx_projectid` (`project_id`),
    INDEX `idx_fingerprint` (`user`)
)
    COLLATE = 'latin1_swedish_ci'
    ENGINE = MyISAM
;
