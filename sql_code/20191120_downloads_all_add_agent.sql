ALTER TABLE `ppload_files_downloaded_all`
	ADD COLUMN `user_agent` VARCHAR(255) NULL DEFAULT NULL AFTER `link_type`,
	ADD COLUMN `app_id` INT NULL DEFAULT NULL AFTER `user_agent`
	ADD COLUMN `fingerprint` VARCHAR(255) NULL DEFAULT NULL AFTER `app_id`
;
