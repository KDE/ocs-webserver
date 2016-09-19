RENAME TABLE `file_types` TO `package_types`;
ALTER TABLE `package_types`
	CHANGE COLUMN `filetype_id` `package_type_id` INT(11) NOT NULL AUTO_INCREMENT FIRST;



ALTER TABLE `project_file_type`
	ALTER `filetype_id` DROP DEFAULT;
ALTER TABLE `project_file_type`
	CHANGE COLUMN `filetype_id` `package_type_id` INT(11) NOT NULL AFTER `file_id`;

RENAME TABLE `project_file_type` TO `project_package_type`;
