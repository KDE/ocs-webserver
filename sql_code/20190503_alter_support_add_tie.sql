
ALTER TABLE `support`
    ADD COLUMN `tier` double(10, 2) COMMENT '0.99, 2,5,10,null' AFTER `amount`;
	