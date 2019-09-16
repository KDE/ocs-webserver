DROP TABLE IF EXISTS micro_payout;

CREATE TABLE `micro_payout` (
	`yearmonth` INT(6) NOT NULL,
	`type` INT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 = download, 1 = mediaviews',
	`project_id` INT(11) NOT NULL,
	`project_category_id` INT(11) NOT NULL,
	`collection_id` INT(11) NOT NULL,
	`file_id` INT(11) NOT NULL,
	`member_id` INT(11) NOT NULL,
	`mail` VARCHAR(255) NULL DEFAULT NULL,
	`paypal_mail` VARCHAR(255) NULL DEFAULT NULL,
	`num_plings` BIGINT(21) NULL DEFAULT NULL,
	`category_pling_factor` DECIMAL(3,2) NOT NULL DEFAULT '0.00',
	`amount_plings` DECIMAL(11,2) NULL DEFAULT NULL,
	`is_license_missing` INT(1) UNSIGNED NULL DEFAULT '0',
	`is_source_missing` INT(1) UNSIGNED NULL DEFAULT '0',
	`is_pling_excluded` INT(1) UNSIGNED NULL DEFAULT '0',
	`is_member_pling_excluded` INT(1) UNSIGNED NULL DEFAULT '0',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	UNIQUE INDEX `uk_month_proj` (`yearmonth`, `member_id`, `project_id`, `file_id`),
	INDEX `idx_yearmonth` (`yearmonth`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;

DROP TABLE  IF EXISTS micro_payout_types;
CREATE TABLE `micro_payout_types` (
	`type_id` INT(10) UNSIGNED NOT NULL,
	`name` VARCHAR(50) NULL DEFAULT NULL,
	`description` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`type_id`)
)
ENGINE=InnoDB
;

INSERT INTO micro_payout_types (`type_id`,`name`,`description`) VALUES (0,'downloads','Downlaods');
INSERT INTO micro_payout_types (`type_id`,`name`,`description`) VALUES (1,'mediaviews','Media-Views');








#INSERT

#1. Downloads
DROP VIEW stat_micro_payout_dl_curent_month;

CREATE VIEW stat_micro_payout_dl_curent_month AS 	

SELECT 
	date_format(now(),'%Y%m') AS `yearmonth`
	,0 AS `type` 
	,`p`.`project_id` AS `project_id`
	,`p`.`project_category_id` AS `project_category_id`
	,p.ppload_collection_id AS `collection_id`
	,`d`.`file_id`
	,`d`.`owner_id` AS `member_id`
	,`m`.`mail` AS `mail`
	,`m`.`paypal_mail` AS `paypal_mail`
	,count(`d`.`id`) AS `num_plings`
	,`c`.`dl_pling_factor` AS `category_pling_factor`
	,((count(`d`.`id`) * `c`.`dl_pling_factor`) / 100) AS `amount_plings`
	,(case when isnull(`tag`.`tag_item_id`) then 1 else 0 end) AS `is_license_missing`
	,(case when (((`c`.`source_required` = 1) and (`p`.`source_url` is not null) and (length(`p`.`source_url`) > 0)) or (`c`.`source_required` = 0)) then 0 else 1 end) AS `is_source_missing`
	,`p`.`pling_excluded` AS `is_pling_excluded`
	,`m`.`pling_excluded` AS `is_member_pling_excluded`
	
	,NOW() AS `created_at`
	,NULL AS `updated_at`
from ((((`ppload`.`ppload_files_downloaded_unique` `d` 
join `pling`.`member` `m` on(((`m`.`member_id` = `d`.`owner_id`) and (`m`.`is_active` = 1)))) 
join `pling`.`tmp_project_for_micro_payout` `p` on((`p`.`ppload_collection_id` = `d`.`collection_id`))) 
join `pling`.`project_category` `c` on((`c`.`project_category_id` = `p`.`project_category_id`))) 
left join `pling`.`tag_object` `tag` on(((`tag`.`tag_type_id` = 1) and (`tag`.`tag_group_id` = 7) and (`tag`.`tag_object_id` = `p`.`project_id`)))) 
where (`d`.`downloaded_timestamp` >= concat(left(now(),7),'-01 00:00:00')) 
group by `d`.`owner_id`,`p`.`project_id`,p.ppload_collection_id,`d`.`file_id`
;






	#Generate tmp table for active projects
	DROP TABLE IF EXISTS tmp_project_for_micro_payout;
	CREATE TABLE tmp_project_for_micro_payout AS
	select * from project p where p.ppload_collection_id is not null and p.type_id = 1 and p.`status` = 100;
	
	#ppload_collection_id from char to int
	ALTER TABLE `tmp_project_for_micro_payout`
	CHANGE COLUMN `ppload_collection_id` `ppload_collection_id` INT NULL DEFAULT NULL COLLATE 'utf8_general_ci' AFTER `embed_code`;

	#add index
	ALTER TABLE `tmp_project_for_micro_payout` ADD INDEX `idx_ppload` (`ppload_collection_id`);
	ALTER TABLE `tmp_project_for_micro_payout` ADD INDEX `idx_pk` (`project_id`);

	#fill tmp micro_payout table
	DROP TABLE IF EXISTS tmp_micro_payout;

	CREATE TABLE tmp_micro_payout LIKE micro_payout;
		
	INSERT INTO tmp_micro_payout
	(SELECT * FROM stat_micro_payout_dl_curent_month);
		
	#delete plings from actual month
	DELETE FROM micro_payout
	WHERE yearmonth = (DATE_FORMAT(NOW(),'%Y%m'))
	AND TYPE = 0;
		
	#insert ping for this month from tmp member_dl_plings table
	INSERT INTO micro_payout
	(SELECT * FROM tmp_micro_payout);
	
	#remove tmp micro_payout table
	DROP TABLE tmp_micro_payout;



SELECT * FROM micro_payout p
WHERE p.yearmonth = 201909
AND p.`type` = 0
LIMIT 100;


#2. Mediaviews

#CREATE VIEW stat_micro_payout_curent_month AS 	

DROP VIEW stat_micro_payout_mv_curent_month;
CREATE VIEW stat_micro_payout_mv_curent_month AS 
SELECT 
	DATE_FORMAT(mv.start_timestamp,'%Y%m') AS `yearmonth`
	,1 AS `type` 
	,`p`.`project_id` AS `project_id`
	,`p`.`project_category_id` AS `project_category_id`
	,p.ppload_collection_id AS `collection_id`
	,`mv`.`file_id`
	,`mv`.`member_id`
	,`m`.`mail` AS `mail`
	,`m`.`paypal_mail` AS `paypal_mail`
	,count(`mv`.media_view_id) AS `num_plings`
	,`c`.`dl_pling_factor` AS `category_pling_factor`
	,((count(`mv`.media_view_id) * `c`.`dl_pling_factor`) / 100) AS `amount_plings`
	,(case when isnull(`tag`.`tag_item_id`) then 1 else 0 end) AS `is_license_missing`
	,(case when (((`c`.`source_required` = 1) and (`p`.`source_url` is not null) and (length(`p`.`source_url`) > 0)) or (`c`.`source_required` = 0)) then 0 else 1 end) AS `is_source_missing`
	,`p`.`pling_excluded` AS `is_pling_excluded`
	,`m`.`pling_excluded` AS `is_member_pling_excluded`
	
	,NOW() AS `created_at`
	,NULL AS `updated_at`
FROM media_views mv
join `pling`.`member` `m` ON `m`.`member_id` = `mv`.`member_id` and `m`.`is_active` = 1
join `pling`.`tmp_project_for_micro_payout` `p` ON `p`.`project_id` = `mv`.`project_id`
join `pling`.`project_category` `c` ON `c`.`project_category_id` = `p`.`project_category_id`
left join `pling`.`tag_object` `tag` ON (`tag`.`tag_type_id` = 1) and (`tag`.`tag_group_id` = 7) AND (`tag`.`tag_object_id` = `p`.`project_id`)
WHERE mv.start_timestamp >= concat(left(now(),7),'-01 00:00:00')
GROUP BY mv.member_id, mv.project_id, `p`.project_id
;


#fill tmp micro_payout table
	DROP TABLE IF EXISTS tmp_micro_payout_mv;

	CREATE TABLE tmp_micro_payout_mv LIKE micro_payout;
		
	INSERT INTO tmp_micro_payout_mv
	(SELECT * FROM stat_micro_payout_mv_curent_month);
		
	#delete plings from actual month
	DELETE FROM micro_payout
	WHERE yearmonth = (DATE_FORMAT(NOW(),'%Y%m'))
	AND TYPE = 1;
		
	#insert ping for this month from tmp member_dl_plings table
	INSERT INTO micro_payout
	(SELECT * FROM tmp_micro_payout_mv);
	
	#remove tmp micro_payout table
	DROP TABLE tmp_micro_payout_mv;




SELECT * FROM micro_payout p
WHERE p.yearmonth = 201909
AND p.`type` = 1
;




