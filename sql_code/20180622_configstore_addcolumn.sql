ALTER TABLE `config_store`
  ADD COLUMN `is_show_title` INT(1) DEFAULT 1
  AFTER `cross_domain_login`;