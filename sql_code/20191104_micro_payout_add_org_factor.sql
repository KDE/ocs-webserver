
ALTER TABLE `micro_payout`
	ADD COLUMN `org_factor` DECIMAL(3,2) NULL DEFAULT '1.00' AFTER `credits_plings`,
	ADD COLUMN `credits_org` DECIMAL(11,2) NULL DEFAULT NULL AFTER `org_factor`;



DROP VIEW stat_micro_payout_dl_curent_month;

CREATE VIEW stat_micro_payout_dl_curent_month AS 	

SELECT 
	date_format(`d`.`downloaded_timestamp`,'%Y%m') AS `yearmonth`
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
	,(count(`d`.`id`) * `c`.`dl_pling_factor`) AS `credits_plings`
	,case 
     when tag_original.tag_item_id IS NOT NULL 
     then 1.0
     ELSE 
       case 
          when tag_modification.tag_item_id IS NOT NULL 
          then 0.25
       ELSE
			0.1
       END  
   END AS org_factor
	,round(case 
     when tag_original.tag_item_id IS NOT NULL 
     then count(`d`.`id`) * `c`.`dl_pling_factor`
     ELSE 
       case 
          when tag_modification.tag_item_id IS NOT NULL 
          then count(`d`.`id`) * `c`.`dl_pling_factor`*0.25
       ELSE
			count(`d`.`id`) * `c`.`dl_pling_factor`*0.1
       END  
   END,2) AS credits_org
	,0 as section_id
	,1.00 as section_payout_factor #case when sfs.factor IS NULL then 1.00 ELSE sfs.factor end AS section_payout_factor
	,(count(`d`.`id`) * `c`.`dl_pling_factor`) AS credits_section
	,(case when isnull(`tag`.`tag_item_id`) then 1 else 0 end) AS `is_license_missing`
	,(case when (`c`.`source_required` = 1 AND ((`p`.`source_url` is not null and length(`p`.`source_url`) > 0) OR `p`.`gitlab_project_id` is not null) or `c`.`source_required` = 0) then 0 else 1 end) AS `is_source_missing`
	,`p`.`pling_excluded` AS `is_pling_excluded`
	,`m`.`pling_excluded` AS `is_member_pling_excluded`
	#, case when isnull(tag_is_original.tag_item_id) then 0 ELSE 1 END AS is_original
	#,case when pc.project_clone_id IS NOT NULL then 1 ELSE 0 END AS `is_clone`
	,NOW() AS `created_at`
	,NULL AS `updated_at`
from ((((`ppload`.`ppload_files_downloaded_unique` `d` 
join `pling`.`member` `m` on(((`m`.`member_id` = `d`.`owner_id`) and (`m`.`is_active` = 1)))) 
join `pling`.`tmp_project_for_micro_payout` `p` on((`p`.`ppload_collection_id` = `d`.`collection_id`))) 
join `pling`.`project_category` `c` on((`c`.`project_category_id` = `p`.`project_category_id`))) 
#left JOIN `pling`.section_category sc ON sc.project_category_id = p.project_category_id
#left JOIN `pling`.section_funding_stats sfs ON sfs.section_id = sc.section_id AND sfs.yearmonth = DATE_FORMAT(`d`.`downloaded_timestamp`,'%Y%m')
left join `pling`.`tag_object` `tag` on(((`tag`.`tag_type_id` = 1) and (`tag`.`tag_group_id` = 7) and (`tag`.`tag_object_id` = `p`.`project_id`)))
#LEFT JOIN pling.tag_object tag_is_original ON tag_is_original.tag_id = 2451 and tag_is_original.tag_group_id=11 and tag_is_original.tag_type_id = 1 and tag_is_original.is_deleted = 0 AND tag_is_original.tag_object_id = `p`.`project_id`
#LEFT JOIN project_clone pc ON pc.project_id = p.project_id AND pc.is_valid = 1 AND pc.project_id_parent IS NOT NULL
LEFT JOIN tag_object tag_original ON tag_original.tag_id = 2451 and tag_original.tag_object_id=p.project_id and tag_original.tag_group_id=11 and tag_original.tag_type_id = 1 and tag_original.is_deleted = 0
LEFT JOIN tag_object tag_modification ON tag_modification.tag_id = 6600 and tag_modification.tag_object_id=p.project_id and tag_modification.tag_group_id=11 and tag_modification.tag_type_id = 1 and tag_modification.is_deleted = 0
) 
where (`d`.`downloaded_timestamp` >= concat(left(now(),7),'-01 00:00:00')) 
GROUP BY DATE_FORMAT(`d`.`downloaded_timestamp`,'%Y%m'),`d`.`owner_id`,`p`.`project_id`,p.ppload_collection_id,`d`.`file_id`
;




