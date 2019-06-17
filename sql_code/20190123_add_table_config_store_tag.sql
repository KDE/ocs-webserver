CREATE TABLE `config_store_tag`
(
    `config_store_tag_id` INT(11)         NOT NULL AUTO_INCREMENT,
    `store_id`            INT(11)         NOT NULL,
    `tag_id`              INT(11)         NOT NULL,
    `is_active`           INT(1) UNSIGNED NOT NULL DEFAULT '1',
    `created_at`          DATETIME        NULL     DEFAULT NULL,
    `changed_at`          DATETIME        NULL     DEFAULT NULL,
    `deleted_at`          DATETIME        NULL     DEFAULT NULL,
    PRIMARY KEY (`config_store_tag_id`)
)
;


CREATE TRIGGER `config_store_tag_before_insert`
    BEFORE INSERT
    ON `config_store_tag`
    FOR EACH ROW
BEGIN
    IF `NEW`.`created_at` IS NULL THEN
        SET `NEW`.`created_at` = NOW();
    END IF;
END;


INSERT INTO `config_store_tag`

SELECT NULL               AS `config_store_tag_id`,
       `s`.`store_id`     AS `store_id`,
       `s`.`package_type` AS `tag_id`,
       1                  AS `is_active`,
       NULL               AS `created_at`,
       NULL               AS `changed_at`,
       NULL               AS `deleted_at`
FROM `config_store` `s`
WHERE `s`.`package_type` IS NOT NULL;



