ALTER TABLE `config_store`
    ADD COLUMN `is_show_git_projects` INT(1) NULL DEFAULT '1' COMMENT 'Should the latest Git-Projects-Section been shown?' AFTER `is_show_home`;
