ALTER TABLE `member_download_history` 
ADD COLUMN `anonymous_cookie` VARCHAR(255) NULL AFTER `member_id`;

ALTER TABLE `member_download_history` 
ADD COLUMN `downloaded_ip` VARCHAR(40);

ALTER TABLE `member_download_history` 
CHANGE COLUMN `member_id` `member_id` VARCHAR(255) NULL ;
