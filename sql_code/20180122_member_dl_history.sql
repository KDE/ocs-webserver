CREATE TABLE `member_download_history` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`member_id` VARCHAR(255) NOT NULL,
	`project_id` INT(11) NOT NULL,
	`file_id` INT(11) NOT NULL,
	`file_name` VARCHAR(255) NOT NULL,
	`file_type` VARCHAR(255) NOT NULL,
	`file_size` BIGINT(20) UNSIGNED NOT NULL,
	`downloaded_timestamp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `idx_time` (`downloaded_timestamp`),
	INDEX `idx_projectid` (`project_id`),
	INDEX `idx_memberid` (`member_id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=MyISAM
;
