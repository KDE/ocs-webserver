ALTER TABLE `tag_object`
	ADD COLUMN `tag_parent_object_id` INT NULL AFTER `tag_group_id`;
