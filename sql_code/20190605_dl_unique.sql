DROP TABLE IF EXISTS ppload.tmp_stat_ppload_files_downloaded_nounique;
	
CREATE TABLE ppload.tmp_stat_ppload_files_downloaded_nounique
(INDEX `idx_coll` (`collection_id`),INDEX `idx_file` (`file_id`))
   ENGINE MyISAM
   AS
        SELECT f.owner_id, f.collection_id, f.file_id, COUNT(1) AS count_dl FROM ppload.ppload_files_downloaded f
        WHERE f.downloaded_timestamp < '2019-06-01 00:00:00'
        GROUP BY f.collection_id, f.file_id
;
RENAME TABLE ppload.stat_ppload_files_downloaded_nounique TO ppload.old_stat_ppload_files_downloaded_nounique;
RENAME TABLE ppload.tmp_stat_ppload_files_downloaded_nounique TO ppload.stat_ppload_files_downloaded_nounique;
DROP TABLE IF EXISTS ppload.old_stat_ppload_files_downloaded_nounique;





CREATE TABLE ppload_files_downloaded_unique LIKE ppload_files_downloaded;

ALTER TABLE `ppload_files_downloaded_unique`
	ADD UNIQUE INDEX `uk` (`collection_id`, `file_id`, `downloaded_ip`);

INSERT IGNORE	INTO ppload.ppload_files_downloaded_unique	
SELECT MAX(f.id) as id, MAX(f.client_id) AS client_id, f.owner_id, f.collection_id, f.file_id,MAX(f.user_id) AS user_id, MIN(f.referer) AS referer, MIN(f.downloaded_timestamp) AS downloaded_timestamp, f.downloaded_ip  FROM ppload.ppload_files_downloaded f
		WHERE f.downloaded_timestamp >= '2019-05-01 00:00:00'
		GROUP BY f.collection_id, f.file_id, f.downloaded_ip
;

