ALTER TABLE `config_store`
	ADD COLUMN `is_show_blog_news` INT(1) NULL DEFAULT '1' AFTER `is_show_git_projects`,
	ADD COLUMN `is_show_forum_news` INT(1) NULL DEFAULT '1' AFTER `is_show_blog_news`;
