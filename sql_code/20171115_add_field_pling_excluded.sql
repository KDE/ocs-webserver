ALTER TABLE `project`
	ADD COLUMN `pling_excluded` INT(1) NOT NULL DEFAULT '0' COMMENT 'Project was excluded from pling payout' AFTER `spam_checked`;

INSERT INTO `activity_log_types` (`activity_log_type_id`, `type_text`) VALUES ('318', 'BackendProjectPlingExcluded');


DROP VIEW stat_member_dl_curent_month;
CREATE VIEW stat_member_dl_curent_month AS
SELECT DATE_FORMAT(NOW(),'%Y%m') AS `yearmonth`,`p`.`project_id` AS `project_id`,`p`.`project_category_id` AS `project_category_id`,`d`.`owner_id` AS `member_id`,`m`.`mail` AS `mail`,`m`.`paypal_mail` AS `paypal_mail`, COUNT(`d`.`id`) AS `num_downloads`,`c`.`dl_pling_factor` AS `dl_pling_factor`,((COUNT(`d`.`id`) * `c`.`dl_pling_factor`) / 100) AS `amount`, NULL AS `created_at`, NULL AS `updated_at`
FROM (((`ppload`.`ppload_files_downloaded` `d`
JOIN `pling`.`member` `m` ON(((`m`.`member_id` = `d`.`owner_id`) AND (`m`.`is_active` = 1))))
JOIN `pling`.`project` `p` ON(((CAST(`p`.`ppload_collection_id` AS UNSIGNED) = `d`.`collection_id`) AND (`p`.`pling_excluded` = 0) AND (`p`.`status` = 100))))
JOIN `pling`.`project_category` `c` ON((`c`.`project_category_id` = `p`.`project_category_id`)))
WHERE ((`d`.`downloaded_timestamp` >= CONCAT(
LEFT(NOW(),7),'-01 00:00:00')) AND (`p`.`ppload_collection_id` IS NOT NULL) AND (LENGTH(`p`.`ppload_collection_id`) > 0) AND (NOT((`p`.`ppload_collection_id` LIKE '!%'))))
GROUP BY `d`.`owner_id`,`p`.`project_id`
ORDER BY COUNT(`d`.`id`) DESC;
