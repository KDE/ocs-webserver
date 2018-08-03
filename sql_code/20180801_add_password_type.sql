#Create Table member_password_types
CREATE TABLE `member_password_types` (
	`password_type_id` INT(10) UNSIGNED NOT NULL,
	`name`             VARCHAR(50)      NOT NULL,
	`description`      VARCHAR(255)     NOT NULL,
	PRIMARY KEY (`password_type_id`)
)
	ENGINE = InnoDB;

INSERT INTO `member_password_types` (`password_type_id`, `name`, `description`) VALUES (0, 'MD5', 'Default OCS Password Hash');
INSERT INTO `member_password_types` (`password_type_id`, `name`, `description`) VALUES (1, 'SHA', 'Hive Password Hash');

#Add field in table member
ALTER TABLE `member`
	ADD COLUMN `password_type` INT(1) NOT NULL DEFAULT '0'
COMMENT 'Type:  0 = MD5 (OCS), 1 = SHA (Hive)'
	AFTER `password`;

#Update Hive-Members
UPDATE `member` `m`
SET `m`.`password_type` = 1
WHERE `m`.`source_id` = 1;