DROP VIEW stat_micro_payout_dl_last_month;
CREATE VIEW stat_micro_payout_dl_last_month AS 	

SELECT 
	date_format(`d`.`downloaded_timestamp`,'%Y%m') AS `yearmonth`
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
	,(count(`d`.`id`) * `c`.`dl_pling_factor`) AS `credits_plings`
	,case 
     when tag_original.tag_item_id IS NOT NULL 
     then 1.0
     ELSE 
       case 
          when tag_modification.tag_item_id IS NOT NULL 
          then 0.25
       ELSE
			0.1
       END  
   END AS org_factor
	,round(case 
     when tag_original.tag_item_id IS NOT NULL 
     then count(`d`.`id`) * `c`.`dl_pling_factor`
     ELSE 
       case 
          when tag_modification.tag_item_id IS NOT NULL 
          then count(`d`.`id`) * `c`.`dl_pling_factor`*0.25
       ELSE
			count(`d`.`id`) * `c`.`dl_pling_factor`*0.1
       END  
   END,2) AS credits_org
	,0 as section_id
	,1.00 as section_payout_factor #case when sfs.factor IS NULL then 1.00 ELSE sfs.factor end AS section_payout_factor
	,(count(`d`.`id`) * `c`.`dl_pling_factor`) AS credits_section# (count(`d`.`id`) * `c`.`dl_pling_factor` * (case when sfs.factor IS NULL then 1.00 ELSE sfs.factor end)) AS credits_section
	,(case when isnull(`tag`.`tag_item_id`) then 1 else 0 end) AS `is_license_missing`
	,(case when (`c`.`source_required` = 1 AND ((`p`.`source_url` is not null and length(`p`.`source_url`) > 0) OR `p`.`gitlab_project_id` is not null) or `c`.`source_required` = 0) then 0 else 1 end) AS `is_source_missing`
	,`p`.`pling_excluded` AS `is_pling_excluded`
	,`m`.`pling_excluded` AS `is_member_pling_excluded`
	#, case when isnull(tag_is_original.tag_item_id) then 0 ELSE 1 END AS is_original
	,NOW() AS `created_at`
	,NULL AS `updated_at`
