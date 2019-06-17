DROP TABLE `tmp_cat_tag_proj`;

CREATE TABLE `tmp_cat_tag_proj`
(
    `project_id`          INT(11)     NOT NULL,
    `project_category_id` INT(11)     NOT NULL,
    `tag_id`              INT(11)     NOT NULL,
    `ancestor_id_path`    VARCHAR(50) NULL DEFAULT NULL,
    PRIMARY KEY (`project_id`, `project_category_id`, `tag_id`)
)
    COLLATE = 'latin1_swedish_ci'
    ENGINE = MyISAM
;


DELIMITER $
DROP PROCEDURE IF EXISTS `generate_tmp_cat_tag_proj`$

CREATE PROCEDURE `generate_tmp_cat_tag_proj`()
BEGIN

    TRUNCATE TABLE `tmp_cat_tag_proj`;

    INSERT INTO `tmp_cat_tag_proj`

    SELECT `p`.`project_id`, `p`.`project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = `t`.`project_category_id`
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

    INSERT IGNORE INTO `tmp_cat_tag_proj`
        #ebene 1
    SELECT `p`.`project_id`, `c`.`category_id` AS `project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = (SPLIT_STRING(`t`.`ancestor_id_path`, ',', 1))
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

    INSERT IGNORE INTO `tmp_cat_tag_proj`
        #ebene 2
    SELECT `p`.`project_id`, `c`.`category_id` AS `project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = (SPLIT_STRING(`t`.`ancestor_id_path`, ',', 2))
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

    INSERT IGNORE INTO `tmp_cat_tag_proj`
        #ebene 3
    SELECT `p`.`project_id`, `c`.`category_id` AS `project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = (SPLIT_STRING(`t`.`ancestor_id_path`, ',', 3))
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

    INSERT IGNORE INTO `tmp_cat_tag_proj`
        #ebene 4
    SELECT `p`.`project_id`, `c`.`category_id` AS `project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = (SPLIT_STRING(`t`.`ancestor_id_path`, ',', 4))
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

    INSERT IGNORE INTO `tmp_cat_tag_proj`
        #ebene 5
    SELECT `p`.`project_id`, `c`.`category_id` AS `project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = (SPLIT_STRING(`t`.`ancestor_id_path`, ',', 5))
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

    INSERT IGNORE INTO `tmp_cat_tag_proj`
        #ebene 6
    SELECT `p`.`project_id`, `c`.`category_id` AS `project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = (SPLIT_STRING(`t`.`ancestor_id_path`, ',', 6))
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

    INSERT IGNORE INTO `tmp_cat_tag_proj`
        #ebene 7
    SELECT `p`.`project_id`, `c`.`category_id` AS `project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = (SPLIT_STRING(`t`.`ancestor_id_path`, ',', 7))
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

    INSERT IGNORE INTO `tmp_cat_tag_proj`
        #ebene 8
    SELECT `p`.`project_id`, `c`.`category_id` AS `project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = (SPLIT_STRING(`t`.`ancestor_id_path`, ',', 8))
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

    INSERT IGNORE INTO `tmp_cat_tag_proj`
        #ebene 9
    SELECT `p`.`project_id`, `c`.`category_id` AS `project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = (SPLIT_STRING(`t`.`ancestor_id_path`, ',', 9))
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

    INSERT IGNORE INTO `tmp_cat_tag_proj`
        #ebene 10
    SELECT `p`.`project_id`, `c`.`category_id` AS `project_category_id`, `c`.`tag_id`, `t`.`ancestor_id_path`
    FROM `project` `p`
             JOIN `stat_cat_tree` `t` ON `t`.`project_category_id` = `p`.`project_category_id`
             JOIN `category_tag` `c` ON `c`.`category_id` = (SPLIT_STRING(`t`.`ancestor_id_path`, ',', 10))
             JOIN `tag` `ta` ON `ta`.`tag_id` = `c`.`tag_id`
    WHERE `p`.`status` = 100;

END$

DELIMITER ;

DELIMITER $

DROP PROCEDURE IF EXISTS `SPLIT_STRING`$

CREATE FUNCTION `SPLIT_STRING`(`s` VARCHAR(1024),
                               `del` CHAR(1),
                               `i` INT)
    RETURNS varchar(1024) CHARSET `latin1`
BEGIN

    DECLARE `n` INT;

    -- get max number of items
    SET `n` = LENGTH(`s`) - LENGTH(REPLACE(`s`, `del`, '')) + 1;

    IF `i` > `n` THEN
        RETURN NULL ;
    ELSE
        RETURN SUBSTRING_INDEX(SUBSTRING_INDEX(`s`, `del`, `i`), `del`, -1);
    END IF;

END$
DELIMITER ;	