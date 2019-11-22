USE ppload;

ALTER TABLE `ppload_files`
	ADD COLUMN `has_torrent` INT(1) UNSIGNED NULL DEFAULT '0' AFTER `updated_ip`;
