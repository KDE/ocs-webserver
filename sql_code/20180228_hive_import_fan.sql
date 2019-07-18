ALTER TABLE `project_follower`
    ADD COLUMN `source_id` INT(1) UNSIGNED NULL DEFAULT '0' AFTER `created_at`,
    ADD COLUMN `source_pk` INT(11) UNSIGNED NULL AFTER `source_id`;



SELECT count(1)
FROM `H01`.`fan` `f`
         JOIN `H01`.`users` `u` ON `u`.`userdb` = 0 AND `u`.`login` = `f`.`user`
         JOIN `pling`.`project` `p`
              ON `p`.`source_id` = 1 AND `p`.`source_pk` = `f`.`contentid` AND `p`.`source_type` = 'project'
         JOIN `pling`.`member` `m`
              ON `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 AND `m`.`source_id` = 1 AND `m`.`source_pk` = `u`.`id`
WHERE `f`.`userdb` = 0;


INSERT INTO `project_follower`
    (
        SELECT NULL                           AS `project_follower_id`,
               `p`.`project_id`,
               `m`.`member_id`,
               from_unixtime(`f`.`timestamp`) AS `created_at`,
               1                              AS `source_id`,
               `f`.`id`                       AS `source_pk`
        FROM `hive`.`fan` `f`
                 JOIN `hive`.`users` `u` ON `u`.`userdb` = 0 AND `u`.`login` = `f`.`user`
                 JOIN `pling-import`.`project` `p`
                      ON `p`.`source_id` = 1 AND `p`.`source_pk` = `f`.`contentid` AND `p`.`source_type` = 'project'
                 JOIN `pling-import`.`member` `m`
                      ON `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 AND `m`.`source_id` = 1 AND
                         `m`.`source_pk` = `u`.`id`
        WHERE `f`.`userdb` = 0
    );


