
ALTER TABLE `file_types`
CHANGE COLUMN `text` `name` VARCHAR(255) NOT NULL ;

ALTER TABLE `project_file_type`
CHANGE COLUMN `file_type_id` `filetype_id` INT(11) NOT NULL ,
ADD INDEX `idx_type_id` (`filetype_id` ASC);