ALTER TABLE `project_plings`
    ADD COLUMN `is_active` int(1) DEFAULT 1 AFTER `created_at`;
ALTER TABLE `project_plings`
    ADD COLUMN `deactive_at` date DEFAULT NULL AFTER `is_active`;