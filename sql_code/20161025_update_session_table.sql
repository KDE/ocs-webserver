ALTER TABLE `session`
  CHANGE COLUMN `idsession` `session_id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE COLUMN `member_id` `member_id` INT(11) NOT NULL ,
  CHANGE COLUMN `uuid` `remember_me_id` VARCHAR(255) NOT NULL ,
  ADD COLUMN `expiry` DATETIME NOT NULL AFTER `remember_me_id`,
  DROP INDEX `member_uuid` ,
  ADD INDEX `idx_remember` (`member_id` ASC, `remember_me_id` ASC, `expiry` ASC);

UPDATE `session`
SET expiry = DATE_ADD( `session`.created, INTERVAL + 31536000 SECOND)
;
