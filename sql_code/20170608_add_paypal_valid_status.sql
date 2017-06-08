ALTER TABLE `member`
	ADD COLUMN `paypal_valid_status` MEDIUMINT NULL DEFAULT NULL AFTER `paypal_mail`;
/*	
NULL = unknown (default)
100 = valid (can receive money)
500 = invalid
501 = can only receive money from homepage
502 = no personal money send
503 = This recipient is currently unable to receive money
*/
