DROP TABLE IF EXISTS `member_deactivation_log`;
CREATE TABLE `member_deactivation_log`
(
    `log_id`          INT       NOT NULL AUTO_INCREMENT,
    `deactivation_id` INT       NOT NULL DEFAULT '0' COMMENT 'Id of the deactivation',
    `object_type_id`  INT       NOT NULL DEFAULT '0',
    `object_id`       INT       NOT NULL DEFAULT '0',
    `member_id`       INT       NOT NULL DEFAULT '0' COMMENT 'Member was deactivated from this user',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`)
)
    COLLATE = 'utf8_general_ci'
;

DROP TABLE IF EXISTS `member_deactivation_object_types`;
CREATE TABLE `member_deactivation_object_types`
(
    `object_type_id` INT         NULL,
    `object_system`  VARCHAR(50) NULL,
    `object_name`    VARCHAR(50) NULL,
    PRIMARY KEY (`object_type_id`)
)
    COLLATE = 'utf8_general_ci'
;

INSERT INTO `member_deactivation_object_types` (`object_type_id`, `object_system`, `object_name`)
VALUES (1, 'opendesktop', 'member');
INSERT INTO `member_deactivation_object_types` (`object_type_id`, `object_system`, `object_name`)
VALUES (2, 'opendesktop', 'member_email');
INSERT INTO `member_deactivation_object_types` (`object_type_id`, `object_system`, `object_name`)
VALUES (3, 'opendesktop', 'project');
INSERT INTO `member_deactivation_object_types` (`object_type_id`, `object_system`, `object_name`)
VALUES (4, 'opendesktop', 'comments');

INSERT INTO `member_deactivation_object_types` (`object_type_id`, `object_system`, `object_name`)
VALUES (20, 'gitlab', 'user');
INSERT INTO `member_deactivation_object_types` (`object_type_id`, `object_system`, `object_name`)
VALUES (21, 'gitlab', 'project');

INSERT INTO `member_deactivation_object_types` (`object_type_id`, `object_system`, `object_name`)
VALUES (30, 'discourse', 'user');
INSERT INTO `member_deactivation_object_types` (`object_type_id`, `object_system`, `object_name`)
VALUES (31, 'discourse', 'topic');


ALTER TABLE `member_deactivation_log`
    ADD COLUMN `is_deleted` INT NULL DEFAULT '0' COMMENT 'Is the user undeleted -> is_deleted = 1' AFTER `created_at`,
    ADD COLUMN `deleted_at` TIMESTAMP NULL AFTER `is_deleted`;
