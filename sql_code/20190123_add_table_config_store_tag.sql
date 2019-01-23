CREATE TABLE `config_store_tag` (
	`config_store_tag_id` INT NOT NULL AUTO_INCREMENT,
	`store_id` INT NOT NULL,
	`tag_id` INT NOT NULL,
	`is_active` INT(1) UNSIGNED NOT NULL DEFAULT '1',
	`created_at` DATETIME NOT NULL,
	`changed_at` DATETIME NOT NULL,
	`deleted_at` DATETIME NOT NULL,
	PRIMARY KEY (`config_store_tag_id`)
)
;

