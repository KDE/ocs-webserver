ALTER TABLE `support`
	ADD COLUMN `type_id` INT(1) UNSIGNED NULL DEFAULT '1' COMMENT '1 = signup, 2 = payment' AFTER `create_time`,
	ADD COLUMN `period` VARCHAR(50) NULL AFTER `amount`;


UPDATE support p
SET p.type_id = 2
WHERE p.type_id = 1;

ALTER TABLE `support`
	ADD COLUMN `subscription_id` VARCHAR(255) NULL AFTER `type_id`;