CREATE TABLE `project_plings` (
  `project_plings_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` int(1) DEFAULT 0,
  `deleted_at` timestamp,
  PRIMARY KEY (`project_plings_id`),
  CONSTRAINT UC_Pling UNIQUE (`project_id`,`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;