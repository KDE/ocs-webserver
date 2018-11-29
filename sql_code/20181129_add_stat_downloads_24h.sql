create table stat_downloads_24h as
select count(1) as anz, f.collection_id, p.project_id from ppload.ppload_files_downloaded f
join project p on p.ppload_collection_id = f.collection_id AND p.`status` = 100
where f.downloaded_timestamp >= subdate(now(), 1)
group by f.collection_id, p.project_id
;

ALTER TABLE `stat_downloads_24h`
	ADD INDEX `idx_project_id` (`project_id`);
	


DELIMITER //
DROP PROCEDURE `generate_stat_downloads_24h`;
CREATE PROCEDURE `generate_stat_downloads_24h`()
BEGIN

	DECLARE exit handler for sqlexception
	  BEGIN
	    -- ERROR
	  ROLLBACK;
	END;

	START TRANSACTION;

    DROP TABLE IF EXISTS `temp_stat_downloads_24h`;
    
    CREATE TABLE `temp_stat_downloads_24h` (
      `anz`            BIGINT,
      `collection_id`  INT(11) NOT NULL,
      `project_id`     INT(11)  NOT NULL,
      INDEX `idx_project` (`project_id` ASC)
    )
      ENGINE = MyISAM
    AS
      select count(1) as anz, f.collection_id, p.project_id from ppload.ppload_files_downloaded f
		join project p on p.ppload_collection_id = f.collection_id AND p.`status` = 100
		where f.downloaded_timestamp >= subdate(now(), 1)
		group by f.collection_id, p.project_id;

    ALTER TABLE `stat_downloads_24h`
      RENAME TO  `old_stat_downloads_24h` ;

    ALTER TABLE `temp_stat_downloads_24h`
      RENAME TO  `stat_downloads_24h` ;

    DROP TABLE `old_stat_downloads_24h`;
    
    COMMIT;

END //
DELIMITER ;

DROP EVENT IF EXISTS `e_generate_stat_downloads_24h`;
CREATE EVENT `e_generate_stat_downloads_24h`
	ON SCHEDULE
		EVERY 1 DAY STARTS '2018-11-20 05:00:00'
	ON COMPLETION PRESERVE
	ENABLE
	COMMENT 'Save download data for the last 24h into table stat_downloads_24h'
	DO 
		CALL generate_stat_downloads_24h();


CALL `generate_stat_downloads_24h`();
