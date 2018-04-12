ALTER TABLE `member_dl_plings`
	ADD COLUMN `is_license_missing` INT(1) UNSIGNED NULL DEFAULT '0' AFTER `probably_payout_amount`,
	ADD COLUMN `is_source_missing` INT(1) UNSIGNED NULL DEFAULT '0' AFTER `is_license_missing`,
	ADD COLUMN `is_pling_excluded` INT(1) UNSIGNED NULL DEFAULT '0' AFTER `is_source_missing`;


DROP VIEW stat_member_dl_curent_month;
CREATE VIEW stat_member_dl_curent_month
AS
SELECT DATE_FORMAT(NOW(),'%Y%m') AS `yearmonth`,`p`.`project_id` AS `project_id`,`p`.`project_category_id` AS `project_category_id`,`d`.`owner_id` AS `member_id`,`m`.`mail` AS `mail`,`m`.`paypal_mail` AS `paypal_mail`, COUNT(`d`.`id`) AS `num_downloads`,`c`.`dl_pling_factor` AS `dl_pling_factor`,((COUNT(`d`.`id`) * `c`.`dl_pling_factor`) / 100) AS `amount`,
(CASE WHEN ISNULL(`tag`.`tag_item_id`) THEN 1 ELSE 0 END) AS `is_license_missing`,
(CASE WHEN (((`c`.`source_required` = 1) AND (`p`.`source_url` IS NOT NULL) AND (LENGTH(`p`.`source_url`) > 0)) OR (`c`.`source_required` = 0)) THEN 0 ELSE 1 END) AS `is_source_missing`,
`p`.`pling_excluded` AS `is_pling_excluded`, 
NULL AS `created_at`, 
NULL AS `updated_at`
FROM ((((`ppload`.`ppload_files_downloaded` `d`
JOIN `member` `m` ON(((`m`.`member_id` = `d`.`owner_id`) AND (`m`.`is_active` = 1))))
JOIN `project` `p` ON(((CAST(`p`.`ppload_collection_id` AS UNSIGNED) = `d`.`collection_id`) AND (`p`.`status` = 100))))
JOIN `project_category` `c` ON((`c`.`project_category_id` = `p`.`project_category_id`)))
LEFT JOIN .`tag_object` `tag` ON(((`tag`.`tag_type_id` = 1) AND (`tag`.`tag_group_id` = 7) AND (`tag`.`tag_object_id` = `p`.`project_id`))))
WHERE ((`d`.`downloaded_timestamp` >= CONCAT(
LEFT(NOW(),7),'-01 00:00:00')) AND (`p`.`ppload_collection_id` IS NOT NULL) AND (LENGTH(`p`.`ppload_collection_id`) > 0) AND (NOT((`p`.`ppload_collection_id` LIKE '!%'))))
GROUP BY `d`.`owner_id`,`p`.`project_id`
;


DROP VIEW stat_member_dl_last_month;
CREATE VIEW stat_member_dl_last_month
AS
SELECT DATE_FORMAT((NOW() - INTERVAL 1 MONTH),'%Y%m') AS `yearmonth`,`p`.`project_id` AS `project_id`,`p`.`project_category_id` AS `project_category_id`,`d`.`owner_id` AS `member_id`,`m`.`mail` AS `mail`,`m`.`paypal_mail` AS `paypal_mail`, COUNT(`d`.`id`) AS `num_downloads`,`c`.`dl_pling_factor` AS `dl_pling_factor`,((COUNT(`d`.`id`) * `c`.`dl_pling_factor`) / 100) AS `amount`, 
(CASE WHEN ISNULL(`tag`.`tag_item_id`) THEN 1 ELSE 0 END) AS `is_license_missing`,
(CASE WHEN (((`c`.`source_required` = 1) AND (`p`.`source_url` IS NOT NULL) AND (LENGTH(`p`.`source_url`) > 0)) OR (`c`.`source_required` = 0)) THEN 0 ELSE 1 END) AS `is_source_missing`,
`p`.`pling_excluded` AS `is_pling_excluded`, 
NULL AS `created_at`, NULL AS `updated_at`
FROM (((`ppload`.`ppload_files` `d`
JOIN `member` `m` ON(((`m`.`member_id` = `d`.`owner_id`) AND (`m`.`is_active` = 1))))
JOIN `project` `p` ON(((CAST(`p`.`ppload_collection_id` AS UNSIGNED) = `d`.`collection_id`) AND (`p`.`status` = 100))))
JOIN `project_category` `c` ON((`c`.`project_category_id` = `p`.`project_category_id`))
LEFT JOIN .`tag_object` `tag` ON(((`tag`.`tag_type_id` = 1) AND (`tag`.`tag_group_id` = 7) AND (`tag`.`tag_object_id` = `p`.`project_id`))))
WHERE ((`d`.`downloaded_timestamp` >= CONCAT(
LEFT((NOW() - INTERVAL 1 MONTH),7),'-01 00:00:00')) AND (`p`.`ppload_collection_id` IS NOT NULL) AND (LENGTH(`p`.`ppload_collection_id`) > 0) AND (NOT((`p`.`ppload_collection_id` LIKE '!%'))))
GROUP BY `d`.`owner_id`,`p`.`project_id`
;


INSERT INTO `activity_log_types` (`activity_log_type_id`, `type_text`) VALUES ('70', 'ProjectLicenseChanged');



