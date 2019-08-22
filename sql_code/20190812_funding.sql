
#Table with monthly support payments
CREATE TABLE `section_support_paypements` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`yearmonth` INT UNSIGNED NOT NULL,
	`section_support_id` INT UNSIGNED NOT NULL,
	`support_id` INT UNSIGNED NOT NULL,
	`section_id` INT UNSIGNED NOT NULL,
	`amount` FLOAT(10,2) UNSIGNED NOT NULL,
	`tier` FLOAT(10,2) UNSIGNED NOT NULL,
	`period` VARCHAR(50) NOT NULL,
	`period_frequenzy` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
;

TRUNCATE TABLE section_support_paypements;


#Subscriptions

#201901
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201901 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 1
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201901'
;

#201902
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201902 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 1
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201902'
;

#201903
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201903 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 1
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201903'
;

#201904
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201904 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 1
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201904'
;

#201905
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201905 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 1
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201905'
;

#201906
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201906 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 1
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201906'
;

#201907
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201907 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 1
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201907'
;

#201908
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201908 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 1
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201908'
#AND su.id = 378
;

#SELECT * FROM section_support_paypements p
#WHERE p.support_id = 378
#;















#One-Time Payments

#201801
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201801 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201801'
;

#201802
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201802 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201802'
;

#201803
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201803 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201803'
;

#201804
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201804 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201804'
;

#201805
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201805 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201805'
;

#201806
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201806 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201806'
;

#201807
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201807 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201807'
;

#201808
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201808 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201808'
;

#201809
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201809 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201809'
;

#201810
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201810 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201810'
;

#201811
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201811 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201811'
;

#201812
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201812 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201812'
;

#201901
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201901 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201901'
AND DATE_FORMAT(su.active_time,'%Y%m')  > '201801'
;

#201902
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201902 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201902'
AND DATE_FORMAT(su.active_time,'%Y%m')  > '201802'
;

#201903
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201903 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201903'
AND DATE_FORMAT(su.active_time,'%Y%m')  >= '201803'
;

#201904
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201904 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201904'
AND DATE_FORMAT(su.active_time,'%Y%m')  > '201804'
;

#201905
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201905 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201905'
AND DATE_FORMAT(su.active_time,'%Y%m')  > '201805'
;

#201906
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201906 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201906'
AND DATE_FORMAT(su.active_time,'%Y%m')  > '201806'
;

#201907
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201907 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201907'
AND DATE_FORMAT(su.active_time,'%Y%m')  > '201807'
;

#201908
INSERT INTO section_support_paypements (yearmonth, section_support_id, support_id, section_id, amount, tier, period, period_frequenzy)

SELECT 201908 AS yearmonth, s.section_support_id, s.support_id, s.section_id, s.amount, s.tier, s.period, s.period_frequency FROM section_support s
JOIN support su ON su.id = s.support_id
WHERE s.is_active = 1
AND su.status_id = 2
AND su.type_id = 0
AND DATE_FORMAT(su.active_time,'%Y%m')  <= '201908'
AND DATE_FORMAT(su.active_time,'%Y%m')  > '201808'
;







