ALTER TABLE `member`
    ADD COLUMN `pling_excluded` INT(1) NOT NULL DEFAULT '0'
        AFTER `primary_mail`;

INSERT INTO `activity_log_types` (`activity_log_type_id`, `type_text`)
VALUES ('319', 'BackendUserPlingExcluded');


ALTER TABLE `member_dl_plings`
    ADD COLUMN `is_member_pling_excluded` INT(1) UNSIGNED NULL DEFAULT '0'
        AFTER `is_pling_excluded`;
