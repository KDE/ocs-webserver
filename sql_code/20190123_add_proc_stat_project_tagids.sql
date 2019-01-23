drop PROCEDURE  generate_stat_project_ids;

DELIMITER $$
CREATE PROCEDURE `generate_stat_project_ids`()
BEGIN
	DROP TABLE IF EXISTS tmp_stat_project_tagids;
	CREATE TABLE tmp_stat_project_tagids
	(INDEX `idx_tag_id` (`tag_id`),INDEX `idx_project_id` (`project_id`))
	ENGINE MyISAM
	AS
	
	select distinct tag_id, project_id from (
	
		select distinct tag.tag_id, tgo.tag_object_id AS project_id        
		FROM tag_object AS tgo
		JOIN tag ON tag.tag_id = tgo.tag_id
		WHERE tag_type_id = 1 #project        
		UNION ALL        
		select distinct tag.tag_id, tgo.tag_parent_object_id AS project_id        
		FROM tag_object AS tgo
		JOIN tag ON tag.tag_id = tgo.tag_id
		JOIN ppload.ppload_files files ON files.id = tgo.tag_object_id
		WHERE tag_type_id = 3 #file
		AND files.active = 1
	) A
	;
	RENAME TABLE stat_project_tagids TO old_stat_project_tagids, tmp_stat_project_tagids TO stat_project_tagids;
	DROP TABLE IF EXISTS old_stat_project_tagids;

END$

DELIMITER ;
