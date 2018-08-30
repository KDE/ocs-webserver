CREATE TABLE `project_moderation_type` (
  `project_moderation_type_id` INT(11)      NOT NULL AUTO_INCREMENT,
  `name`                       VARCHAR(100) NOT NULL,
  `tag_id`                     INT(11)               DEFAULT NULL
  COMMENT 'if exist insert/remove project tag_id relation',
  PRIMARY KEY (`project_moderation_type_id`)
)
  ENGINE = INNODB;


CREATE TABLE `project_moderation` (
  `project_moderation_id`      INT(11)   NOT NULL AUTO_INCREMENT,
  `project_moderation_type_id` INT(11)   NOT NULL,
  `project_id`                 INT(11)   NOT NULL,
  `created_by`                 INT(11)   NOT NULL,
  `updated_by`                 INT(11)            DEFAULT NULL,
  `note`                       TEXT      NOT NULL,
  `is_deleted`                 INT(1)    NOT NULL DEFAULT '0',
  `is_valid`                   INT(1)    NOT NULL DEFAULT '0'
  COMMENT 'Admin can mark a report as valid',
  `created_at`                 TIMESTAMP NULL     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                 DATETIME           DEFAULT NULL,
  PRIMARY KEY (`project_moderation_id`),
  FOREIGN KEY (`project_moderation_type_id`)
  REFERENCES `project_moderation_type` (`project_moderation_type_id`)
)
  ENGINE = INNODB;

