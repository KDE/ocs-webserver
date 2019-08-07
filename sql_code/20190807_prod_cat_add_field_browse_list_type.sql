ALTER TABLE `project_category`
	ADD COLUMN `browse_list_type` INT(1) NOT NULL DEFAULT '0' AFTER `source_required`;


CREATE TABLE `browse_list_types` (
	`browse_list_type_id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NOT NULL,
	`desc` VARCHAR(255) NULL DEFAULT NULL,
	`is_active` INT(1) UNSIGNED NULL DEFAULT '1',
	`deleted_at` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`browse_list_type_id`)
)
ENGINE=InnoDB
AUTO_INCREMENT=4
;

INSERT INTO `pling`.`browse_list_types` (`browse_list_type_id`, `type_name`, `type_desc`) VALUES ('0', 'default', 'Default List type is list');
INSERT INTO `pling`.`browse_list_types` (`type_name`, `type_desc`) VALUES ('picture', 'Gridview with big pictures');
INSERT INTO `pling`.`browse_list_types` (`type_name`, `type_desc`) VALUES ('music', 'Gridview with play buttons');