DELIMITER $$
CREATE PROCEDURE `generate_stat_rating_project`()
BEGIN
    DROP TABLE IF EXISTS tmp_stat_rating_project;
    CREATE TABLE tmp_stat_rating_project
    (
        `project_id`  int(11) NOT NULL,
        `likes`       int(11) NOT NULL,
        `dislikes`    int(11) NOT NULL,
        `votes_total` int(11) NOT NULL,
        `score`       int(11) NOT NULL,
        `score_with_pling` int(11) NOT NULL,
        PRIMARY KEY `primary` (project_id)
    )
    AS
    SELECT pr.project_id,    
  sum(pr.user_like)                                      AS likes,
  sum(pr.user_dislike)                                   AS dislikes,
  sum(pr.user_like) + sum(pr.user_dislike)               AS votes_total,
  laplace_score(sum(pr.user_like), sum(pr.user_dislike)) AS score,    
    laplace_score_with_plings(sum(pr.user_like), sum(pr.user_dislike)
    ,(select count(1) from project_plings p where p.project_id = pr.project_id and is_deleted = 0)
    ) AS score_with_pling
    FROM project_rating AS pr
  WHERE (pr.rating_active = 1 or (rating_active=0 and user_like>1))
  GROUP BY pr.project_id;

    IF EXISTS(SELECT table_name
              FROM INFORMATION_SCHEMA.TABLES
              WHERE table_schema = DATABASE()
                AND table_name = 'stat_rating_project')
    THEN
        RENAME TABLE stat_rating_project TO old_stat_rating_project, tmp_stat_rating_project TO stat_rating_project;

    ELSE
        RENAME TABLE tmp_stat_rating_project TO stat_rating_project;

    END IF;


    DROP TABLE IF EXISTS old_stat_rating_project;
END$$
DELIMITER ;