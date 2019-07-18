

CREATE TABLE `sso_auth_token`
(
    `sso_auth_token_id` int(11)     NOT NULL AUTO_INCREMENT,
    `token_member_id`   int(11)     NOT NULL,
    `token_value`       varchar(45) NOT NULL,
    `token_action`      varchar(45) NOT NULL,
    `remember_me`       int(1)   DEFAULT '0',
    `token_created`     datetime DEFAULT NULL,
    `token_changed`     datetime DEFAULT NULL,
    `token_expired`     datetime DEFAULT NULL,
    PRIMARY KEY (`sso_auth_token_id`),
    KEY `idx_token` (`token_member_id`, `token_value`, `token_action`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = `latin1`;

DELIMITER $$

DROP TRIGGER IF EXISTS `sso_auth_token_BEFORE_INSERT`$$
CREATE DEFINER = CURRENT_USER TRIGGER `sso_auth_token_BEFORE_INSERT`
    BEFORE INSERT
    ON `sso_auth_token`
    FOR EACH ROW
BEGIN
    IF `NEW`.`token_created` IS NULL THEN
        SET `NEW`.`token_created` = NOW();
    END IF;
    IF `NEW`.`token_expired` IS NULL THEN
        SET `NEW`.`token_expired` = NOW() + INTERVAL 1 HOUR;
    END IF;
END$$
DELIMITER ;
