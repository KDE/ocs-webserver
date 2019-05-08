ALTER TABLE project_rating
	ADD COLUMN score int(2) COMMENT 'vote up = 8 votedown = 3' AFTER user_dislike;
	
select * from project_rating where user_like >1;

update project_rating set score = 8 where user_like = 1 ;
update project_rating set score = 3 where user_dislike = 1 ;
update project_rating set score = round((user_like*8+user_dislike*3+2*5)/(user_like+user_dislike+2),0) where user_like > 1 ;