from ((((`ppload`.`ppload_files_downloaded_unique` `d` 
join `pling`.`member` `m` on(((`m`.`member_id` = `d`.`owner_id`) and (`m`.`is_active` = 1)))) 
join `pling`.`tmp_project_for_micro_payout` `p` on((`p`.`ppload_collection_id` = `d`.`collection_id`))) 
join `pling`.`project_category` `c` on((`c`.`project_category_id` = `p`.`project_category_id`))) 
#left JOIN `pling`.section_category sc ON sc.project_category_id = p.project_category_id
#left JOIN `pling`.section_funding_stats sfs ON sfs.section_id = sc.section_id AND sfs.yearmonth = DATE_FORMAT(`d`.`downloaded_timestamp`,'%Y%m')
left join `pling`.`tag_object` `tag` on(((`tag`.`tag_type_id` = 1) and (`tag`.`tag_group_id` = 7) and (`tag`.`tag_object_id` = `p`.`project_id`)))
#LEFT JOIN pling.tag_object tag_is_original ON tag_is_original.tag_id = 2451 and tag_is_original.tag_group_id=11 and tag_is_original.tag_type_id = 1 and tag_is_original.is_deleted = 0 AND tag_is_original.tag_object_id = `p`.`project_id`
LEFT JOIN tag_object tag_original ON tag_original.tag_id = 2451 and tag_original.tag_object_id=p.project_id and tag_original.tag_group_id=11 and tag_original.tag_type_id = 1 and tag_original.is_deleted = 0
LEFT JOIN tag_object tag_modification ON tag_modification.tag_id = 6600 and tag_modification.tag_object_id=p.project_id and tag_modification.tag_group_id=11 and tag_modification.tag_type_id = 1 and tag_modification.is_deleted = 0

) 
where (`d`.`downloaded_timestamp` >= concat(left((now() - interval 1 month),7),'-01 00:00:00')) AND (`d`.`downloaded_timestamp` <= concat(left(now(),7),'-01 00:00:00'))
GROUP BY DATE_FORMAT(`d`.`downloaded_timestamp`,'%Y%m'),`d`.`owner_id`,`p`.`project_id`,p.ppload_collection_id,`d`.`file_id`
;



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
	,`pr`.`member_id`
	,`m`.`mail` AS `mail`
	,`m`.`paypal_mail` AS `paypal_mail`
	,count(`mv`.media_view_id) AS `num_plings`
	,`c`.`mv_pling_factor` AS `category_pling_factor`
	,(count(`mv`.media_view_id) * `c`.`mv_pling_factor`) AS `credits_plings`
	,case 
     when tag_original.tag_item_id IS NOT NULL 
     then 1.0
     ELSE 
       case 
          when tag_modification.tag_item_id IS NOT NULL 
          then 0.25
       ELSE
			0.1
       END  
   END AS org_factor
	,round(case 
     when tag_original.tag_item_id IS NOT NULL 
     then count(`mv`.media_view_id) * `c`.`mv_pling_factor`
     ELSE 
       case 
          when tag_modification.tag_item_id IS NOT NULL 
          then count(`mv`.media_view_id) * `c`.`mv_pling_factor`*0.25
       ELSE
			count(`mv`.media_view_id) * `c`.`mv_pling_factor`*0.1
       END  
   END,2) AS credits_org
	,0 as section_id
	,1.0 AS section_payout_factor
	,(count(`mv`.media_view_id) * `c`.`mv_pling_factor`) AS credits_section	
	,(case when isnull(`tag`.`tag_item_id`) then 1 else 0 end) AS `is_license_missing`
	,(case when (`c`.`source_required` = 1 AND ((`p`.`source_url` is not null and length(`p`.`source_url`) > 0) OR `p`.`gitlab_project_id` is not null) or `c`.`source_required` = 0) then 0 else 1 end) AS `is_source_missing`
	,`p`.`pling_excluded` AS `is_pling_excluded`
	,`m`.`pling_excluded` AS `is_member_pling_excluded`
	#, case when isnull(tag_is_original.tag_item_id) then 0 ELSE 1 END AS is_original
	,NOW() AS `created_at`
	,NULL AS `updated_at`
FROM media_views mv
JOIN project pr ON pr.project_id = mv.project_id
join `pling`.`member` `m` ON `m`.`member_id` = `pr`.`member_id` and `m`.`is_active` = 1
join `pling`.`tmp_project_for_micro_payout` `p` ON `p`.`project_id` = `mv`.`project_id`
join `pling`.`project_category` `c` ON `c`.`project_category_id` = `p`.`project_category_id`
#left JOIN `pling`.section_category sc ON sc.project_category_id = p.project_category_id
#left JOIN `pling`.section_funding_stats sfs ON sfs.section_id = sc.section_id AND sfs.yearmonth = DATE_FORMAT(mv.start_timestamp,'%Y%m')
left join `pling`.`tag_object` `tag` ON (`tag`.`tag_type_id` = 1) and (`tag`.`tag_group_id` = 7) AND (`tag`.`tag_object_id` = `p`.`project_id`)
#LEFT JOIN pling.tag_object tag_is_original ON tag_is_original.tag_id = 2451 and tag_is_original.tag_group_id=11 and tag_is_original.tag_type_id = 1 and tag_is_original.is_deleted = 0 AND tag_is_original.tag_object_id = `p`.`project_id`
LEFT JOIN tag_object tag_original ON tag_original.tag_id = 2451 and tag_original.tag_object_id=pr.project_id and tag_original.tag_group_id=11 and tag_original.tag_type_id = 1 and tag_original.is_deleted = 0
LEFT JOIN tag_object tag_modification ON tag_modification.tag_id = 6600 and tag_modification.tag_object_id=pr.project_id and tag_modification.tag_group_id=11 and tag_modification.tag_type_id = 1 and tag_modification.is_deleted = 0

WHERE mv.start_timestamp >= concat(left(now(),7),'-01 00:00:00')
GROUP BY DATE_FORMAT(mv.start_timestamp,'%Y%m'),pr.member_id, mv.project_id,`p`.`project_category_id`,p.ppload_collection_id,`mv`.`file_id`
;


