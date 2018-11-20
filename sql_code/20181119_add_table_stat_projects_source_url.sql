CREATE DEFINER=`root`@`%` EVENT `e_generate_stat_projects_source_url`
	ON SCHEDULE
		EVERY 5 MINUTE STARTS '2018-11-19 11:57:15'
	ON COMPLETION NOT PRESERVE
	ENABLE
	COMMENT ''
	DO BEGIN

	create table stat_projects_source_url_tmp 
	(PRIMARY KEY `primary` (`project_id`)
			,INDEX `idx_proj` (`project_id`)
			,INDEX `idx_member` (`member_id`) 
			,INDEX `idx_source_url` (`source_url`(50))
	)
   ENGINE MyISAM
	as
	select p.project_id, p.member_id,TRIM(TRAILING '/' FROM p.source_url) as source_url, p.created_at, p.changed_at from stat_projects p
	where p.source_url is not null 
	and p.source_url<>'' 
	and p.status=100
	;
	rename table stat_projects_source_url to stat_projects_source_url_old;
	rename table stat_projects_source_url_tmp to stat_projects_source_url;
	drop table stat_projects_source_url_old;

END