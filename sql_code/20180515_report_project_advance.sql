ALTER TABLE `reports_project`
	ADD COLUMN `report_type` INT(1) NOT NULL DEFAULT '0' COMMENT '1 = spam, 2 = fraud' AFTER `report_id`,
	ADD COLUMN `text` TEXT NOT NULL AFTER `reported_by`,
	ADD COLUMN `is_valid` INT(1) NOT NULL DEFAULT '0' COMMENT 'Admin can mark a report as valid' AFTER `is_deleted`;

