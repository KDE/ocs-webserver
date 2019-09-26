CREATE TABLE `supporter_view_queue` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`section_id` INT NULL DEFAULT '0',
	`member_id` INT NULL DEFAULT '0',
	`count_views` INT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
;


SELECT 
 p.section_id
 ,s.member_id
 ,round(p.tier * 3.03,0) AS factor
 ,0 
FROM section_support_paypements p
JOIN support s ON s.id = p.support_id
WHERE p.yearmonth = 201909
AND p.section_id = 1
AND s.member_id NOT IN (543047,544922,375449 )






DROP PROCEDURE createSupporterQueueList;

DELIMITER $$
CREATE PROCEDURE createSupporterQueueList()
BEGIN
	 DECLARE income INT;
    DECLARE finished INTEGER DEFAULT 0;
    DECLARE v_section_id INTEGER(100) DEFAULT "";
    DECLARE v_member_id INTEGER(100) DEFAULT "";
    DECLARE v_factor INTEGER(100) DEFAULT "";
 
    -- declare cursor for employee email
    DEClARE curSupporter 
        CURSOR FOR 
            SELECT 
				 p.section_id
				 ,s.member_id
				 ,round(p.tier * 3.03,0) AS factor
				FROM section_support_paypements p
				JOIN support s ON s.id = p.support_id
				WHERE p.yearmonth = 201909
				AND s.member_id NOT IN (543047,544922,375449 );
 
    -- declare NOT FOUND handler
    DECLARE CONTINUE HANDLER 
        FOR NOT FOUND SET finished = 1;
        
    TRUNCATE TABLE supporter_view_queue; 
 
    OPEN curSupporter;
 
    getSupporter: LOOP
        FETCH curSupporter INTO v_section_id,v_member_id,v_factor;
        IF finished = 1 THEN 
            LEAVE getSupporter;
        END IF;
        
        
        
        SET income = 0;
 
		   label1: LOOP
		     SET income = income + 1;
		     -- insert supporter
	        INSERT INTO supporter_view_queue (section_id, member_id) VALUES (v_section_id,v_member_id);
		     IF income < v_factor THEN
		       ITERATE label1;
		     END IF;
		     LEAVE label1;
		   END LOOP label1;
        
    END LOOP getSupporter;
    CLOSE curSupporter;
 
END$$
DELIMITER ;

CALL createSupporterQueueList();

SELECT * FROM supporter_view_queue;