#table with all income and payouts per month and section factor
CREATE TABLE `section_funding_stats` (
	`yearmonth` INT(6) NULL DEFAULT NULL,
	`section_id` INT(11) NULL DEFAULT '0',
	`section_name` VARCHAR(50) NULL,
	`sum_support` DOUBLE(19,2) NULL DEFAULT NULL,
	`sum_sponsor` DOUBLE NULL DEFAULT NULL,
	`sum_dls` DECIMAL(42,0) NULL DEFAULT NULL,
	`sum_amount` DECIMAL(46,2) NULL DEFAULT NULL,
	`sum_dls_payout` DECIMAL(64,0) NULL DEFAULT NULL,
	`sum_amount_payout` DECIMAL(65,2) NULL DEFAULT NULL,
	`factor` DOUBLE(19,2) NULL DEFAULT NULL
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;


ALTER TABLE `member_dl_plings`
	ADD COLUMN `section_id` INT NULL DEFAULT NULL AFTER `updated_at`,
	ADD COLUMN `section_payout_factor` DECIMAL(3,2) NULL DEFAULT 1.00 AFTER `section_id`;



DROP PROCEDURE `generate_section_funding_stats`;

DELIMITER $$
CREATE PROCEDURE `generate_section_funding_stats`(
	IN `p_yearmonth` INT
)
BEGIN

    delete from section_funding_stats where yearmonth = p_yearmonth;

    INSERT INTO section_funding_stats 

    SELECT *, case when sum_support < sum_amount then ROUND(sum_support/sum_amount,2) ELSE 1 END AS factor 
    FROM (

        SELECT p.yearmonth, s.section_id, s.name AS section_name
                ,(SELECT ROUND(SUM(ss.tier),2) AS sum_support FROM section_support_paypements ss
                    JOIN support su2 ON su2.id = ss.support_id
                    WHERE s.section_id = ss.section_id
                    AND ss.yearmonth = p_yearmonth 
                    GROUP BY ss.section_id
                ) AS sum_support
                ,(SELECT SUM(sp.amount * (ssp.percent_of_sponsoring/100)) AS sum_sponsor FROM sponsor sp
                LEFT JOIN section_sponsor ssp ON ssp.sponsor_id = sp.sponsor_id
                WHERE sp.is_active = 1
                AND ssp.section_id = s.section_id) AS sum_sponsor
                , SUM(p.num_downloads) AS sum_dls
                , ROUND(SUM(p.probably_payout_amount),2) AS sum_amount
                , p3.num_downloads AS sum_dls_payout, p3.amount AS sum_amount_payout
                FROM member_dl_plings p
                LEFT JOIN section_category sc ON sc.project_category_id = p.project_category_id
                LEFT JOIN section s ON s.section_id = sc.section_id
                LEFT JOIN (
                        SELECT yearmonth, section_id, SUM(num_downloads) AS num_downloads, SUM(amount) AS amount FROM (
                                SELECT m.yearmonth, `m`.`member_id`,`m`.`paypal_mail`, s.section_id, sum(`m`.`num_downloads`) AS `num_downloads`,round(sum(`m`.`probably_payout_amount`),2) AS `amount` 
                                from `member_dl_plings` `m` 
                                LEFT JOIN section_category sc ON sc.project_category_id = m.project_category_id
                                LEFT JOIN section s ON s.section_id = sc.section_id
                                where ((`m`.`yearmonth` = p_yearmonth) 
                                and (length(`m`.`paypal_mail`) > 0) and (`m`.`paypal_mail` regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') and (`m`.`is_license_missing` = 0) and (`m`.`is_source_missing` = 0) and (`m`.`is_pling_excluded` = 0) and (`m`.`is_member_pling_excluded` = 0)) 
                                group by m.yearmonth, `m`.`member_id`,`m`.`paypal_mail`, s.section_id
                                HAVING sum(`m`.`probably_payout_amount`) >= 1
                        ) A GROUP BY yearmonth, section_id
                ) p3 ON p3.yearmonth = p.yearmonth AND p3.section_id = s.section_id
        WHERE p.yearmonth = p_yearmonth
        AND sc.section_id IS NOT null
        GROUP BY s.section_id
    ) AA
    ;

END$$
DELIMITER ;



CALL `generate_section_funding_stats`('201704');
CALL `generate_section_funding_stats`('201705');
CALL `generate_section_funding_stats`('201706');
CALL `generate_section_funding_stats`('201707');
CALL `generate_section_funding_stats`('201708');
CALL `generate_section_funding_stats`('201709');
CALL `generate_section_funding_stats`('201710');
CALL `generate_section_funding_stats`('201711');
CALL `generate_section_funding_stats`('201712');

CALL `generate_section_funding_stats`('201801');
CALL `generate_section_funding_stats`('201802');
CALL `generate_section_funding_stats`('201803');
CALL `generate_section_funding_stats`('201804');
CALL `generate_section_funding_stats`('201805');
CALL `generate_section_funding_stats`('201806');
CALL `generate_section_funding_stats`('201807');
CALL `generate_section_funding_stats`('201808');
CALL `generate_section_funding_stats`('201809');
CALL `generate_section_funding_stats`('201810');
CALL `generate_section_funding_stats`('201811');
CALL `generate_section_funding_stats`('201812');

CALL `generate_section_funding_stats`('201901');
CALL `generate_section_funding_stats`('201902');
CALL `generate_section_funding_stats`('201903');
CALL `generate_section_funding_stats`('201904');
CALL `generate_section_funding_stats`('201905');
CALL `generate_section_funding_stats`('201906');
CALL `generate_section_funding_stats`('201907');
CALL `generate_section_funding_stats`('201908');
CALL `generate_section_funding_stats`('201909');
CALL `generate_section_funding_stats`('201910');
CALL `generate_section_funding_stats`('201911');
CALL `generate_section_funding_stats`('201912');

#SELECT * FROM section_funding_stats p
#WHERE p.yearmonth = 201901
#;

#Update section factor for current month
UPDATE member_dl_plings p
JOIN section_category sc ON sc.project_category_id = p.project_category_id
SET p.section_id = sc.section_id
WHERE p.yearmonth = DATE_FORMAT(NOW(),'%Y%m');

CALL `generate_section_funding_stats`(DATE_FORMAT(NOW(),'%Y%m'));

UPDATE member_dl_plings p
JOIN section_funding_stats sfs ON sfs.yearmonth = p.yearmonth AND sfs.section_id = p.section_id
SET p.section_payout_factor = sfs.factor
WHERE p.yearmonth = DATE_FORMAT(NOW(),'%Y%m');



#Update section factor for last month
UPDATE member_dl_plings p
JOIN section_category sc ON sc.project_category_id = p.project_category_id
SET p.section_id = sc.section_id
WHERE p.yearmonth = DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m');

CALL `generate_section_funding_stats`(DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m'));

UPDATE member_dl_plings p
JOIN section_funding_stats sfs ON sfs.yearmonth = p.yearmonth AND sfs.section_id = p.section_id
SET p.section_payout_factor = sfs.factor
WHERE p.yearmonth = DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m');



drop event e_update_member_dl_plings_current_month;

DELIMITER $$
CREATE DEVENT `e_update_member_dl_plings_current_month`
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
	
	#Update section factor for current month
	UPDATE member_dl_plings p
	JOIN section_category sc ON sc.project_category_id = p.project_category_id
	SET p.section_id = sc.section_id
	WHERE p.yearmonth = DATE_FORMAT(NOW(),'%Y%m');
	
	CALL `generate_section_funding_stats`(DATE_FORMAT(NOW(),'%Y%m'));
	
	UPDATE member_dl_plings p
	JOIN section_funding_stats sfs ON sfs.yearmonth = p.yearmonth AND sfs.section_id = p.section_id
	SET p.section_payout_factor = sfs.factor
	WHERE p.yearmonth = DATE_FORMAT(NOW(),'%Y%m');

	
	
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

END$$
DELIMITER ;


drop event e_update_member_dl_plings_last_month;

DELIMITER $$
CREATE EVENT `e_update_member_dl_plings_last_month`
	ON SCHEDULE
		EVERY 1 MONTH STARTS '2017-12-01 01:00:00'
	ON COMPLETION NOT PRESERVE
	ENABLE
	COMMENT ''
	DO BEGIN

	

	DELETE FROM member_dl_plings

	WHERE yearmonth = (DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m'));



	INSERT INTO member_dl_plings

	(SELECT * FROM stat_member_dl_last_month);
	
	
	#Update section factor for last month
	UPDATE member_dl_plings p
	JOIN section_category sc ON sc.project_category_id = p.project_category_id
	SET p.section_id = sc.section_id
	WHERE p.yearmonth = DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m');
	
	CALL `generate_section_funding_stats`(DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m'));
	
	UPDATE member_dl_plings p
	JOIN section_funding_stats sfs ON sfs.yearmonth = p.yearmonth AND sfs.section_id = p.section_id
	SET p.section_payout_factor = sfs.factor
	WHERE p.yearmonth = DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m');



	#fill tmp member_dl_plings_nouk table
	DROP TABLE IF EXISTS tmp_member_dl_plings_nouk;

	CREATE TABLE tmp_member_dl_plings_nouk LIKE member_dl_plings_nouk;
		
	INSERT INTO tmp_member_dl_plings_nouk
	(SELECT * FROM stat_member_dl_last_month_nouk);
		
	#delete plings from actual month
	DELETE FROM member_dl_plings_nouk
	WHERE yearmonth = (DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m'));
		
	#insert ping for this month from tmp member_dl_plings table
	INSERT INTO member_dl_plings_nouk
	(SELECT * FROM tmp_member_dl_plings_nouk);
	
	#remove tmp member_dl_plings table
	DROP TABLE tmp_member_dl_plings_nouk;


END$$
DELIMITER ;





