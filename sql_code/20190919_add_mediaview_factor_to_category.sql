ALTER TABLE `project_category`
	CHANGE COLUMN `dl_pling_factor` `dl_pling_factor` DOUBLE UNSIGNED NULL DEFAULT '1' COMMENT 'Dowmload Factor' AFTER `orderPos`,
	ADD COLUMN `mv_pling_factor` DOUBLE UNSIGNED NULL DEFAULT '1' COMMENT 'Mediaview Factor' AFTER `dl_pling_factor`;

UPDATE project_category c
SET c.mv_pling_factor = c.dl_pling_factor
;

