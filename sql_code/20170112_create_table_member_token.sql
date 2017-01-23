CREATE TABLE `member_token` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `token_member_id` int(11) NOT NULL,
  `token_provider_name` varchar(45) NOT NULL,
  `token_value` varchar(45) NOT NULL,
  `token_provider_username` varchar(45) DEFAULT NULL,
  `token_fingerprint` varchar(45) DEFAULT NULL,
  `token_created` datetime DEFAULT NULL,
  `token_changed` datetime DEFAULT NULL,
  `token_deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`token_id`),
  KEY `idx_token` (`token_member_id`,`token_provider_name`,`token_value`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

DELIMITER $$

DROP TRIGGER IF EXISTS member_token_BEFORE_INSERT$$

CREATE DEFINER = CURRENT_USER TRIGGER `member_token_BEFORE_INSERT` BEFORE INSERT ON `member_token` FOR EACH ROW
  BEGIN
    IF NEW.token_created IS NULL THEN
      SET NEW.token_created = NOW();
    END IF;
  END$$
DELIMITER ;
