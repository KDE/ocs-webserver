ALTER TABLE `tag_group`
    ADD COLUMN `group_display_name` VARCHAR(255) NOT NULL AFTER `group_name`;
ALTER TABLE `tag_group`
    ADD COLUMN `group_legacy_name` VARCHAR(45) NOT NULL AFTER `group_display_name`;

UPDATE `pling-import`.`tag_group`
SET `group_display_name`='Packagetype'
WHERE `group_id` = 8;
UPDATE `pling-import`.`tag_group`
SET `group_display_name`='Architecture'
WHERE `group_id` = 9;

UPDATE `pling-import`.`tag_group`
SET `group_legacy_name`='license'
WHERE `group_id` = 7;
UPDATE `pling-import`.`tag_group`
SET `group_legacy_name`='packagetype'
WHERE `group_id` = 8;
UPDATE `pling-import`.`tag_group`
SET `group_legacy_name`='architecture'
WHERE `group_id` = 9;
