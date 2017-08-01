USE `pling`;
DROP function IF EXISTS `laplace_score`;

DELIMITER $$
USE `pling`$$
CREATE DEFINER=CURRENT_USER FUNCTION `laplace_score`(upvotes INT, downvotes INT) RETURNS int(11) DETERMINISTIC 
  BEGIN
    DECLARE score INT(10);
    SET score = (round(((upvotes + 6) / ((upvotes + downvotes) + 12)),2) * 100);
    RETURN score;
  END$$

DELIMITER ;