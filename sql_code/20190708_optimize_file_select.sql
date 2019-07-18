DROP TABLE IF EXISTS ppload.stat_ppload_files_downloaded_unique;
CREATE TABLE ppload.stat_ppload_files_downloaded_unique like ppload.stat_ppload_files_downloaded;

DELIMITER $$
drop PROCEDURE generate_stat_files_downloaded;
CREATE  PROCEDURE `generate_stat_files_downloaded`()
BEGIN

	DROP TABLE IF EXISTS ppload.tmp_stat_ppload_files_downloaded;
	
	CREATE TABLE ppload.tmp_stat_ppload_files_downloaded
	(INDEX `idx_coll` (`collection_id`),INDEX `idx_file` (`file_id`))
	   ENGINE MyISAM
	   AS
		SELECT f.owner_id, f.collection_id, f.file_id, COUNT(1) AS count_dl FROM ppload.ppload_files_downloaded f
		WHERE f.downloaded_timestamp < DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00')
		GROUP BY f.collection_id, f.file_id
	;
	RENAME TABLE ppload.stat_ppload_files_downloaded TO ppload.old_stat_ppload_files_downloaded, ppload.tmp_stat_ppload_files_downloaded TO ppload.stat_ppload_files_downloaded;
	DROP TABLE IF EXISTS ppload.old_stat_ppload_files_downloaded;

	DROP TABLE IF EXISTS ppload.tmp_stat_ppload_files_downloaded_unique;
	
	CREATE TABLE ppload.tmp_stat_ppload_files_downloaded_unique
	(INDEX `idx_coll` (`collection_id`),INDEX `idx_file` (`file_id`))
	   ENGINE MyISAM
	   AS
		SELECT f.owner_id, f.collection_id, f.file_id, COUNT(1) AS count_dl FROM ppload.ppload_files_downloaded_unique f
		WHERE f.downloaded_timestamp > '2019-06-01 00:00:00' AND f.downloaded_timestamp < DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00')
		GROUP BY f.collection_id, f.file_id
	;
	RENAME TABLE ppload.stat_ppload_files_downloaded_unique TO ppload.old_stat_ppload_files_downloaded_unique, ppload.tmp_stat_ppload_files_downloaded_unique TO ppload.stat_ppload_files_downloaded_unique;
	DROP TABLE IF EXISTS ppload.old_stat_ppload_files_downloaded_unique;

END$$
DELIMITER ;
