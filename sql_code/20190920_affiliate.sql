CREATE TABLE `affiliate_config` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`percent` DOUBLE NOT NULL DEFAULT '0.15',
	`active_from` INT NOT NULL DEFAULT '201701',
	`active_until` INT NOT NULL DEFAULT '209912',
	PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
;
INSERT INTO `pling`.`affiliate_config` (`id`) VALUES ('1');
