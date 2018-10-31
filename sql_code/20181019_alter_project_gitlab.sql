ALTER TABLE `project`
	ADD COLUMN `is_gitlab_project` INT(1) NOT NULL DEFAULT '0' AFTER `count_downloads_hive`,
	ADD COLUMN `gitlab_project_name` VARCHAR(60) NOT NULL AFTER `is_gitlab_project`;

ALTER TABLE `project`
	ADD COLUMN `gitlab_project_id` INT(11) NULL AFTER `gitlab_project_name`,
	ADD COLUMN `use_gitlab_project_readme` INT(1) NOT NULL DEFAULT '0' AFTER `gitlab_project_id`;
