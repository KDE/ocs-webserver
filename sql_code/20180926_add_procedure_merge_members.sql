INSERT INTO `pling-import`.`activity_log_types` (`activity_log_type_id`, `type_text`) VALUES ('321', 'BackendUserMerged');



DROP PROCEDURE `pling-import`.`merge_members`;


DELIMITER //
CREATE PROCEDURE `pling-import`.`merge_members`(
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
	
	#Update table project
	UPDATE project p
	SET p.member_id = to_member_id
	WHERE p.member_id = from_member_id
	AND p.type_id = 1;
	
	#Update table comments
	UPDATE comments c
	SET c.comment_member_id = to_member_id
	WHERE c.comment_member_id = from_member_id;
	
	#Update table project_follower
	UPDATE project_follower f
	SET f.member_id = to_member_id
	WHERE f.member_id = from_member_id;
	
	#Update table project_rating
	UPDATE project_rating r
	SET r.member_id = to_member_id
	WHERE r.member_id = from_member_id;
	
	#Update table project_plings
	UPDATE project_plings r
	SET r.member_id = to_member_id
	WHERE r.member_id = from_member_id;
	
	#Update ppload
	#Update ppload_collections
	UPDATE ppload.ppload_collections pc
	SET pc.owner_id = to_member_id
	WHERE pc.owner_id = from_member_id;
	
	#Update ppload_files
	UPDATE ppload.ppload_files pf
	SET pf.owner_id = to_member_id
	WHERE pf.owner_id = from_member_id;

	#Update ppload_files_downloaded?
	UPDATE ppload.ppload_files_downloaded pfd
	SET pfd.owner_id = to_member_id
	WHERE pfd.owner_id = from_member_id;

	#Update ppload_profiles
	UPDATE ppload.ppload_profiles pp
	SET pp.owner_id = to_member_id
	WHERE pp.owner_id = from_member_id;
	
	INSERT INTO `pling-import`.`activity_log` (`member_id`, `object_id`, `object_ref`, `object_title`, `object_text`, `activity_type_id`, `time`) VALUES ('22', from_member_id, 'member', 'call merge_members', CONCAT('merge member ', from_member_id,' into member ',to_member_id), '321', NOW());
	
	
	

END //
DELIMITER ;








