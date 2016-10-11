ALTER TABLE `activity_log`
  ADD INDEX `idx_time` (`member_id` ASC,`time` DESC);

ALTER TABLE `stat_page_views`
  ADD INDEX `idx_member` (`member_id` ASC, `created_at` ASC);