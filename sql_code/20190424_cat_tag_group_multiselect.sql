ALTER TABLE `tag_group`
	ADD COLUMN `is_multi_select` INT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is this Tag-Group a multiselect Dropdown?' AFTER `group_legacy_name`;
