
DROP TABLE member_score;

CREATE TABLE `member_score` (
	`member_score_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`member_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`score` INT(10) NOT NULL DEFAULT '0',
	`count_product` INT(11) NULL DEFAULT '0',
	`count_pling` INT(11) NULL DEFAULT '0',
	`count_like` INT(11) NULL DEFAULT '0',
	`count_comment` INT(11) NULL DEFAULT '0',
	`count_years_membership` INT(11) NULL DEFAULT '0',
	`count_report_product_spam` INT(11) NULL DEFAULT '0',
	`count_report_product_fraud` INT(11) NULL DEFAULT '0',
	`count_report_comment` INT(11) NULL DEFAULT '0',
	`count_report_member` INT(11) NULL DEFAULT '0',
	`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`member_score_id`),
	INDEX `idx_member` (`member_id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB;


DROP TABLE member_score_factors;
CREATE TABLE `member_score_factors` (
	`factor_id` INT NOT NULL,
	`name` VARCHAR(50) NULL,
	`descrption` VARCHAR(255) NULL,
	`value` INT NULL,
	PRIMARY KEY (`factor_id`)
)
COLLATE='latin1_swedish_ci'
;

USE pling;
INSERT INTO `member_score_factors` (`factor_id`, `name`, `value`) VALUES ('1', 'project', '1');
INSERT INTO `member_score_factors` (`factor_id`, `name`, `value`) VALUES ('2', 'pling', '100');
INSERT INTO `member_score_factors` (`factor_id`, `name`, `value`) VALUES ('3', 'like', '100');
INSERT INTO `member_score_factors` (`factor_id`, `name`, `value`) VALUES ('4', 'comment', '10');
INSERT INTO `member_score_factors` (`factor_id`, `name`, `value`) VALUES ('5', 'year', '10');
INSERT INTO `member_score_factors` (`factor_id`, `name`, `value`) VALUES ('6', 'report_product_spam', '-100');
INSERT INTO `member_score_factors` (`factor_id`, `name`, `value`) VALUES ('7', 'report_product_fraud', '-100');
