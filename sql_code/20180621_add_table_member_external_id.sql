CREATE TABLE `member_external_id` (
  `external_id` VARCHAR(255) NOT NULL,
  `member_id`   INT(11)      NOT NULL,
  `created_at`  DATETIME     NULL,
  `is_deleted`  INT(1)       NULL,
  PRIMARY KEY (`external_id`),
  INDEX `idx_member` (`member_id` ASC)
)
  ENGINE = InnoDB;

DROP TRIGGER IF EXISTS `member_external_id_BEFORE_INSERT`;

DELIMITER $$
CREATE DEFINER = CURRENT_USER TRIGGER `member_external_id_BEFORE_INSERT`
  BEFORE INSERT
  ON `member_external_id`
  FOR EACH ROW
  BEGIN
    IF `NEW`.`created_at` IS NULL
    THEN
      SET `NEW`.`created_at` = NOW();
    END IF;
  END$$
DELIMITER ;

INSERT INTO `member_external_id` (`external_id`, `member_id`)
  SELECT
    SUBSTR(SHA(`member_id`), 1, 20) AS `external_id`,
    `member_id`
  FROM `member`
  WHERE `is_active` = 1 AND `is_deleted` = 0
  ORDER BY `member_id`;