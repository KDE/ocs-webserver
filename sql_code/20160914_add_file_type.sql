CREATE TABLE `file_types` (
	`filetype_id` INT NOT NULL AUTO_INCREMENT,
	`text` VARCHAR(255) NOT NULL,
	`order` INT NULL,
	`is_active` INT(1) NULL DEFAULT '1',
	PRIMARY KEY (`filetype_id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;
CREATE TABLE `project_file_type` (
	`project_id` INT NOT NULL,
	`file_id` INT NOT NULL,
	`file_type_id` INT NOT NULL,
	PRIMARY KEY (`project_id`, `file_id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;

INSERT INTO `pling-import`.`file_types` (`filetype_id`, `text`, `order`) VALUES ('1', 'AppImage', '1');
INSERT INTO `pling-import`.`file_types` (`filetype_id`, `text`, `order`) VALUES ('2', 'Android (APK)', '2');
INSERT INTO `pling-import`.`file_types` (`filetype_id`, `text`, `order`) VALUES ('3', 'OS X compatible', '3');
INSERT INTO `pling-import`.`file_types` (`filetype_id`, `text`, `order`) VALUES ('4', 'Windows executable', '4');

ALTER TABLE `file_types`
CHANGE COLUMN `text` `name` VARCHAR(255) NOT NULL ;

ALTER TABLE `project_file_type`
CHANGE COLUMN `file_type_id` `filetype_id` INT(11) NOT NULL ,
ADD INDEX `idx_type_id` (`filetype_id` ASC);