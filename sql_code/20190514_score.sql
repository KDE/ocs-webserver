update project_rating set score = round(((user_like-6)*8+(user_dislike-6)*3+2*5)/(user_like+user_dislike+2-12),2) 
  where user_like >=6 and user_dislike>=6 ;