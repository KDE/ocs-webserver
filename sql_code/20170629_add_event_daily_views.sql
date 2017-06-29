USE `pling`;

DROP EVENT IF EXISTS `e_generate_page_views_today`;

CREATE
  DEFINER = CURRENT_USER
EVENT IF NOT EXISTS `e_generate_page_views_today`
  ON SCHEDULE
    EVERY 30 MINUTE
DISABLE ON SLAVE
  COMMENT 'Regenerates page views counter for projects on every hour'
DO
  CALL generate_stat_views_today();