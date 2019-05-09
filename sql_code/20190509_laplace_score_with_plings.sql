DELIMITER $$
CREATE FUNCTION `laplace_score_with_plings`(upvotes INT, downvotes INT, plings INT) RETURNS int(3)
    DETERMINISTIC
BEGIN
  DECLARE score INT(4);
    SET score = round((upvotes*8+downvotes*3+2*5 + plings*11)/(upvotes+downvotes+2+plings),2)*100 ;
  RETURN score;

END$$
DELIMITER ;
