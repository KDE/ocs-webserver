CREATE TABLE `member_email` (
  `email_id` INT NOT NULL AUTO_INCREMENT,
  `email_member_id` INT NOT NULL,
  `email_address` VARCHAR(254) NOT NULL,
  `email_primary` INT(1) NULL DEFAULT 0,
  `email_deleted` INT(1) NULL DEFAULT 0,
  `email_created` DATETIME NULL DEFAULT NULL,
  `email_checked` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`email_id`),
  INDEX `idx_address` (`email_address` ASC));

ALTER TABLE `member_email`
  ADD INDEX `idx_member` (`email_member_id` ASC);

ALTER TABLE `member_email`
  CHANGE COLUMN `email_address` `email_address` VARCHAR(255) NOT NULL ,
  ADD COLUMN `email_verification_value` VARCHAR(255) NULL DEFAULT NULL AFTER `email_checked`,
  ADD INDEX `idx_verification` (`email_verification_value` ASC);

DELIMITER $$

DROP TRIGGER IF EXISTS member_email_BEFORE_INSERT$$
CREATE DEFINER = CURRENT_USER TRIGGER `member_email_BEFORE_INSERT` BEFORE INSERT ON `member_email` FOR EACH ROW
  BEGIN
    IF NEW.email_created IS NULL THEN

      SET NEW.email_created = NOW();

    END IF;

  END$$
DELIMITER ;

-- migrate all user email
TRUNCATE member_email;
INSERT INTO member_email (email_member_id, email_address, email_primary, email_created, email_checked, email_verification_value)
  SELECT member.member_id as email_member_id, member.mail as email_address, 1 as email_primary, member.created_at as email_created, member.created_at as email_checked, member.verificationVal as email_verification_value
  FROM member
  WHERE member.mail_checked AND member.is_active and member.mail IS NOT NULL
;