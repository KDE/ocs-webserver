ALTER TABLE `config_store`
	ADD COLUMN `package_type` VARCHAR(45) NULL DEFAULT NULL COMMENT '1-n package_type_ids' AFTER `google_id`;
