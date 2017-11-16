ALTER TABLE `project`
	ADD COLUMN `pling_excluded` INT(1) NOT NULL DEFAULT '0' COMMENT 'Project was excluded from pling payout' AFTER `spam_checked`;

INSERT INTO `activity_log_types` (`activity_log_type_id`, `type_text`) VALUES ('318', 'BackendProjectPlingExcluded');