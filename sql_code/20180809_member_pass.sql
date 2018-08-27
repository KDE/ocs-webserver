ALTER TABLE `member`
	ADD COLUMN `password_old` VARCHAR(255) NULL AFTER `pling_excluded`,
	ADD COLUMN `password_type_old` INT(1) NULL AFTER `password_old`;

