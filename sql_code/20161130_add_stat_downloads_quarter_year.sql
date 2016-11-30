
CREATE TABLE `stat_downloads_quarter_year` (
	`project_id` INT(11) NOT NULL DEFAULT '0',
	`project_category_id` INT(11) NOT NULL DEFAULT '0',
	`ppload_collection_id` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`amount` BIGINT(21) NOT NULL DEFAULT '0',
	`category_title` VARCHAR(100) NOT NULL COLLATE 'utf8_general_ci',
	INDEX `idx_project_id` (`project_id`),
	INDEX `idx_collection_id` (`ppload_collection_id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;