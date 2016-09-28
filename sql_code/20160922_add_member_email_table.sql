CREATE TABLE `member_email` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_member_id` int(11) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `email_primary` int(1) DEFAULT '0',
  `email_deleted` int(1) DEFAULT '0',
  `email_created` datetime DEFAULT NULL,
  `email_checked` datetime DEFAULT NULL,
  `email_verification_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`email_id`),
  KEY `idx_address` (`email_address`),
  KEY `idx_member` (`email_member_id`),
  KEY `idx_verification` (`email_verification_value`)
) ENGINE=InnoDB;

DELIMITER $$

DROP TRIGGER IF EXISTS member_email_BEFORE_INSERT$$
CREATE DEFINER = CURRENT_USER TRIGGER `member_email_BEFORE_INSERT` BEFORE INSERT ON `member_email` FOR EACH ROW
  BEGIN
    IF NEW.email_created IS NULL THEN

      SET NEW.email_created = NOW();

    END IF;

  END$$
DELIMITER ;

START TRANSACTION;

-- migrate all user email
TRUNCATE member_email;
INSERT INTO member_email (email_member_id, email_address, email_primary, email_created, email_checked, email_verification_value)
  SELECT member.member_id as email_member_id, member.mail as email_address, 1 as email_primary, member.created_at as email_created, member.created_at as email_checked, member.verificationVal as email_verification_value
  FROM member
  WHERE member.mail_checked AND member.is_active and member.mail IS NOT NULL
;

-- new column for member table
ALTER TABLE `member`
  ADD COLUMN `primary_mail` VARCHAR(255) NULL DEFAULT NULL AFTER `source_pk`;

-- after migrating to member_email we can drop the column `validationVal`
ALTER TABLE `member`
  DROP COLUMN `verificationVal`;

-- and copy addresses
UPDATE member SET primary_mail = mail;

COMMIT;
