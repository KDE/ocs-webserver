ALTER TABLE `config_store`
  ADD COLUMN `is_show_home` INT(1) DEFAULT 0
  AFTER `is_show_title`;

ALTER TABLE `config_store`
  ADD COLUMN `layout_home` VARCHAR(45)
  AFTER `is_show_home`;

ALTER TABLE `config_store`
  ADD COLUMN `layout` VARCHAR(45)
  AFTER `layout_home`;


ALTER TABLE `config_store`
  ADD COLUMN `render_view_postfix` VARCHAR(45)
  AFTER `layout`;
 