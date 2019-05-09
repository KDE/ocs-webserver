create view v_support as
SELECT
  member_id
  ,max(active_time) AS active_time_max
    ,min(active_time)  AS active_time_min 
  , DATE_ADD(max(active_time), INTERVAL 1 YEAR) as valid_till
  ,(DATE_ADD(max(active_time), INTERVAL 1 YEAR) > now()) AS is_valid
  ,0 as is_subscription
  from support
  where status_id = 2 AND type_id = 0
  group by member_id
  
  union
  
  SELECT
  member_id
  ,max(active_time) AS active_time_max
    ,min(active_time)  AS active_time_min 
  , DATE_ADD(max(active_time), INTERVAL 1 MONTH) as valid_till
  ,(DATE_ADD(max(active_time), INTERVAL 1 MONTH) > now()) AS is_valid
  ,1 as is_subscription
  from support
  where status_id = 2 AND type_id = 2 and period= 'M'
  group by member_id
  
  union
  
  SELECT
  member_id
  ,max(active_time) AS active_time_max
    ,min(active_time)  AS active_time_min 
  , DATE_ADD(max(active_time), INTERVAL 1 YEAR) as valid_till
  ,(DATE_ADD(max(active_time), INTERVAL 1 YEAR) > now()) AS is_valid
  ,1 as is_subscription
  from support
  where status_id = 2 AND type_id = 2 and period= 'Y'
  group by member_id;