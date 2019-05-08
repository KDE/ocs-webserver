DELIMITER $$

CREATE PROCEDURE `generate_stat_files_downloaded`()
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
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


END$$

DELIMITER ;


CREATE EVENT `e_generate_stat_files_downloaded`
	ON SCHEDULE
		EVERY 1 DAY	STARTS '2019-05-01 00:00:00'
	ON COMPLETION PRESERVE
	ENABLE
	COMMENT 'Regenerates ppload.stat_ppload_files_downloaded table'
	DO CALL generate_stat_files_downloaded()
;	