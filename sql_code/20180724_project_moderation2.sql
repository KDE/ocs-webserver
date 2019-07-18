ALTER TABLE `project_moderation`
    DROP COLUMN `updated_by`;
ALTER TABLE `project_moderation`
    DROP COLUMN `updated_at`;
ALTER TABLE `project_moderation`
    ADD COLUMN `value` int(1) NOT NULL AFTER `project_id`;