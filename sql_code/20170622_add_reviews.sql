ALTER TABLE `project_rating`
	ADD COLUMN `comment_id` INT(11) NULL DEFAULT '0' COMMENT 'review for rating' AFTER `user_dislike`,
	ADD COLUMN `rating_active` INT(1) NULL DEFAULT '1' COMMENT 'active = 1, deleted = 0' AFTER `comment_id`;

UPDATE project_rating r SET r.rating_active = 1;
