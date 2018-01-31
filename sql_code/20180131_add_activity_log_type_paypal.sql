INSERT INTO `pling`.`activity_log_types` (`activity_log_type_id`, `type_text`) VALUES ('110', 'MEMBER_PAYPAL_CHANGED');
UPDATE `pling`.`activity_log_types` SET `activity_log_type_id`='401' WHERE  `activity_log_type_id`=103;
UPDATE `pling`.`activity_log_types` SET `activity_log_type_id`='410', `type_text`='MemberPaypalChanged' WHERE  `activity_log_type_id`=110;
INSERT INTO `pling`.`activity_log_types` (`activity_log_type_id`, `type_text`) VALUES ('402', 'MemberEmailChanged');