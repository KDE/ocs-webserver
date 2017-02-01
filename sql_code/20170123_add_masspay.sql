DROP VIEW stat_dl_payment_last_month;

CREATE VIEW stat_dl_payment_last_month AS	
#Letzter Monat
SELECT (DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m')) as yearmonth, count(d.id) AS num_downloads, d.owner_id as member_id, m.username, count(d.id)/100 as amount,m.mail,m.paypal_mail 
FROM ppload.ppload_files_downloaded d
JOIN member m ON m.member_id = d.owner_id
WHERE 
	(d.downloaded_timestamp BETWEEN CONCAT(LEFT(NOW() - INTERVAL 1 MONTH,7),'-01 00:00:00') AND CONCAT(LEFT(NOW(),7),'-01 00:00:00'))
#	AND count(d.id) > 100
GROUP BY d.owner_id
ORDER BY count(d.id) DESC;


CREATE TABLE `payout` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`yearmonth` INT(11) NOT NULL,
	`member_id` INT(11) NOT NULL,
	`mail` VARCHAR(50) NOT NULL,
	`paypal_mail` VARCHAR(50) NULL DEFAULT NULL,
	`amount` DOUBLE NOT NULL,
	`num_downloads` INT(11) NOT NULL,
	`status` INT(11) NOT NULL DEFAULT '0' COMMENT '0=new,1=start request,2=money recieved,99=error',
	`timestamp_create` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`timestamp_masspay_start` TIMESTAMP NULL DEFAULT NULL,
	`timestamp_masspay_last_ipn` TIMESTAMP NULL DEFAULT NULL,
	`paypal_ipn` TEXT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `UK_PAYOUT` (`yearmonth`, `member_id`)
)
COMMENT='Table for our monthly payouts'
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;

