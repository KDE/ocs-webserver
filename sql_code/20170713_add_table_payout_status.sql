CREATE TABLE `payout_status` (
	`id` INT(11) NOT NULL,
	`type` VARCHAR(50) NOT NULL DEFAULT 'info',
	`title` VARCHAR(50) NULL DEFAULT NULL,
	`description` TEXT NULL,
	`color` VARCHAR(50) NULL DEFAULT NULL,
	`icon` VARCHAR(50) NULL DEFAULT 'glyphicon-info-sign',
	`is_active` INT(1) NULL DEFAULT '1',
	PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;

/**
public static $PAYOUT_STATUS_NEW = 0;
    public static $PAYOUT_STATUS_REQUESTED = 1;
    public static $PAYOUT_STATUS_PROCESSED = 10;
    public static $PAYOUT_STATUS_PENDING = 50;
    public static $PAYOUT_STATUS_COMPLETED = 100;
    public static $PAYOUT_STATUS_DENIED = 999;
    public static $PAYOUT_STATUS_REFUND = 900;
    public static $PAYOUT_STATUS_ERROR = 99;
**/
insert into payout_status (id,title,description,color,icon) values (0,'New','New - valid status unknown','#31708f;','glyphicon-info-sign');
insert into payout_status (id,title,description,color) values (1,'Status: Requested','We send your payout. The actual status is: Requested.','#31708f;','glyphicon-info-sign');
insert into payout_status (id,title,description,color) values (10,'Status: Processed','We send your payout. The actual status is: Processed.','#31708f;','glyphicon-info-sign');
insert into payout_status (id,title,description,color) values (50,'Status: Pending','We send your payout. The actual status is: Pending.','#31708f;','glyphicon-info-sign');
insert into payout_status (id,title,description,color) values (100,'Status: Completed','For this month we has successfully paid you.','#3c763d;','glyphicon-ok-sign');
insert into payout_status (id,title,description,color) values (999,'Status: Denied','We tried to payout your plings, but your payment was denied.','#a94442;','glyphicon-exclamation-sign');
insert into payout_status (id,title,description,color) values (900,'Status: Refund','We tried to payout your plings, but your payment was refund.','#a94442;','glyphicon-exclamation-sign');
insert into payout_status (id,title,description,color) values (99,'Status: Error','We tried to payout your plings, but there was an error.','#a94442;','glyphicon-exclamation-sign');

