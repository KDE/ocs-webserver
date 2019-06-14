CREATE TABLE `config_store_tag_group`
(
    `config_store_taggroup_id` INT(11)         NOT NULL AUTO_INCREMENT,
    `store_id`                 INT(11)         NOT NULL,
    `tag_group_id`             INT(11)         NOT NULL,
    `is_active`                INT(1) UNSIGNED NOT NULL DEFAULT '1',
    `created_at`               DATETIME        NULL     DEFAULT NULL,
    `changed_at`               DATETIME        NULL     DEFAULT NULL,
    `deleted_at`               DATETIME        NULL     DEFAULT NULL,
    PRIMARY KEY (`config_store_taggroup_id`)
)
    COLLATE = 'latin1_swedish_ci'
    ENGINE = InnoDB
;
