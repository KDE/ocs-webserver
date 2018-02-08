ALTER TABLE `reports_comment`
	ADD COLUMN `user_ip` VARCHAR(255) NULL AFTER `created_at`,
	ADD COLUMN `user_ip2` VARCHAR(255) NULL AFTER `user_ip`;
