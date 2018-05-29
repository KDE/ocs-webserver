alter table tag_object add column tag_group_id int(11) after tag_type_id;
ALTER TABLE tag_object   
  DROP INDEX tags_unique;
 
 ALTER TABLE tag_object   
   ADD UNIQUE KEY `tags_unique` (`tag_id`,`tag_type_id`,`tag_object_id`,`tag_group_id`);
