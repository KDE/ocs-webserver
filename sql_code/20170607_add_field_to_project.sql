ALTER TABLE `project`
  ADD COLUMN `spam_checked` INT(1) NOT NULL DEFAULT '0' AFTER `approved`;
