
ALTER TABLE `project_follower`
	ADD COLUMN `source_id` INT(1) UNSIGNED NULL DEFAULT '0' AFTER `created_at`,
	ADD COLUMN `source_pk` INT(11) UNSIGNED NULL AFTER `source_id`;



select 
	count(1)
from H01.fan f
join H01.users u on u.userdb = 0 and u.login = f.user
join `pling`.project p on p.source_id = 1 and p.source_pk = f.contentid and p.source_type = 'project'
join `pling`.member m on m.is_active = 1 and m.is_deleted = 0 and m.source_id = 1 and m.source_pk = u.id
where f.userdb = 0;


INSERT INTO project_follower
(
	select 
		null as project_follower_id,
		p.project_id,
		m.member_id,
		from_unixtime(f.timestamp) AS `created_at`,
		1 as source_id,
		f.id as source_pk
	from hive.fan f
	join hive.users u on u.userdb = 0 and u.login = f.user
	join `pling-import`.project p on p.source_id = 1 and p.source_pk = f.contentid and p.source_type = 'project'
	join `pling-import`.member m on m.is_active = 1 and m.is_deleted = 0 and m.source_id = 1 and m.source_pk = u.id
	where f.userdb = 0
);


