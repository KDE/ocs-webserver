ALTER TABLE `member_external_id`
	ADD COLUMN `gitlab_user_id` INT(1) NULL DEFAULT NULL AFTER `is_deleted`;
