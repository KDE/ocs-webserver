INSERT INTO `pling-import`.`activity_log_types` (`activity_log_type_id`, `type_text`) VALUES ('321', 'BackendUserMerged');


CREATE TABLE `merge_member_log` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`object_type` VARCHAR(50) NOT NULL COMMENT 'project, comment, rating,...',
	`object_id` INT NOT NULL,
	`member_id_org` INT NOT NULL COMMENT 'MemberId vor dem Merge',
	`member_id_new` INT NOT NULL COMMENT 'MemberId nach dem Merge',
	PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
;



USE `pling-import`;

DROP PROCEDURE `merge_members`;


DELIMITER //
CREATE PROCEDURE `merge_members`(
	IN `from_member_id` INT,
	IN `to_member_id` INT
)
COMMENT 'Merge of 2 members into 1'
BEGIN


	#Update table member
	UPDATE member m
	SET m.is_active = 0
	AND m.is_deleted = 1
	AND m.deleted_at = NOW()
	WHERE m.member_id = from_member_id;
	
	#Update table member_email
	UPDATE member_email me
	SET me.email_deleted = 1
	WHERE me.email_member_id = from_member_id;
	
	#Update table project
	INSERT INTO merge_member_log
	(
	    SELECT null, 'project', project_id, member_id, to_member_id 
	    FROM project p WHERE p.member_id = from_member_id AND p.type_id = 1
	);
	
	UPDATE project p
	SET p.member_id = to_member_id
	WHERE p.member_id = from_member_id
	AND p.type_id = 1;
	
	#Update table comments
	INSERT INTO merge_member_log
	(
	    SELECT null, 'comments', comment_id, comment_member_id, to_member_id 
	    FROM comments c WHERE c.comment_member_id = from_member_id
	);
	
	UPDATE comments c
	SET c.comment_member_id = to_member_id
	WHERE c.comment_member_id = from_member_id;
	
	#Update table project_follower
	INSERT INTO merge_member_log
	(
	    SELECT null, 'project_follower', project_follower_id, member_id, to_member_id 
	    FROM project_follower f WHERE f.member_id = from_member_id
	);
	
	UPDATE project_follower f
	SET f.member_id = to_member_id
	WHERE f.member_id = from_member_id;
	
	#Update table project_rating
	INSERT INTO merge_member_log
	(
	    SELECT null, 'project_rating', project_rating_id, member_id, to_member_id 
	    FROM project_rating r WHERE r.member_id = from_member_id
	);
	
	UPDATE project_rating r
	SET r.member_id = to_member_id
	WHERE r.member_id = from_member_id;
	
	#Update table project_plings
	INSERT INTO merge_member_log
	(
	    SELECT null, 'project_plings', project_plings_id, member_id, to_member_id 
	    FROM project_plings r WHERE r.member_id = from_member_id
	);
	
	UPDATE project_plings r
	SET r.member_id = to_member_id
	WHERE r.member_id = from_member_id;
	
	
	#Update ppload
	#Update ppload_collections
	INSERT INTO merge_member_log
	(
	    SELECT null, 'ppload_collections', pc.id, pc.owner_id, to_member_id 
	    FROM ppload_collections pc WHERE pc.owner_id = from_member_id
	);
	
	UPDATE ppload.ppload_collections pc
	SET pc.owner_id = to_member_id
	WHERE pc.owner_id = from_member_id;
	
	#Update ppload_files
	INSERT INTO merge_member_log
	(
	    SELECT null, 'ppload_files', pc.id, pc.owner_id, to_member_id 
	    FROM ppload_files pc WHERE pc.owner_id = from_member_id
	);
	
	UPDATE ppload.ppload_files pf
	SET pf.owner_id = to_member_id
	WHERE pf.owner_id = from_member_id;

	#Update ppload_files_downloaded?
	INSERT INTO merge_member_log
	(
	    SELECT null, 'ppload_files_downloaded', pc.id, pc.owner_id, to_member_id 
	    FROM ppload_files_downloaded pc WHERE pc.owner_id = from_member_id
	);
	
	UPDATE ppload.ppload_files_downloaded pfd
	SET pfd.owner_id = to_member_id
	WHERE pfd.owner_id = from_member_id;

	#Update ppload_profiles
	INSERT INTO merge_member_log
	(
	    SELECT null, 'ppload_profiles', pc.id, pc.owner_id, to_member_id 
	    FROM ppload_profiles pc WHERE pc.owner_id = from_member_id
	);
	
	UPDATE ppload.ppload_profiles pp
	SET pp.owner_id = to_member_id
	WHERE pp.owner_id = from_member_id;
	
	
	#Write a log entry
	INSERT INTO `activity_log` (`member_id`, `object_id`, `object_ref`, `object_title`, `object_text`, `activity_type_id`, `time`) VALUES ('22', from_member_id, 'member', 'call merge_members', CONCAT('merge member ', from_member_id,' into member ',to_member_id), '321', NOW());
	

END //
DELIMITER ;



