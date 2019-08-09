CREATE TABLE `section_support` (
	`section_support_id` INT NOT NULL AUTO_INCREMENT,
	`support_id` INT NULL,
	`section_id` INT NULL,
	`amount` DOUBLE NULL DEFAULT NULL,
	`tier` DOUBLE NULL DEFAULT NULL,
	`period` VARCHAR(1) NULL DEFAULT 'Y',
	`period_frequency` INT NULL DEFAULT '1',
	`is_active` INT NULL DEFAULT '1',
	`created_at` DATETIME NULL,
	`changed_at` DATETIME NULL,
	`deleted_at` DATETIME NULL,
	PRIMARY KEY (`section_support_id`)
)
COLLATE='latin1_swedish_ci'
;

