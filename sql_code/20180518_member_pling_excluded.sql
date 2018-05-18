ALTER TABLE `member`
	ADD COLUMN `pling_excluded` INT(1) NOT NULL DEFAULT '0' AFTER `primary_mail`;

INSERT INTO `pling-import`.`activity_log_types` (`activity_log_type_id`, `type_text`) VALUES ('319', 'BackendUserPlingExcluded');