DROP VIEW stat_member_dl_curent_month;
CREATE VIEW stat_member_dl_curent_month AS
select date_format(now(),'%Y%m') AS `yearmonth`,`p`.`project_id` AS `project_id`,`p`.`project_category_id` AS `project_category_id`,`d`.`owner_id` AS `member_id`,`m`.`mail` AS `mail`,`m`.`paypal_mail` AS `paypal_mail`,count(`d`.`id`) AS `num_downloads`,`c`.`dl_pling_factor` AS `dl_pling_factor`,((count(`d`.`id`) * `c`.`dl_pling_factor`) / 100) AS `amount`,(case when ((select count(1) AS `sum_plings` from `project_plings` `pp` where ((`pp`.`project_id` = `p`.`project_id`) and (`pp`.`is_deleted` = 0) and (`pp`.`is_active` = 1)) group by `pp`.`project_id`) > 0) then ((select count(1) AS `sum_plings` from `project_plings` `pp` where ((`pp`.`project_id` = `p`.`project_id`) and (`pp`.`is_deleted` = 0) and (`pp`.`is_active` = 1)) group by `pp`.`project_id`) + 1) else 1 end) AS `num_plings`,(case when isnull(`tag`.`tag_item_id`) then 1 else 0 end) AS `is_license_missing`,(case when (((`c`.`source_required` = 1) and (`p`.`source_url` is not null) and (length(`p`.`source_url`) > 0)) or (`c`.`source_required` = 0)) then 0 else 1 end) AS `is_source_missing`,`p`.`pling_excluded` AS `is_pling_excluded`,`m`.`pling_excluded` AS `is_member_pling_excluded`,NULL AS `created_at`,NULL AS `updated_at` from ((((`ppload`.`ppload_files_downloaded_unique` `d` join `member` `m` on(((`m`.`member_id` = `d`.`owner_id`) and (`m`.`is_active` = 1)))) join `tmp_project_for_member_dl_plings` `p` on((`p`.`ppload_collection_id` = `d`.`collection_id`))) join `project_category` `c` on((`c`.`project_category_id` = `p`.`project_category_id`))) left join `tag_object` `tag` on(((`tag`.`tag_type_id` = 1) and (`tag`.`tag_group_id` = 7) and (`tag`.`tag_object_id` = `p`.`project_id`)))) where (`d`.`downloaded_timestamp` >= concat(left(now(),7),'-01 00:00:00')) group by `d`.`owner_id`,`p`.`project_id`
;
DROP VIEW stat_member_dl_last_month;
CREATe VIEW stat_member_dl_last_month AS
select date_format((now() - interval 1 month),'%Y%m') AS `yearmonth`,`p`.`project_id` AS `project_id`,`p`.`project_category_id` AS `project_category_id`,`d`.`owner_id` AS `member_id`,`m`.`mail` AS `mail`,`m`.`paypal_mail` AS `paypal_mail`,count(`d`.`id`) AS `num_downloads`,`c`.`dl_pling_factor` AS `dl_pling_factor`,((count(`d`.`id`) * `c`.`dl_pling_factor`) / 100) AS `amount`,(case when ((select count(1) AS `sum_plings` from `pling`.`project_plings` `pp` where ((`pp`.`project_id` = `p`.`project_id`) and (`pp`.`is_deleted` = 0) and (`pp`.`is_active` = 1)) group by `pp`.`project_id`) > 0) then ((select count(1) AS `sum_plings` from `pling`.`project_plings` `pp` where ((`pp`.`project_id` = `p`.`project_id`) and (`pp`.`is_deleted` = 0) and (`pp`.`is_active` = 1)) group by `pp`.`project_id`) + 1) else 1 end) AS `num_plings`,(case when isnull(`tag`.`tag_item_id`) then 1 else 0 end) AS `is_license_missing`,(case when (((`c`.`source_required` = 1) and (`p`.`source_url` is not null) and (length(`p`.`source_url`) > 0)) or (`c`.`source_required` = 0)) then 0 else 1 end) AS `is_source_missing`,`p`.`pling_excluded` AS `is_pling_excluded`,`m`.`pling_excluded` AS `is_member_pling_excluded`,NULL AS `created_at`,NULL AS `updated_at` from ((((`ppload`.`ppload_files_downloaded_unique` `d` join `pling`.`member` `m` on(((`m`.`member_id` = `d`.`owner_id`) and (`m`.`is_active` = 1)))) join `pling`.`project` `p` on(((cast(`p`.`ppload_collection_id` as unsigned) = `d`.`collection_id`) and (`p`.`status` = 100)))) join `pling`.`project_category` `c` on((`c`.`project_category_id` = `p`.`project_category_id`))) left join `pling`.`tag_object` `tag` on(((`tag`.`tag_type_id` = 1) and (`tag`.`tag_group_id` = 7) and (`tag`.`tag_object_id` = `p`.`project_id`)))) where ((`d`.`downloaded_timestamp` >= concat(left((now() - interval 1 month),7),'-01 00:00:00')) and (`d`.`downloaded_timestamp` <= concat(left(now(),7),'-01 00:00:00')) and (`p`.`ppload_collection_id` is not null) and (length(`p`.`ppload_collection_id`) > 0) and (not((`p`.`ppload_collection_id` like '!%')))) group by `d`.`owner_id`,`p`.`project_id`
;



