#need super privileges to turn on the event scheduler
#SET GLOBAL event_scheduler = ON;

USE `pling`;

DROP EVENT IF EXISTS `e_generate_page_views_today`;

CREATE
  DEFINER = CURRENT_USER
EVENT IF NOT EXISTS `e_generate_page_views_today`
  ON SCHEDULE
    EVERY 30 MINUTE STARTS DATE_FORMAT(NOW(),'%Y-%m-%d 05:00:00')
  ON COMPLETION PRESERVE
  -- DISABLE ON SLAVE
  COMMENT 'Regenerates page views counter for projects on every hour'
DO
  CALL pling.generate_stat_views_today();
