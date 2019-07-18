-- drop old procedure
DROP PROCEDURE `generate_tmp_cat_tag_proj`;


DROP TABLE IF EXISTS `stat_cat_tree_hierachie`;

CREATE TABLE `stat_cat_tree_hierachie`
(
    `project_category_id` int,
    `ancestor_id_path`    varchar(50),
    `catid1`              int,
    `catid2`              int,
    `catid3`              int,
    `catid4`              int,
    `catid5`              int,
    `catid6`              int,
    `created_at`          timestamp NOT NULL DEFAULT now(),
    PRIMARY KEY (`project_category_id`),
    INDEX `ix_stat_cat_tree_hierachie_1` (`catid1`),
    INDEX `ix_stat_cat_tree_hierachie_2` (`catid2`),
    INDEX `ix_stat_cat_tree_hierachie_3` (`catid3`),
    INDEX `ix_stat_cat_tree_hierachie_4` (`catid4`),
    INDEX `ix_stat_cat_tree_hierachie_5` (`catid5`),
    INDEX `ix_stat_cat_tree_hierachie_6` (`catid6`)
);

TRUNCATE TABLE `stat_cat_tree_hierachie`;
INSERT INTO `stat_cat_tree_hierachie`
SELECT `t`.`project_category_id`,
       `t`.`ancestor_id_path`,
       SPLIT_STRING(`t`.`ancestor_id_path`, ',', 1) AS `catid1`, -- root no category tags ignore
       SPLIT_STRING(`t`.`ancestor_id_path`, ',', 2) AS `catid2`,
       SPLIT_STRING(`t`.`ancestor_id_path`, ',', 3) AS `catid3`,
       SPLIT_STRING(`t`.`ancestor_id_path`, ',', 4) AS `catid4`,
       SPLIT_STRING(`t`.`ancestor_id_path`, ',', 5) AS `catid5`,
       SPLIT_STRING(`t`.`ancestor_id_path`, ',', 6) AS `catid6`,
       now()                                        AS `created_at`
FROM `stat_cat_tree` `t`;


DROP TABLE IF EXISTS `tmp_project_system_tag`;

CREATE TABLE `tmp_project_system_tag`
(
    `project_id`          INT(11)     NOT NULL,
    `project_category_id` INT(11)     NOT NULL,
    `tag_id`              INT(11)     NOT NULL,
    `ancestor_id_path`    VARCHAR(50) NULL DEFAULT NULL,
    INDEX (`project_id`, `project_category_id`, `tag_id`)
);

DROP PROCEDURE IF EXISTS `generate_tmp_cat_tag_proj_init`;

DELIMITER $$
CREATE PROCEDURE `generate_tmp_cat_tag_proj_init`()
BEGIN

    TRUNCATE TABLE `tmp_project_system_tag`;

    TRUNCATE TABLE `stat_cat_tree_hierachie`;
    INSERT INTO `stat_cat_tree_hierachie`
    SELECT `t`.`project_category_id`,
           `t`.`ancestor_id_path`,
           SPLIT_STRING(`t`.`ancestor_id_path`, ',', 1) AS `catid1`, -- root no category tags ignore
           SPLIT_STRING(`t`.`ancestor_id_path`, ',', 2) AS `catid2`,
           SPLIT_STRING(`t`.`ancestor_id_path`, ',', 3) AS `catid3`,
           SPLIT_STRING(`t`.`ancestor_id_path`, ',', 4) AS `catid4`,
           SPLIT_STRING(`t`.`ancestor_id_path`, ',', 5) AS `catid5`,
           SPLIT_STRING(`t`.`ancestor_id_path`, ',', 6) AS `catid6`,
           now()                                        AS `created_at`
    FROM `stat_cat_tree` `t`;

    INSERT INTO `tmp_project_system_tag`
    SELECT `p`.`project_id`, `p`.`project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree_hierachie` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = `t`.`catid2`
    WHERE `p`.`status` = 100;

    INSERT INTO `tmp_project_system_tag`
    SELECT `p`.`project_id`, `p`.`project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree_hierachie` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = `t`.`catid3`
    WHERE `p`.`status` = 100;

    INSERT INTO `tmp_project_system_tag`
    SELECT `p`.`project_id`, `p`.`project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree_hierachie` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = `t`.`catid4`
    WHERE `p`.`status` = 100;

    INSERT INTO `tmp_project_system_tag`
    SELECT `p`.`project_id`, `p`.`project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree_hierachie` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = `t`.`catid5`
    WHERE `p`.`status` = 100;

    INSERT INTO `tmp_project_system_tag`
    SELECT `p`.`project_id`, `p`.`project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree_hierachie` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = `t`.`catid6`
    WHERE `p`.`status` = 100;


    DROP TABLE IF EXISTS `tmp_tag_object_to_delete`;
    CREATE TEMPORARY TABLE `tmp_tag_object_to_delete`
    (
        PRIMARY KEY `primary` (`tag_item_id`)
    )
        ENGINE MyISAM
    AS
    SELECT `o`.`tag_item_id`
    FROM `tag_object` `o`
             LEFT JOIN `tmp_project_system_tag` `t`
                       ON `t`.`project_id` = `o`.`tag_object_id` AND `t`.`tag_id` = `o`.`tag_id`
    WHERE `o`.`tag_group_id` = 6
      AND `o`.`is_deleted` = 0
      AND `t`.`project_id` IS NULL;

    /*DELETE SYSTEM TAGS -- 12155 TO DELETE*/

    UPDATE `tag_object`
    SET `is_deleted`  = 1,
        `tag_changed` = now()
    WHERE `tag_item_id` IN
          (
              SELECT `o`.`tag_item_id`
              FROM `tmp_tag_object_to_delete` `o`
          );


    DROP TABLE IF EXISTS `tmp_tag_object_to_insert`;
    CREATE TEMPORARY TABLE `tmp_tag_object_to_insert`
        /*(INDEX (project_id,project_category_id,tag_id))*/
        ENGINE MyISAM
    AS
    SELECT `t`.*
    FROM `tmp_project_system_tag` `t`
             LEFT JOIN `tag_object` `o` ON `t`.`project_id` = `o`.`tag_object_id` AND `t`.`tag_id` = `o`.`tag_id` AND
                                           `o`.`tag_group_id` = 6
    WHERE `o`.`tag_item_id` IS NULL;


    INSERT INTO `tag_object`
    SELECT NULL             AS `tag_item_id`,
           `p`.`tag_id`,
           1                AS `tag_type_id`,
           6                AS `tag_group_id`,
           `p`.`project_id` AS `tag_object_id`,
           NULL             AS `tag_parenet_object_id`,
           NOW()            AS `tag_created`,
           NULL             AS `tag_changed`,
           0                AS `is_deleted`
    FROM (
             SELECT DISTINCT *
             FROM `tmp_tag_object_to_insert`
         ) `p`;


END;
$$

CREATE EVENT `e_generate_tmp_cat_tag_proj_init`
    ON SCHEDULE
        EVERY 1 DAY STARTS '2019-02-19 17:00:00'
    ON COMPLETION NOT PRESERVE
    ENABLE
    COMMENT ''
    DO
    BEGIN
        CALL generate_tmp_cat_tag_proj_init();
    END$$

DELIMITER ;
