CREATE TABLE ppload.ppload_file_preview (
	`id` BIGINT(20) NOT NULL,
	`collection_id` INT(11) NOT NULL,
	`file_id` INT(11) NOT NULL,
	`url_preview` VARCHAR(255) NULL DEFAULT NULL,
	`url_thumb` VARCHAR(255) NULL DEFAULT NULL,
	`create_timestamp` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

ALTER TABLE ppload.ppload_file_preview
	ADD INDEX `idx_file` (`collection_id`, `file_id`);

ALTER TABLE `ppload_file_preview`
	ADD UNIQUE INDEX `UK` (`collection_id`, `file_id`);