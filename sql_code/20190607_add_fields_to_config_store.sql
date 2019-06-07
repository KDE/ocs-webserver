ALTER TABLE `config_store`
	ADD COLUMN `is_show_in_menu` INT(1) NULL DEFAULT '0' AFTER `is_show_forum_news`;

ALTER TABLE `config_store`
	ADD COLUMN `is_show_real_domain_as_url` INT(1) NULL DEFAULT '0' AFTER `is_show_in_menu`;

