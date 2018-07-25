CREATE TABLE `project_moderation_type` (  
  `project_moderation_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT null ,
  `tag_id` int(11) DEFAULT null COMMENT 'if exist insert/remove project tag_id relation',
  PRIMARY KEY (`project_moderation_type_id`)
)ENGINE=INNODB;


CREATE TABLE `project_moderation` (
  `project_moderation_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_moderation_type_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  `is_valid` int(1) NOT NULL DEFAULT '0' COMMENT 'Admin can mark a report as valid',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,  
  `updated_at` Datetime DEFAULT null,  
  PRIMARY KEY (`project_moderation_id`),
  FOREIGN KEY (project_moderation_type_id)
      REFERENCES project_moderation_type(project_moderation_type_id)
)ENGINE=INNODB;

