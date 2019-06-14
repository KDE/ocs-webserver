CREATE TABLE IF NOT EXISTS `suspicion_log`
(
    `suspicion_id` INT          NOT NULL AUTO_INCREMENT,
    `project_id`   INT(11)      NOT NULL,
    `member_id`    INT(11)      NOT NULL,
    `http_referer` VARCHAR(255) NULL,
    `http_origin`  VARCHAR(255) NULL,
    `client_ip`    VARCHAR(45)  NULL,
    `user_agent`   VARCHAR(255) NULL,
    `suspicious`   INT(1)       NULL DEFAULT 0,
    PRIMARY KEY (`suspicion_id`),
    INDEX `idxProject` (`project_id` ASC),
    INDEX `idxMember` (`member_id` ASC)
)
    ENGINE = MyISAM;