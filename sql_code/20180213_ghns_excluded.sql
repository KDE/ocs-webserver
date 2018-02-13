INSERT INTO `pling`.`member_role` (`member_role_id`, `title`, `shortname`, `is_active`) VALUES ('400', 'Moderator', 'moderator', '1');

ALTER TABLE `project` CHANGE COLUMN `approved` `ghns_excluded` INT(1) NULL DEFAULT '0' AFTER `featured`;
