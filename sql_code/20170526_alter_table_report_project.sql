USE `pling`;
ALTER TABLE `reports_project`
  CHANGE COLUMN `is_deleted` `is_deleted` INT(1) NOT NULL DEFAULT '0' ,
  ADD INDEX `idxReport` (`project_id` ASC, `reported_by` ASC, `is_deleted` ASC, `created_at` ASC),
  DROP INDEX `idxMemberId` ,
  DROP INDEX `idxProjectId` ;