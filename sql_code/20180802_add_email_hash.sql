ALTER TABLE `member_email`
	ADD COLUMN `email_hash` VARCHAR(255) NOT NULL AFTER `email_verification_value`;

ALTER TABLE `member_email`
	ADD INDEX `idx_hash` (`email_hash`);

UPDATE member_email me
SET me.email_hash = MD5(me.email_address)
;
