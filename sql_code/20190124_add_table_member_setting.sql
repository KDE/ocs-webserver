CREATE TABLE `member_setting_group`
(
    `member_setting_group_id` INT(11)     NOT NULL AUTO_INCREMENT,
    `title`                   VARCHAR(45) NOT NULL,
    PRIMARY KEY (`member_setting_group_id`)
);


CREATE TABLE `member_setting_item`
(
    `member_setting_item_id`  INT(11)     NOT NULL AUTO_INCREMENT,
    `title`                   VARCHAR(45) NOT NULL,
    `member_setting_group_id` INT(11)     NOT NULL,
    PRIMARY KEY (`member_setting_item_id`)
);


CREATE TABLE `member_setting_value`
(
    `member_setting_value_id` INT(11)      NOT NULL AUTO_INCREMENT,
    `member_setting_item_id`  INT(11)      NOT NULL,
    `value`                   VARCHAR(100) NOT NULL,
    `member_id`               INT(11)      NOT NULL,
    `created_at`              TIMESTAMP    NOT NULL DEFAULT now(),
    `changed_at`              DATETIME     NULL     DEFAULT NULL,
    `deleted_at`              DATETIME     NULL     DEFAULT NULL,
    `is_active`               INT(1)       NOT NULL DEFAULT 1,
    PRIMARY KEY (`member_setting_value_id`)
);
