CREATE TABLE `project_clone` (
  `project_clone_id`  INT(11)      NOT NULL AUTO_INCREMENT,
  `project_id`        INT(11)      NOT NULL,
  `project_id_parent` INT(11)      NULL     DEFAULT NULL  COMMENT 'Project Id of the clone on opendesktop',
  `external_link`     VARCHAR(255) NULL     DEFAULT NULL  COMMENT 'External Link to the original project',
  `member_id`         INT(11)      NULL     DEFAULT NULL  COMMENT 'Who send the report',
  `text`              TEXT         NULL,
  `is_deleted`        INT(1)       NOT NULL DEFAULT '0',
  `is_valid`          INT(1)       NOT NULL DEFAULT '0'   COMMENT 'Admin can mark a report as valid',
  `created_at`        TIMESTAMP    NULL     DEFAULT CURRENT_TIMESTAMP,
  `changed_at`        TIMESTAMP    NULL     DEFAULT NULL,
  `deleted_at`        TIMESTAMP    NULL     DEFAULT NULL,
  PRIMARY KEY (`project_clone_id`),
  INDEX `idxReport` (`project_id`, `member_id`, `is_deleted`, `created_at`)
)
  ENGINE = InnoDB
;