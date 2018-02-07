DROP TRIGGER IF EXISTS `comment_update`;

DELIMITER $$

CREATE DEFINER = CURRENT_USER TRIGGER `comment_update` BEFORE UPDATE ON `comments` FOR EACH ROW 
  BEGIN

	IF NEW.comment_active = 0 AND OLD.comment_active = 1 THEN
	
		UPDATE project p
		SET p.count_comments = (p.count_comments-1)
		WHERE p.project_id = NEW.comment_target_id;
		
		SET NEW.comment_deleted_at = NOW();
		
	END IF;
	
	IF NEW.comment_active = 1 AND OLD.comment_active = 0 THEN
	
		UPDATE project p
		SET p.count_comments = (p.count_comments+1)
		WHERE p.project_id = NEW.comment_target_id;
		
		SET NEW.comment_deleted_at = null;
		
	END IF;
	
  END$$
DELIMITER ;