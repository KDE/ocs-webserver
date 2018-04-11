ALTER TABLE `member_dl_plings`
	ADD COLUMN `is_license_missing` INT(1) UNSIGNED NULL DEFAULT '0' AFTER `probably_payout_amount`,
	ADD COLUMN `is_source_missing` INT(1) UNSIGNED NULL DEFAULT '0' AFTER `is_license_missing`,
	ADD COLUMN `is_pling_excluded` INT(1) UNSIGNED NULL DEFAULT '0' AFTER `is_source_missing`;

