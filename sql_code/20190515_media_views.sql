CREATE TABLE `media_views` (
	`media_view_id` BIGINT(20) NOT NULL,
	`media_view_type_id` INT(1) NOT NULL,
	`project_id` INT(11) NOT NULL,
	`collection_id` INT(11) NOT NULL,
	`file_id` INT(11) NOT NULL,
	`member_id` INT(11) NULL DEFAULT NULL,
	`referer` VARCHAR(255) NULL DEFAULT NULL,
	`start_timestamp` DATETIME NULL DEFAULT NULL,
	`stop_timestamp` DATETIME NULL DEFAULT NULL,
	`ip` VARCHAR(39) NULL DEFAULT NULL,
	`source` VARCHAR(39) NULL DEFAULT NULL,
	PRIMARY KEY (`media_view_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

ALTER TABLE `media_views`
	ADD INDEX `idx_file` (`collection_id`, `file_id`),
	ADD INDEX `idx_media_type` (`media_view_type_id`);


CREATE TABLE `media_view_type` (
	`media_view_type_id` INT(1) NOT NULL,
	`name` VARCHAR(35) NULL DEFAULT NULL,
	`full_name` VARCHAR(255) NULL DEFAULT NULL,
	`description` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`media_view_type_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

INSERT INTO media_view_type (media_view_type_id,NAME,full_name,description) VALUES (1,'video','Video','Video');
INSERT INTO media_view_type (media_view_type_id,NAME,full_name,description) VALUES (2,'music','Music','Music');
INSERT INTO media_view_type (media_view_type_id,NAME,full_name,description) VALUES (3,'book','Book','Book');


SELECT * FROM media_views m
JOIN media_view_type mp ON mp.media_view_type_id = m.media_view_type_id
;


