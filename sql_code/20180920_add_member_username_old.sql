ALTER TABLE `member`
	ADD COLUMN `username_old` VARCHAR(255) NULL DEFAULT NULL AFTER `password_type_old`,
	ADD COLUMN `mail_old` VARCHAR(255) NULL DEFAULT NULL AFTER `username_old`;