DROP VIEW stat_micro_payout_mv_last_month;
CREATE VIEW stat_micro_payout_mv_last_month AS 
SELECT 
	DATE_FORMAT(mv.start_timestamp,'%Y%m') AS `yearmonth`
	,1 AS `type` 
	,`p`.`project_id` AS `project_id`
	,`p`.`project_category_id` AS `project_category_id`
	,p.ppload_collection_id AS `collection_id`
	,`mv`.`file_id`
	,`pr`.`member_id`
	,`m`.`mail` AS `mail`
	,`m`.`paypal_mail` AS `paypal_mail`
	,count(`mv`.media_view_id) AS `num_plings`
	,`c`.`mv_pling_factor` AS `category_pling_factor`
	,(count(`mv`.media_view_id) * `c`.`mv_pling_factor`) AS `credits_plings`
	,case 
     when tag_original.tag_item_id IS NOT NULL 
     then 1.0
     ELSE 
       case 
          when tag_modification.tag_item_id IS NOT NULL 
          then 0.25
       ELSE
			0.1
       END  
   END AS org_factor
	,round(case 
     when tag_original.tag_item_id IS NOT NULL 
     then count(`mv`.media_view_id) * `c`.`mv_pling_factor`
     ELSE 
       case 
          when tag_modification.tag_item_id IS NOT NULL 
          then count(`mv`.media_view_id) * `c`.`mv_pling_factor`*0.25
       ELSE
			count(`mv`.media_view_id) * `c`.`mv_pling_factor`*0.1
       END  
   END,2) AS credits_org
	,0 as section_id
	,1.0 AS section_payout_factor
	,(count(`mv`.media_view_id) * `c`.`mv_pling_factor`) AS credits_section	
	,(case when isnull(`tag`.`tag_item_id`) then 1 else 0 end) AS `is_license_missing`
	,(case when (`c`.`source_required` = 1 AND ((`p`.`source_url` is not null and length(`p`.`source_url`) > 0) OR `p`.`gitlab_project_id` is not null) or `c`.`source_required` = 0) then 0 else 1 end) AS `is_source_missing`
	,`p`.`pling_excluded` AS `is_pling_excluded`
	,`m`.`pling_excluded` AS `is_member_pling_excluded`
	#, case when isnull(tag_is_original.tag_item_id) then 0 ELSE 1 END AS is_original
	,NOW() AS `created_at`
	,NULL AS `updated_at`
FROM media_views mv
JOIN project pr ON pr.project_id = mv.project_id
join `pling`.`member` `m` ON `m`.`member_id` = `pr`.`member_id` and `m`.`is_active` = 1
join `pling`.`tmp_project_for_micro_payout` `p` ON `p`.`project_id` = `mv`.`project_id`
join `pling`.`project_category` `c` ON `c`.`project_category_id` = `p`.`project_category_id`
#left JOIN `pling`.section_category sc ON sc.project_category_id = p.project_category_id
#left JOIN `pling`.section_funding_stats sfs ON sfs.section_id = sc.section_id AND sfs.yearmonth = DATE_FORMAT(mv.start_timestamp,'%Y%m')
left join `pling`.`tag_object` `tag` ON (`tag`.`tag_type_id` = 1) and (`tag`.`tag_group_id` = 7) AND (`tag`.`tag_object_id` = `p`.`project_id`)
#LEFT JOIN pling.tag_object tag_is_original ON tag_is_original.tag_id = 2451 and tag_is_original.tag_group_id=11 and tag_is_original.tag_type_id = 1 and tag_is_original.is_deleted = 0 AND tag_is_original.tag_object_id = `p`.`project_id`
LEFT JOIN tag_object tag_original ON tag_original.tag_id = 2451 and tag_original.tag_object_id=pr.project_id and tag_original.tag_group_id=11 and tag_original.tag_type_id = 1 and tag_original.is_deleted = 0
LEFT JOIN tag_object tag_modification ON tag_modification.tag_id = 6600 and tag_modification.tag_object_id=pr.project_id and tag_modification.tag_group_id=11 and tag_modification.tag_type_id = 1 and tag_modification.is_deleted = 0

WHERE (`mv`.start_timestamp >= concat(left((now() - interval 1 month),7),'-01 00:00:00')) and (`mv`.start_timestamp <= concat(left(now(),7),'-01 00:00:00'))
GROUP BY DATE_FORMAT(mv.start_timestamp,'%Y%m'),pr.member_id, mv.project_id,`p`.`project_category_id`,p.ppload_collection_id,`mv`.`file_id`
;



UPDATE micro_payout m
SET m.credits_org = m.credits_plings
WHERE m.credits_org IS NULL;




DROP PROCEDURE `generate_section_funding_stats_micro_payout`;

