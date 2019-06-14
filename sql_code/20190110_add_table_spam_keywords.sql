

DROP TABLE IF EXISTS `spam_keywords`;

CREATE TABLE `spam_keywords`
(
    `spam_key_id`         INT         NOT NULL AUTO_INCREMENT,
    `spam_key_word`       VARCHAR(45) NOT NULL,
    `spam_key_created_at` DATETIME    NULL,
    `spam_key_is_deleted` INT(1)      NULL DEFAULT 0,
    `spam_key_is_active`  INT(1)      NULL DEFAULT 1,
    PRIMARY KEY (`spam_key_id`)
)
    ENGINE = InnoDB;

DROP TRIGGER IF EXISTS `spam_keywords_BEFORE_INSERT`;

DELIMITER $$
CREATE DEFINER = CURRENT_USER TRIGGER `spam_keywords_BEFORE_INSERT`
    BEFORE INSERT
    ON `spam_keywords`
    FOR EACH ROW
BEGIN
    IF `NEW`.`spam_key_created_at` IS NULL THEN
        SET `NEW`.`spam_key_created_at` = NOW();
    END IF;
END$$
DELIMITER ;


INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('keto');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('spartan');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('ingredient');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('rdx surge');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('vashikaran');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('muscles');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('viagra');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('s3xual');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('erection');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('praltrix');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('s3x');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('herpes');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('male enhancement');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('astrology');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('megashare');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('body weight');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('diet');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('foreskin');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('fat loss');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('cream');
INSERT INTO `spam_keywords` (`spam_key_word`)
VALUES ('healthy');