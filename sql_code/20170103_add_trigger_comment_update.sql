/**
 * If a comment will be deleted (hidden) then update num of
 * active comments in project (viewd in explore list).
 */
CREATE TRIGGER `comment_update` BEFORE UPDATE ON `comments` FOR EACH ROW BEGIN

	IF NEW.comment_active = 0 AND OLD.comment_active = 1 THEN

		UPDATE project p
		SET p.count_comments = (p.count_comments-1)
		WHERE p.project_id = NEW.comment_target_id;

	END IF;
END
