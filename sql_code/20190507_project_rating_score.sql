ALTER TABLE project_rating
	ADD COLUMN score int(2) COMMENT 'vote up = 8 votedown = 3' AFTER user_dislike;
	
select * from project_rating where user_like >1;

update project_rating set score = 8 where user_like = 1 ;
update project_rating set score = 3 where user_dislike = 1 ;
update project_rating set score = round((laplace_score(user_like, user_dislike)+4)/10 ,0) where user_like > 1 ;