DELIMITER $$
CREATE PROCEDURE `generate_section_funding_stats_micro_payout`(
	IN `p_yearmonth` INT
)
BEGIN

    delete from section_funding_stats where yearmonth = p_yearmonth;

    INSERT INTO section_funding_stats 
    SELECT  yearmonth,section_id,section_name
	 	,case when sum_affiliate_payout IS NOT NULL then (sum_support-sum_affiliate_payout) ELSE sum_support END AS sum_support
	 	,sum_sponsor,sum_dls,sum_amount,sum_dls_payout, sum_amount_payout
	 	,case when sum_affiliate_payout IS NOT NULL then ROUND((sum_support-sum_affiliate_payout)/sum_amount_payout,2) ELSE ROUND(sum_support/sum_amount_payout,2) END AS factor 
    FROM (        
			SELECT p.yearmonth, p.section_id, se.name AS section_name   
								 ,(SELECT ROUND(SUM(ss.tier),2) AS sum_support FROM section_support_paypements ss
			                    JOIN support su2 ON su2.id = ss.support_id
			                    WHERE p.section_id = ss.section_id
			                    AND ss.yearmonth = p_yearmonth 
			                    GROUP BY p.section_id
			                ) AS sum_support
			                ,affiliate_payout.sum_affiliate_payout
			                ,(SELECT SUM(sp.amount * (ssp.percent_of_sponsoring/100)) AS sum_sponsor FROM sponsor sp
			                LEFT JOIN section_sponsor ssp ON ssp.sponsor_id = sp.sponsor_id
			                WHERE sp.is_active = 1
			                AND ssp.section_id = p.section_id) AS sum_sponsor
			                , SUM(p.num_plings) AS sum_dls
			                , ROUND(SUM(p.credits_org)/100,2) AS sum_amount
			                , p3.num_downloads AS sum_dls_payout, p3.amount AS sum_amount_payout
								 FROM micro_payout p
			                JOIN section se ON se.section_id = p.section_id  
								 LEFT JOIN (
			                        SELECT yearmonth, section_id, SUM(num_downloads) AS num_downloads, round(SUM(amount),2) AS amount FROM (
			                                SELECT m.yearmonth, `m`.`member_id`,`m`.`paypal_mail`, `m`.section_id, sum(`m`.`num_plings`) AS `num_downloads`,sum(`m`.`credits_org`)/100 AS `amount` 
			                                from `micro_payout` `m` 
			                                where ((`m`.`yearmonth` = p_yearmonth) 
			                                and (length(`m`.`paypal_mail`) > 0) and (`m`.`paypal_mail` regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') and (`m`.`is_license_missing` = 0) and (`m`.`is_source_missing` = 0) and (`m`.`is_pling_excluded` = 0) and (`m`.`is_member_pling_excluded` = 0)) 
			                                group by m.yearmonth, `m`.`member_id`,`m`.`paypal_mail`, `m`.section_id
			                                #HAVING sum(`m`.`probably_payout_amount`) >= 1
			                        ) A GROUP BY yearmonth, section_id
			                ) p3 ON p3.yearmonth = p.yearmonth AND p3.section_id = p.section_id					   
			                LEFT JOIN (SELECT yearmonth, section_id, round(sum_donations * affiliate_percent,2) AS sum_affiliate_payout FROM (
										   SELECT yearmonth, section_id, COUNT(supporter_member_id) AS count_supporters, SUM(sum_donations) AS sum_donations, 
									                    (SELECT percent FROM affiliate_config WHERE A2.yearmonth >= active_from  AND A2.yearmonth <= active_until) AS affiliate_percent
											FROM (
									                    SELECT 
									                    		ssp2.`yearmonth`, ssp2.section_id, su3.member_id AS supporter_member_id
									                    		,SUM(ssp2.tier) AS sum_donations
									                    from section_support_paypements ssp2
															  JOIN section_support ss2 ON ss2.section_support_id = ssp2.section_support_id
															  JOIN support su3 ON su3.id = ss2.support_id
															  JOIN project pr3 ON pr3.project_id = ss2.project_id
									                    WHERE
									                        ssp2.`yearmonth` = p_yearmonth
									                    GROUP BY ssp2.`yearmonth`, ssp2.section_id, su3.member_id
									                        
									                ) A2
									                GROUP BY A2.yearmonth, A2.section_id
									) A3) affiliate_payout ON affiliate_payout.yearmonth = p.yearmonth AND affiliate_payout.section_id = p.section_id
			  WHERE p.yearmonth = p_yearmonth
	        AND p.section_id IS NOT null
	        GROUP BY p.section_id
	) AA
	;
END$$
DELIMITER ;


