ALTER TABLE `member_deactivation_log`
  ADD COLUMN `object_data` MEDIUMTEXT NULL DEFAULT NULL AFTER `deleted_at`;
