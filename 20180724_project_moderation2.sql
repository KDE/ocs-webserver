alter table project_moderation drop column updated_by;
alter table project_moderation drop column updated_at;
alter table project_moderation add column `value` int(1) not null after `project_id`;