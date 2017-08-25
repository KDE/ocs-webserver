CREATE TABLE `donation` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`member_id` INT(11) NOT NULL COMMENT 'Supporter',
	`status_id` INT(11) NULL DEFAULT '0' COMMENT 'Stati der donation: 0 = inactive, 1 = active (donated), 2 = payed successfull, 99 = deleted',
	`create_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation-time',
	`donation_time` TIMESTAMP NULL DEFAULT NULL COMMENT 'When was a project plinged?',
	`active_time` TIMESTAMP NULL DEFAULT NULL COMMENT 'When did paypal say, that this donation was payed successfull',
	`delete_time` TIMESTAMP NULL DEFAULT NULL,
	`amount` DOUBLE(10,2) NULL DEFAULT '0.00' COMMENT 'Amount of money',
	`comment` VARCHAR(140) NULL DEFAULT NULL COMMENT 'Comment from the supporter',
	`payment_provider` VARCHAR(45) NULL DEFAULT NULL,
	`payment_reference_key` VARCHAR(255) NULL DEFAULT NULL COMMENT 'uniquely identifies the request',
	`payment_transaction_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'uniquely identify caller (developer, facilliator, marketplace) transaction',
	`payment_raw_message` VARCHAR(2000) NULL DEFAULT NULL COMMENT 'the raw text message ',
	`payment_raw_error` VARCHAR(2000) NULL DEFAULT NULL,
	`payment_status` VARCHAR(45) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `status_id` (`status_id`),
	INDEX `member_id` (`member_id`),
	INDEX `DONATION_IX_01` (`status_id`, `member_id`, `active_time`, `amount`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
AUTO_INCREMENT=8
;

