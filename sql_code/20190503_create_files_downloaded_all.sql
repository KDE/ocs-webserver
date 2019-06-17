
CREATE TABLE `ppload`.`ppload_files_downloaded_all` LIKE `ppload`.`ppload_files_downloaded`;


ALTER TABLE `ppload_files_downloaded_all`
    ADD COLUMN `source` VARCHAR(39) NULL DEFAULT NULL AFTER `downloaded_ip`;
ALTER TABLE `ppload_files_downloaded_all`
    ADD COLUMN `link_type` VARCHAR(39) NULL DEFAULT NULL AFTER `source`;