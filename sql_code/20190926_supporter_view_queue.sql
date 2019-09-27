CREATE VIEW v_supporter_view_queue
AS 
SELECT 
 p.section_id
 ,s.member_id
 ,sum(round(p.tier * 3.03,0)) AS weight
FROM section_support_paypements p
JOIN support s ON s.id = p.support_id
WHERE p.yearmonth = 201909
AND s.member_id NOT IN (543047,544922,375449,544922,544923,544924 )
GROUP BY p.section_id,s.member_id
;