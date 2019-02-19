
-- drop old procedure
drop procedure generate_tmp_cat_tag_proj;

       
DROP TABLE IF EXISTS stat_cat_tree_hierachie;

create table stat_cat_tree_hierachie
( 
  project_category_id int,
    ancestor_id_path varchar(50),
    catid1 int,
    catid2 int,
    catid3 int,
    catid4 int,
    catid5 int,
    catid6 int,    
    created_at timestamp not null default now(),
  PRIMARY KEY (project_category_id),
    INDEX ix_stat_cat_tree_hierachie_1 (catid1),
    INDEX ix_stat_cat_tree_hierachie_2 (catid2),
    INDEX ix_stat_cat_tree_hierachie_3 (catid3),
    INDEX ix_stat_cat_tree_hierachie_4 (catid4),
    INDEX ix_stat_cat_tree_hierachie_5 (catid5),
    INDEX ix_stat_cat_tree_hierachie_6 (catid6)
);

truncate table stat_cat_tree_hierachie;
insert into stat_cat_tree_hierachie 
select 
t.project_category_id,
t.ancestor_id_path,
SPLIT_STRING(t.ancestor_id_path, ',', 1) as catid1, -- root no category tags ignore
SPLIT_STRING(t.ancestor_id_path, ',', 2) as catid2,
SPLIT_STRING(t.ancestor_id_path, ',', 3) as catid3,
SPLIT_STRING(t.ancestor_id_path, ',', 4) as catid4,
SPLIT_STRING(t.ancestor_id_path, ',', 5) as catid5,     
SPLIT_STRING(t.ancestor_id_path, ',', 6) as catid6,
now() as created_at            
from stat_cat_tree t;


DROP TABLE IF EXISTS tmp_project_system_tag;

CREATE TABLE `tmp_project_system_tag` (
  `project_id` INT(11) NOT NULL,
  `project_category_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  `ancestor_id_path` VARCHAR(50) NULL DEFAULT NULL,
  INDEX(`project_id`, `project_category_id`, `tag_id`)
);

drop procedure IF EXISTS  generate_tmp_cat_tag_proj_init;

DELIMITER $$
CREATE PROCEDURE `generate_tmp_cat_tag_proj_init`()
BEGIN

    TRUNCATE table tmp_project_system_tag;  

    truncate table stat_cat_tree_hierachie;
    insert into stat_cat_tree_hierachie 
    select 
    t.project_category_id,
    t.ancestor_id_path,
    SPLIT_STRING(t.ancestor_id_path, ',', 1) as catid1, -- root no category tags ignore
    SPLIT_STRING(t.ancestor_id_path, ',', 2) as catid2,
    SPLIT_STRING(t.ancestor_id_path, ',', 3) as catid3,
    SPLIT_STRING(t.ancestor_id_path, ',', 4) as catid4,
    SPLIT_STRING(t.ancestor_id_path, ',', 5) as catid5,     
    SPLIT_STRING(t.ancestor_id_path, ',', 6) as catid6,
    now() as created_at            
    from stat_cat_tree t;

    INSERT INTO tmp_project_system_tag
    select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p
    join stat_cat_tree_hierachie t on t.project_category_id = p.project_category_id
    join category_tag c on c.category_id = t.catid2    
    WHERE p.`status` = 100  
    ; 

    INSERT INTO tmp_project_system_tag
    select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p
    join stat_cat_tree_hierachie t on t.project_category_id = p.project_category_id
    join category_tag c on c.category_id = t.catid3    
    WHERE p.`status` = 100  
    ; 

    INSERT INTO tmp_project_system_tag
    select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p
    join stat_cat_tree_hierachie t on t.project_category_id = p.project_category_id
    join category_tag c on c.category_id = t.catid4    
    WHERE p.`status` = 100  
    ; 

    INSERT INTO tmp_project_system_tag
    select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p
    join stat_cat_tree_hierachie t on t.project_category_id = p.project_category_id
    join category_tag c on c.category_id = t.catid5    
    WHERE p.`status` = 100  
    ;

    INSERT INTO tmp_project_system_tag
    select p.project_id, p.project_category_id, c.tag_id, t.ancestor_id_path from project p
    join stat_cat_tree_hierachie t on t.project_category_id = p.project_category_id
    join category_tag c on c.category_id = t.catid6    
    WHERE p.`status` = 100  
    ;  
  
    
    DROP TABLE IF EXISTS tmp_tag_object_to_delete;
    CREATE TEMPORARY TABLE tmp_tag_object_to_delete    
    (PRIMARY KEY `primary` (tag_item_id))
      ENGINE MyISAM
      AS
        SELECT 
        o.tag_item_id
    FROM 
        tag_object o
        LEFT JOIN tmp_project_system_tag t on t.project_id = o.tag_object_id and t.tag_id = o.tag_id 
    WHERE 
        o.tag_group_id = 6 and o.is_deleted = 0 and t.project_id is null
    ;
    
    /*DELETE SYSTEM TAGS -- 12155 TO DELETE*/

    update tag_object  set is_deleted = 1 , tag_changed = now()
    where tag_item_id in
    (
      SELECT 
        o.tag_item_id
      FROM 
        tmp_tag_object_to_delete o
    );
   




    DROP TABLE IF EXISTS tmp_tag_object_to_insert;
    CREATE TEMPORARY TABLE tmp_tag_object_to_insert    
    /*(INDEX (project_id,project_category_id,tag_id))*/
      ENGINE MyISAM
      AS
        SELECT 
        t.*
      FROM 
        tmp_project_system_tag t
        LEFT JOIN tag_object o on t.project_id = o.tag_object_id and t.tag_id = o.tag_id and o.tag_group_id = 6
      WHERE 
        o.tag_item_id is null
    ;
    

    INSERT INTO tag_object
    SELECT null AS tag_item_id, p.tag_id, 1 AS tag_type_id, 6 AS tag_group_id,p.project_id AS tag_object_id,null as tag_parenet_object_id,NOW() AS tag_created, null AS tag_changed, 0 as is_deleted
    FROM (
      select DISTINCT * from tmp_tag_object_to_insert
    ) p;
    
     
END;
$$
DELIMITER ;
