

DROP VIEW IF EXISTS `v_support`;

CREATE VIEW `v_support` AS
    SELECT `member_id`
         , max(`active_time`)                                      AS `active_time_max`
         , min(`active_time`)                                      AS `active_time_min`
         , DATE_ADD(max(`active_time`), INTERVAL 1 YEAR)           AS `valid_till`
         , (DATE_ADD(max(`active_time`), INTERVAL 1 YEAR) > now()) AS `is_valid`
         , 0                                                       AS `is_subscription`
    FROM `support`
    WHERE `status_id` = 2
      AND `type_id` = 0
    GROUP BY `member_id`

    UNION

    SELECT `member_id`
         , max(`active_time`)                                       AS `active_time_max`
         , min(`active_time`)                                       AS `active_time_min`
         , DATE_ADD(max(`active_time`), INTERVAL 1 MONTH)           AS `valid_till`
         , (DATE_ADD(max(`active_time`), INTERVAL 1 MONTH) > now()) AS `is_valid`
         , 1                                                        AS `is_subscription`
    FROM `support`
    WHERE `status_id` = 2
      AND `type_id` = 2
      AND `period` = 'M'
    GROUP BY `member_id`

    UNION

    SELECT `member_id`
         , max(`active_time`)                                      AS `active_time_max`
         , min(`active_time`)                                      AS `active_time_min`
         , DATE_ADD(max(`active_time`), INTERVAL 1 YEAR)           AS `valid_till`
         , (DATE_ADD(max(`active_time`), INTERVAL 1 YEAR) > now()) AS `is_valid`
         , 1                                                       AS `is_subscription`
    FROM `support`
    WHERE `status_id` = 2
      AND `type_id` = 2
      AND `period` = 'Y'
    GROUP BY `member_id`;