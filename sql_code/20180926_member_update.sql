CREATE TABLE  `member_avatar_type` (
  `member_avatar_type_id` INT NOT NULL ,
  `title` VARCHAR(45) NULL,
  PRIMARY KEY (`member_avatar_type_id`));


ALTER TABLE `member` 
ADD COLUMN `avatar_type_id` INT(11) NOT NULL DEFAULT 1 after avatar;

update member set avatar_type_id = 0;