CREATE TABLE `affiliate_config` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`percent` DOUBLE NOT NULL DEFAULT '0.15',
	`active_from` INT NOT NULL DEFAULT '201701',
	`active_until` INT NOT NULL DEFAULT '209912',
	PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
;
INSERT INTO `pling`.`affiliate_config` (`id`) VALUES ('1');





//new support sum = support - affiliate payout

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
			                , ROUND(SUM(p.credits_plings)/100,2) AS sum_amount
			                , p3.num_downloads AS sum_dls_payout, p3.amount AS sum_amount_payout
								 FROM micro_payout p
			                JOIN section se ON se.section_id = p.section_id  
								 LEFT JOIN (
			                        SELECT yearmonth, section_id, SUM(num_downloads) AS num_downloads, round(SUM(amount),2) AS amount FROM (
			                                SELECT m.yearmonth, `m`.`member_id`,`m`.`paypal_mail`, `m`.section_id, sum(`m`.`num_plings`) AS `num_downloads`,sum(`m`.`credits_plings`)/100 AS `amount` 
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
