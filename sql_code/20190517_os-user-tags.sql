INSERT INTO `tag_type` (`tag_type_id`, `tag_type_name`) VALUES ('9', 'os-user');
INSERT INTO `tag_group` (`group_id`, `group_name`, `group_display_name`,`is_multi_select`) VALUES ('30', 'os-distribution', 'OS Distribution','1');
INSERT INTO `tag_group` (`group_id`, `group_name`, `group_display_name`,`is_multi_select`) VALUES ('31', 'os-desktop-evn','OS Desktop Enviroment', '0');
INSERT INTO `tag_group` (`group_id`, `group_name`, `group_display_name`,`is_multi_select`) VALUES ('32', 'os-desktop-arch','OS Desktop Archtecture', '0');


INSERT INTO `pling-import`.`tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('ubuntu', 'Ubuntu', '1');
INSERT INTO `pling-import`.`tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('mint', 'Mint', '1');
INSERT INTO `pling-import`.`tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('neon', 'Neon', '1');
INSERT INTO `pling-import`.`tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('gnome', 'Gnome', '1');
INSERT INTO `pling-import`.`tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('kde-plasma', 'KDE Plasma', '1');
INSERT INTO `pling-import`.`tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('64bit', '64 bit', '1');
INSERT INTO `pling-import`.`tag` (`tag_name`, `tag_fullname`, `is_active`) VALUES ('32bit', '32 bit', '1');


insert into tag_group_item(tag_group_id,tag_id) values( 30, 3200);
insert into tag_group_item(tag_group_id,tag_id) values( 30, 3201);
insert into tag_group_item(tag_group_id,tag_id) values( 30, 3202);
insert into tag_group_item(tag_group_id,tag_id) values( 31, 3203);
insert into tag_group_item(tag_group_id,tag_id) values( 31, 3204);
insert into tag_group_item(tag_group_id,tag_id) values( 32, 3205);
insert into tag_group_item(tag_group_id,tag_id) values( 32, 3205);





Tag_Type: 
  Tag_type_id:9
  Tag_type_name:os-user

Tag_Group:
  group_id:30
  group_name: os-distribution
      -> Tag_group_item:
            group_id:30
            tag_id:xx -> Ubuntu

            group_id:30
            tag_id:xy-> Mint

            group_id:30
            tag_id:xt-> Neon

  group_id:31
  group_name: os-desktop-evn 
      -> Tag_group_item:
          group_id:31
          tag_id:x1 -> Gnome

          group_id:31
          tag_id: x2-> KDE Plasma


  group_id:32
  group_name: os-distribution-arch
      -> Tag_group_item:
          group_id:32
          tag_id:a1 -> 64bit

          group_id:32
          tag_id:a2 -> 32bit


Tag_object:
  tag_id: a1
  tag_type_id:9
  tag_group_id:29
  tag_object_id: 24(dummy)
  