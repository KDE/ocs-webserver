ALTER TABLE `section_support`
	ADD COLUMN `creator_id` INT(11) NULL DEFAULT NULL AFTER `project_id`,
	ADD COLUMN `project_category_id` INT(11) NULL DEFAULT NULL AFTER `creator_id`;


UPDATE section_support p
JOIN project pp ON pp.project_id = p.project_id
SET p.creator_id = pp.member_id, p.project_category_id = pp.project_category_id
;