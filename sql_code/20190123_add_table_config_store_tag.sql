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

CREATE TRIGGER `config_store_tag_before_insert` BEFORE INSERT ON `config_store_tag` FOR EACH ROW BEGIN
	 IF NEW.created_at IS NULL THEN
      SET NEW.created_at = NOW();
    END IF;
END;

