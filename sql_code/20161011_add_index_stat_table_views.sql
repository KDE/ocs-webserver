ALTER TABLE `stat_page_views`
ADD INDEX `idx_created` (`created_at` DESC, `member_id` ASC);

ALTER TABLE `activity_log`
  ADD INDEX `idx_time` (`time` DESC, `member_id` ASC);
