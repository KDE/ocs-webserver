
INSERT INTO `pling`.`tag_group` (`group_id`, `group_name`, `group_display_name`, `is_multi_select`) VALUES ('34', 'desktop-environments', 'Desktop Environments KDE/GNOME/XFCE...', '0');


INSERT INTO `tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('desktop-env-kde', 'KDE Plasma', '1');
INSERT INTO `tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('desktop-env-gnome', 'GNOME', '1');
INSERT INTO `tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('desktop-env-xfce', 'XFCE', '1');
INSERT INTO `tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('desktop-env-cinnamon', 'Cinnamon', '1');
INSERT INTO `tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('desktop-env-mate', 'Mate', '1');
INSERT INTO `tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('desktop-env-next', 'Next', '1');
INSERT INTO `tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('desktop-env-budgie', 'Budgie', '1');
INSERT INTO `tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('desktop-env-enlightenment', 'Enlightenment', '1');


insert into tag_group_item(tag_group_id,tag_id) values( 34, 5741);
insert into tag_group_item(tag_group_id,tag_id) values( 34, 5742);
insert into tag_group_item(tag_group_id,tag_id) values( 34, 5743);
insert into tag_group_item(tag_group_id,tag_id) values( 34, 5744);
insert into tag_group_item(tag_group_id,tag_id) values( 34, 5745);
insert into tag_group_item(tag_group_id,tag_id) values( 34, 5746);
insert into tag_group_item(tag_group_id,tag_id) values( 34, 5747);
insert into tag_group_item(tag_group_id,tag_id) values( 34, 5748);



CREATE TABLE `tag_rating` (
	`tag_rating_id` INT(11) NOT NULL AUTO_INCREMENT,
	`project_id` INT(11) NOT NULL,
    `member_id` INT(11) NOT NULL,
	`tag_id` INT(11) NOT NULL,
    `vote` INT(1) NOT NULL comment '1 = like -1 = dislike 0=neutral',
    `is_deleted` INT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`deleted_at` TIMESTAMP NULL DEFAULT NULL,        
	PRIMARY KEY (`tag_rating_id`)
)
;

-- CREATE TABLE `category_tag_group_rating`
-- (
--     `category_tag_group_rating_id` INT(11) NOT NULL AUTO_INCREMENT,
--     `category_id`  INT(11) NOT NULL,
--     `tag_group_id` INT(11) NOT NULL,
--     PRIMARY KEY (`category_tag_group_rating_id`)
-- )   
-- ;

ALTER TABLE `project_category`
	ADD COLUMN `tag_rating` INT(11)  comment 'tag_group_id' AFTER `browse_list_type`;
