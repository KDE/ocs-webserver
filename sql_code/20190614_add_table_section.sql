CREATE TABLE `section` (
	`section_id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NOT NULL,
	`description` VARCHAR(255) NULL,
	`is_active` INT(1) UNSIGNED NULL DEFAULT '1',
	`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`deleted_at` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`section_id`)
)
COMMENT='Every section has categories, see table section_categories. And every download belongs to a category and to a section.'
COLLATE='latin1_swedish_ci'
;

CREATE TABLE `section_category` (
	`section_category_id` INT NOT NULL AUTO_INCREMENT,
	`section_id` INT NOT NULL,
	`project_category_id` INT NOT NULL,
	PRIMARY KEY (`section_category_id`)
)
COMMENT='every section has n categories'
COLLATE='latin1_swedish_ci'
;

ALTER TABLE `section`
	ADD COLUMN `percent_of_support` INT UNSIGNED NULL DEFAULT NULL COMMENT 'How much of the supporter donations goes to this section' AFTER `description`;


CREATE TABLE `sponsor` (
	`sponsor_id` INT(11) NOT NULL AUTO_INCREMENT,
	`member_id` INT(11) NOT NULL,
	`name` VARCHAR(50) NOT NULL,
	`fullname` VARCHAR(255) NOT NULL,
	`description` VARCHAR(255) NOT NULL,
	`amount` DOUBLE NOT NULL DEFAULT '0' COMMENT 'Sponsoring amount per month in $',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`begin_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`end_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`is_active` INT(10) UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (`sponsor_id`)
)
ENGINE=InnoDB
;


CREATE TABLE `section_sponsor` (
	`section_sponsor_id` INT NOT NULL AUTO_INCREMENT,
	`section_id` INT NOT NULL,
	`sponsor_id` INT NOT NULL,
	`percent_of_sponsoring` INT UNSIGNED NOT NULL COMMENT 'How much of the sponsoring goes to this section',
	PRIMARY KEY (`section_sponsor_id`)
)
COLLATE='latin1_swedish_ci'
;




/*
Insert Data, if needed
INSERT INTO section (name, description) VALUES ('Themes', 'Themes Section');
INSERT INTO section (name, description) VALUES ('Software', 'Software Section');
INSERT INTO section (name, description) VALUES ('Videos', 'Videos Section');
INSERT INTO section (name, description) VALUES ('Music', 'Music Section');

SELECT * FROM section;

INSERT INTO section_category (project_category_id,section_id)
SELECT c.project_category_id, 2 AS section_id FROM project_category c
WHERE c.is_active = 1
;

*/
