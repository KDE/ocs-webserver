ALTER TABLE `section`
	CHANGE COLUMN `percent_of_support` `goal_amount` FLOAT(10,2) UNSIGNED NULL DEFAULT NULL COMMENT 'How much of the supporter donations goes to this section' AFTER `description`,
	ADD COLUMN `order` INT UNSIGNED NULL DEFAULT NULL AFTER `goal_amount`,
	ADD COLUMN `hide` INT UNSIGNED NULL DEFAULT '0' AFTER `order`;

ALTER TABLE `section`
	CHANGE COLUMN `goal_amount` `goal_amount` FLOAT(10,2) UNSIGNED NULL DEFAULT NULL AFTER `description`;
