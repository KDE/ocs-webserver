
  ALTER TABLE `config_store`
  ADD COLUMN `layout_explore` VARCHAR(45)
  AFTER `layout_home`;


 ALTER TABLE `config_store`
  ADD COLUMN `layout_pagedetail` VARCHAR(45)
  AFTER `layout_explore`;

 