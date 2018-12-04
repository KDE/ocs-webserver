DROP TABLE pling-import.stat_downloads_24h;

CREATE TABLE pling-import.stat_downloads_24h LIKE ppload.ppload_files_downloaded;
ALTER TABLE pling-import.`stat_downloads_24h`
	ADD INDEX `idx_collection_id` (`collection_id`);

DELIMITER $$
DROP TRIGGER IF EXISTS ppload.ppload_files_downloaded_AFTER_INSERT$$
CREATE TRIGGER ppload.ppload_files_downloaded_AFTER_INSERT AFTER INSERT ON ppload.ppload_files_downloaded FOR EACH ROW BEGIN
	#insert also into table stat_downloads_24h
	INSERT INTO `pling-import`.`stat_downloads_24h` (	
		id,
		client_id,
		owner_id,
		collection_id,
		file_id,
		user_id,
		referer,
		downloaded_timestamp,
		downloaded_ip
	) values (
		new.id,
		new.client_id,
		new.owner_id,
		new.collection_id,
		new.file_id,
		new.user_id,
		new.referer,
		new.downloaded_timestamp,
		new.downloaded_ip
	);
END$$
	
DELIMITER ;


DROP EVENT IF EXISTS `e_generate_stat_downloads_24h`;
CREATE EVENT `e_generate_stat_downloads_24h`
	ON SCHEDULE
		EVERY 1 DAY STARTS '2018-11-30 01:00:00'
	ON COMPLETION PRESERVE
	ENABLE
	COMMENT 'Save download data for the last 24h into table stat_downloads_24h'
	DO 
		TRUNCATE TABLE stat_downloads_24h;