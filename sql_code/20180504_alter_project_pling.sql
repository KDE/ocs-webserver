alter table project_plings add column is_active int(1)  default 1 after created_at;
alter table project_plings add column deactive_at date default null after is_active;