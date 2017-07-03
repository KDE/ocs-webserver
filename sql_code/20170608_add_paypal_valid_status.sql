ALTER TABLE `member`
	ADD COLUMN `paypal_valid_status` MEDIUMINT NULL DEFAULT NULL AFTER `paypal_mail`;

CREATE TABLE `paypal_valid_status` (
	`id` INT(11) NOT NULL,
	`title` VARCHAR(50) NULL DEFAULT NULL,
	`description` TEXT NULL,
	`color` VARCHAR(50) NULL DEFAULT NULL,
	`is_active` INT(1) NULL DEFAULT '1',
	PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;


insert into paypal_valid_status (id,title,desciption,color) values (0,'New','New - valid status unknown','yellow');
insert into paypal_valid_status (id,title,desciption,color) values (100,'Valid','Valid - we can send you money per PayPal','green');
insert into paypal_valid_status (id,title,desciption,color) values (404,'Unknown Address','Invalid - Your PayPal-Address could not be found.','red');
insert into paypal_valid_status (id,title,desciption,color) values (500,'Invalid','Invalid - at the moment we can not send you money per PayPal','red');
insert into paypal_valid_status (id,title,desciption,color) values (501,'Can receive only from homepage.','Invalid - You can only receive money from homepage. Please change your Settings on the PayPal Website.','red');
insert into paypal_valid_status (id,title,desciption,color) values (502,'Can receive only personal payments.','Invalid - You can not receive personal payments. Please change your Settings on the PayPal Website.','red');
insert into paypal_valid_status (id,title,desciption,color) values (503,'Currently unable to receive money.','Invalid - You are currently unable to receive money. Please change your Settings on the PayPal Website.','red');