DROP VIEW stat_member_dl_curent_month_nouk;
CREATE VIEW stat_member_dl_curent_month_nouk AS
select date_format(now(),'%Y%m') AS `yearmonth`,`p`.`project_id` AS `project_id`,`p`.`project_category_id` AS `project_category_id`,`d`.`owner_id` AS `member_id`,`m`.`mail` AS `mail`,`m`.`paypal_mail` AS `paypal_mail`,count(`d`.`id`) AS `num_downloads`,`c`.`dl_pling_factor` AS `dl_pling_factor`,((count(`d`.`id`) * `c`.`dl_pling_factor`) / 100) AS `amount`,(case when ((select count(1) AS `sum_plings` from `project_plings` `pp` where ((`pp`.`project_id` = `p`.`project_id`) and (`pp`.`is_deleted` = 0) and (`pp`.`is_active` = 1)) group by `pp`.`project_id`) > 0) then ((select count(1) AS `sum_plings` from `project_plings` `pp` where ((`pp`.`project_id` = `p`.`project_id`) and (`pp`.`is_deleted` = 0) and (`pp`.`is_active` = 1)) group by `pp`.`project_id`) + 1) else 1 end) AS `num_plings`,(case when isnull(`tag`.`tag_item_id`) then 1 else 0 end) AS `is_license_missing`,(case when (((`c`.`source_required` = 1) and (`p`.`source_url` is not null) and (length(`p`.`source_url`) > 0)) or (`c`.`source_required` = 0)) then 0 else 1 end) AS `is_source_missing`,`p`.`pling_excluded` AS `is_pling_excluded`,`m`.`pling_excluded` AS `is_member_pling_excluded`,NULL AS `created_at`,NULL AS `updated_at` from ((((`ppload`.`ppload_files_downloaded` `d` join `member` `m` on(((`m`.`member_id` = `d`.`owner_id`) and (`m`.`is_active` = 1)))) join `tmp_project_for_member_dl_plings` `p` on((`p`.`ppload_collection_id` = `d`.`collection_id`))) join `project_category` `c` on((`c`.`project_category_id` = `p`.`project_category_id`))) left join `tag_object` `tag` on(((`tag`.`tag_type_id` = 1) and (`tag`.`tag_group_id` = 7) and (`tag`.`tag_object_id` = `p`.`project_id`)))) 
WHERE (
	`d`.`downloaded_timestamp` >= concat(left(now(),7),'-01 00:00:00')
) 
group by `d`.`owner_id`,`p`.`project_id`
;

CREATE TABLE member_dl_plings_nouk LIKE member_dl_plings;

INSERT LOW_PRIORITY INTO member_dl_plings_nouk
(SELECT * FROM stat_member_dl_curent_month_nouk);



CREATE EVENT `e_update_member_dl_plings_current_month`
	ON SCHEDULE
		EVERY 1 DAY STARTS '2018-06-07 01:00:00'
	ON COMPLETION NOT PRESERVE
	ENABLE
	COMMENT ''
	DO BEGIN

	#Generate tmp table for active projects
	DROP TABLE IF EXISTS tmp_project_for_member_dl_plings;
	CREATE TABLE tmp_project_for_member_dl_plings AS
	select * from project p where p.ppload_collection_id is not null and p.type_id = 1 and p.`status` = 100;
	
	#ppload_collection_id from char to int
	ALTER TABLE `tmp_project_for_member_dl_plings`
	CHANGE COLUMN `ppload_collection_id` `ppload_collection_id` INT NULL DEFAULT NULL COLLATE 'utf8_general_ci' AFTER `embed_code`;

	#add index
	ALTER TABLE `tmp_project_for_member_dl_plings` ADD INDEX `idx_ppload` (`ppload_collection_id`);
	ALTER TABLE `tmp_project_for_member_dl_plings` ADD INDEX `idx_pk` (`project_id`);

	#fill tmp member_dl_plings table
	DROP TABLE IF EXISTS tmp_member_dl_plings;

	CREATE TABLE tmp_member_dl_plings LIKE member_dl_plings;
		
	INSERT INTO tmp_member_dl_plings
	(SELECT * FROM stat_member_dl_curent_month);
		
	#delete plings from actual month
	DELETE FROM member_dl_plings
	WHERE yearmonth = (DATE_FORMAT(NOW(),'%Y%m'));
		
	#insert ping for this month from tmp member_dl_plings table
	INSERT INTO member_dl_plings
	(SELECT * FROM tmp_member_dl_plings);
	
	#remove tmp member_dl_plings table
	DROP TABLE tmp_member_dl_plings;
	
	
	#fill tmp member_dl_plings_nouk table
	DROP TABLE IF EXISTS tmp_member_dl_plings_nouk;

	CREATE TABLE tmp_member_dl_plings_nouk LIKE member_dl_plings_nouk;
		
	INSERT INTO tmp_member_dl_plings_nouk
	(SELECT * FROM stat_member_dl_curent_month_nouk);
		
	#delete plings from actual month
	DELETE FROM member_dl_plings_nouk
	WHERE yearmonth = (DATE_FORMAT(NOW(),'%Y%m'));
		
	#insert ping for this month from tmp member_dl_plings table
	INSERT INTO member_dl_plings_nouk
	(SELECT * FROM tmp_member_dl_plings_nouk);
	
	#remove tmp member_dl_plings table
	DROP TABLE tmp_member_dl_plings_nouk;

END	
