DROP TRIGGER IF EXISTS `comment_update`;

DELIMITER $$

CREATE DEFINER = CURRENT_USER TRIGGER `comment_update`
    BEFORE UPDATE
    ON `comments`
    FOR EACH ROW
BEGIN
	DECLARE ratingID INT DEFAULT NULL;
    IF `NEW`.`comment_active` = 0 AND `OLD`.`comment_active` = 1 THEN

        UPDATE `project` `p`
        SET `p`.`count_comments` = (`p`.`count_comments` - 1)
        WHERE `p`.`project_id` = `NEW`.`comment_target_id`;
		        
        SET `NEW`.`comment_deleted_at` = NOW();
        
        set ratingID:= (select rating_id  from project_rating where comment_id = `OLD`.`comment_id`);
        IF(ratingID IS NOT NULL and ratingID>0) THEN
			update project_rating set rating_active = 0 where rating_id = ratingID;
		END IF; 

    END IF;

    IF `NEW`.`comment_active` = 1 AND `OLD`.`comment_active` = 0 THEN

        UPDATE `project` `p`
        SET `p`.`count_comments` = (`p`.`count_comments` + 1)
        WHERE `p`.`project_id` = `NEW`.`comment_target_id`;

        SET `NEW`.`comment_deleted_at` = NULL;
        
        set ratingID:= (select rating_id  from project_rating where comment_id = `OLD`.`comment_id`);
        IF(ratingID IS NOT NULL and ratingID>0) THEN
			update project_rating set rating_active = 1 where rating_id = ratingID;
		END IF; 

    END IF;

END$$
DELIMITER ;