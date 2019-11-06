CREATE TABLE `login_history` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`member_id` INT(11) NOT NULL,
	`login_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`ip` VARCHAR(50) NULL DEFAULT NULL,
	`ip_inet` VARBINARY(16) NULL DEFAULT NULL,
	`browser` VARCHAR(50) NULL DEFAULT NULL,
	`os` VARCHAR(50) NULL DEFAULT NULL,
	`architecture` VARCHAR(50) NULL DEFAULT NULL,
	`fingerprint` VARCHAR(50) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB
;

