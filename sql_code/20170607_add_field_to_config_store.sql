ALTER TABLE `pling`.`config_store`
  ADD COLUMN `cross_domain_login` INT(1) NOT NULL DEFAULT '0' AFTER `package_type`;
