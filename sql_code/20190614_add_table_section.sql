CREATE TABLE `section` (
	`section_id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NOT NULL,
	`description` VARCHAR(255) NULL,
	`is_active` INT(1) UNSIGNED NULL DEFAULT '1',
	`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`deleted_at` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`section_id`)
)
COMMENT='Every section has categories, see table section_categories. And every download belongs to a category and to a section.'
COLLATE='latin1_swedish_ci'
;

CREATE TABLE `section_category` (
	`section_category_id` INT NOT NULL AUTO_INCREMENT,
	`section_id` INT NOT NULL,
	`project_category_id` INT NOT NULL,
	PRIMARY KEY (`section_category_id`)
)
COMMENT='every section has n categories'
COLLATE='latin1_swedish_ci'
;

