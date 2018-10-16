CREATE TABLE  `member_avatar_type` (
  `member_avatar_type_id` INT NOT NULL ,
  `title` VARCHAR(45) ,
  PRIMARY KEY (`member_avatar_type_id`));


INSERT INTO `member_avatar_type` (`member_avatar_type_id`, `title`) VALUES (0, 'unknow');
INSERT INTO `member_avatar_type` (`member_avatar_type_id`, `title`) VALUES (1, 'auto generated letter avatar');
INSERT INTO `member_avatar_type` (`member_avatar_type_id`, `title`) VALUES (2, 'user uploaded');


ALTER TABLE `member` 
ADD COLUMN `avatar_type_id` INT(11) NOT NULL DEFAULT 1 after avatar;


update member set avatar_type_id = 0;