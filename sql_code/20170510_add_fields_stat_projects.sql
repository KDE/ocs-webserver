ALTER TABLE `stat_projects`
	ADD COLUMN `cat_xdg_type` VARCHAR(100) NOT NULL AFTER `cat_title`,
	ADD COLUMN `cat_name_legacy` VARCHAR(100) NOT NULL AFTER `cat_xdg_type`;
