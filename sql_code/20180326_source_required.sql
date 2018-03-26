ALTER TABLE `project_category`
	ADD COLUMN `source_required` INT(1) NOT NULL DEFAULT '1' AFTER `show_description`;


