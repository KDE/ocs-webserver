ALTER TABLE `tag_object`
    ADD COLUMN `tag_group_id` int(11) AFTER `tag_type_id`;
ALTER TABLE `tag_object`
    DROP INDEX `tags_unique`;

ALTER TABLE `tag_object`
    ADD UNIQUE KEY `tags_unique` (`tag_id`, `tag_type_id`, `tag_object_id`, `tag_group_id`);
