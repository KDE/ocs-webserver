create table stat_cnt_projects_catid_memberid as
select project_category_id, member_id,count(1) as cnt from project pp 
where  pp.status = 100 and pp.type_id = 1 
group by project_category_id,member_id;

ALTER TABLE `stat_cnt_projects_catid_memberid`
	ADD INDEX `idx_project_category_id` (`project_category_id`),
	ADD INDEX `idx_member_id` (`member_id`);

	
TRUNCATE TABLE stat_cnt_projects_catid_memberid;

INSERT INTO stat_cnt_projects_catid_memberid
select project_category_id, member_id,count(1) as cnt from project pp 
where  pp.status = 100 and pp.type_id = 1 
group by project_category_id,member_id;



CREATE EVENT `e_generate_stat_cnt_projects_catid_memberid`
	ON SCHEDULE
		EVERY 1 DAY STARTS '2019-01-15 03:30:00'
	ON COMPLETION NOT PRESERVE
	ENABLE
	COMMENT ''
	DO BEGIN
	TRUNCATE TABLE stat_cnt_projects_catid_memberid;

	INSERT INTO stat_cnt_projects_catid_memberid
	select project_category_id, member_id,count(1) as cnt from project pp 
	where  pp.status = 100 and pp.type_id = 1 
	group by project_category_id,member_id;
	
END;




	
