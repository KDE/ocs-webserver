ALTER TABLE `support`
    ADD COLUMN `type_id` INT(1) UNSIGNED NULL DEFAULT '0' COMMENT '0 = onetime payment, 1 = subsscription signup, 2 = subsscription payment' AFTER `create_time`,
    ADD COLUMN `period` VARCHAR(50) NULL AFTER `amount`;


UPDATE `support` `p`
SET `p`.`type_id` = 0;

ALTER TABLE `support`
    ADD COLUMN `subscription_id` VARCHAR(255) NULL AFTER `type_id`;

ALTER TABLE `support`
    ADD COLUMN `period_frequency` VARCHAR(50) NULL DEFAULT NULL AFTER `period`;