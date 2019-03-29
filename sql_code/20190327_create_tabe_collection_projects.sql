CREATE TABLE `collection_projects` (
	`collection_project_id` INT(11) NOT NULL,
	`project_id` INT(11) NOT NULL,
	`order` INT(11) NULL,
	`active` INT(1) UNSIGNED NULL DEFAULT '1',
	`created_at` DATETIME NULL,
	`changed_at` DATETIME NULL,
	`deleted_at` DATETIME NULL,
	PRIMARY KEY (`collection_project_id`, `project_id`)
)
COLLATE='latin1_swedish_ci'
;

