
DELIMITER $$
drop PROCEDURE generate_stat_rating_project;
CREATE  PROCEDURE `generate_stat_rating_project`()
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
        `score_test` int(11) NOT NULL,
        PRIMARY KEY `primary` (project_id)
    )
    AS
    SELECT pr.project_id,
    sum(pr.likes)                                      AS likes,
    sum(pr.dislikes)                                   AS dislikes,
    sum(pr.likes) + sum(pr.dislikes)               AS votes_total,
    laplace_score(sum(pr.likes), sum(pr.dislikes)) AS score,
    (sum(pr.totalscore)+4*5)/(sum(pr.count)+4)*100 AS score_with_pling,
    (sum(pr.totalscore_test)+2*5)/(sum(pr.count)+2)*100 AS score_test
    from
    (
        select project_id
        ,user_like as likes
        ,user_dislike as dislikes
        ,1 as count
        ,score as totalscore
        ,score_test as totalscore_test
        from project_rating pr where pr.rating_active = 1
    ) pr
    group by project_id;

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
