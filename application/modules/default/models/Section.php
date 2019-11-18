<?php
/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Created: 13.09.2017
 */

class Default_Model_Section
{

    /**
     * @inheritDoc
     */
    public function __construct()
    {

    }

    public function fetchSponsorHierarchy()
    {
        $sql = "
            SELECT section.name AS section_name, sponsor.sponsor_id,sponsor.name AS sponsor_name
            FROM section_sponsor
            JOIN sponsor ON sponsor.sponsor_id = section_sponsor.sponsor_id
            JOIN section ON section.section_id = section_sponsor.section_id

        ";
        $resultSet = $this->getAdapter()->fetchAll($sql);
        $optgroup = array();
        foreach ($resultSet as $item) {
            $optgroup[$item['section_name']][$item['sponsor_id']] = $item['sponsor_name'];
        }

        return $optgroup;
    }
    
    public function fetchAllSections()
    {
        $sql = "
            SELECT section_id,name,description
            FROM section
            WHERE is_active = 1
            and hide = 0
            ORDER BY section.order
        ";
        $resultSet = $this->getAdapter()->fetchAll($sql);

        return $resultSet;
    }
    
    public function fetchAllSectionsAndCategories()
    {
        $sql = "
            SELECT 
            s.section_id
            ,s.name
            ,s.description
            ,c.project_category_id
            ,pc.title
            FROM section s
            JOIN section_category c on s.section_id = c.section_id
            join project_category pc on c.project_category_id = pc.project_category_id and pc.is_deleted = 0 and pc.is_active = 1 and pc.rgt=pc.lft+1
            WHERE s.is_active = 1
            order by s.name , pc.title
        ";
        $resultSet = $this->getAdapter()->fetchAll($sql);
        return $resultSet;   
    }

    public function fetchCategoriesWithPayout()
    {
        $sql = " select m.section_id,m.project_category_id,SUM(m.credits_section)/100 amount, pc.title
                from micro_payout m
                join project_category pc on m.project_category_id = pc.project_category_id 
                where m.paypal_mail is not null and m.paypal_mail <> '' and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$')            
                and m.yearmonth = DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')  and m.is_license_missing = 0 and m.is_source_missing=0 and m.is_pling_excluded = 0 
                and m.is_member_pling_excluded=0
                group by project_category_id
                ";
         $resultSet = $this->getAdapter()->fetchAll($sql);
        return $resultSet;    
    }
    public function fetchCategoriesWithPlinged()
    {
        $sql = " select
                p.project_category_id, m.section_id,pc.title
                from project_plings pl
                inner join stat_projects p on pl.project_id = p.project_id and p.status = 100
                inner join section_category m on p.project_category_id = m.project_category_id
                inner join project_category pc on m.project_category_id = pc.project_category_id
                inner join (
                    select distinct su2.member_id
                    from section_support_paypements ss
                    JOIN support su2 ON su2.id = ss.support_id
                    where yearmonth = DATE_FORMAT(NOW()- INTERVAL 1 MONTH,'%Y%m')
                ) ss on pl.member_id = ss.member_id
                where pl.is_deleted = 0 and pl.is_active = 1
                group by p.project_category_id
                ";
         $resultSet = $this->getAdapter()->fetchAll($sql);
        return $resultSet;    
    }

    public function getNewActivePlingProduct($section_id=null)
    {
        if($section_id)
        {
            $sqlSection = " and m.section_id = ".$section_id;
        }else
        {
            $sqlSection = " ";
        }
        $sql = "
                select 
                pl.member_id as pling_member_id
                ,pl.project_id                        
                ,p.title
                ,p.image_small
                ,p.laplace_score
                ,p.count_likes
                ,p.count_dislikes   
                ,p.member_id 
                ,p.profile_image_url
                ,p.username
                ,p.cat_title as catTitle
                ,(
                select max(created_at) from project_plings pt where pt.member_id = pl.member_id and pt.project_id=pl.project_id
                ) as created_at
                ,(select count(1) from project_plings pl2 where pl2.project_id = p.project_id and pl2.is_active = 1 and pl2.is_deleted = 0  ) as sum_plings
                from project_plings pl
                inner join stat_projects p on pl.project_id = p.project_id and p.status=100  
                inner join section_category m on p.project_category_id = m.project_category_id                 
                where pl.is_deleted = 0 and pl.is_active = 1 " .$sqlSection."
                order by created_at desc   
                limit 20
        ";
        $resultSet = $this->getAdapter()->fetchAll($sql);
        return $resultSet;    
    }

    public function fetchTopProductsPerSection($section_id=null)
    {
        if($section_id)
        {
            $sqlSection = " and m.section_id = ".$section_id;
        }else
        {
            $sqlSection = " ";
        }
        $sql = "
            select 
            p.project_id,
            p.member_id,
            p.project_category_id,
            p.title,
            p.description,
            p.created_at,
            p.changed_at,
            p.image_small,
            p.username,
            p.profile_image_url,
            p.cat_title,
            p.laplace_score,
            sum(m.credits_plings)/100 AS probably_payout_amount
            from stat_projects p,micro_payout m
            where p.project_id = m.project_id
            and m.paypal_mail is not null and m.paypal_mail <> '' and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
            ".$sqlSection."
            and m.yearmonth = DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')  and m.is_license_missing = 0 and m.is_source_missing=0 and m.is_pling_excluded = 0 
            and m.is_member_pling_excluded=0
            GROUP BY m.project_id
            order by sum(m.credits_plings) desc
            limit 20
        ";
       
        $resultSet = $this->getAdapter()->fetchAll($sql);
        return $resultSet;    
    }



    public function fetchTopPlingedProductsPerSection($section_id=null)
    {
        if($section_id)
        {
            $sqlSection = " and m.section_id = ".$section_id;
        }else
        {
            $sqlSection = " ";
        }

        // ignore if supporter still active 
        // inner join (
        //     select distinct su2.member_id
        //     from section_support_paypements ss
        //     JOIN support su2 ON su2.id = ss.support_id
        //     where yearmonth = DATE_FORMAT(NOW()- INTERVAL 1 MONTH,'%Y%m')
        // ) ss on pl.member_id = ss.member_id

        $sql = "
                select pl.project_id
                ,count(1) as sum_plings 
                ,(select count(1) from project_plings pls where pls.project_id=pl.project_id and pls.is_deleted=0) as sum_plings_all
                ,p.title
                ,p.image_small
                ,p.laplace_score
                ,p.count_likes
                ,p.count_dislikes   
                ,p.member_id 
                ,p.profile_image_url
                ,p.username
                ,p.cat_title as catTitle
                ,p.project_changed_at
                ,p.version
                ,p.description
                ,p.package_names
                ,p.count_comments
                ,p.changed_at
                ,p.created_at
                from project_plings pl
                inner join stat_projects p on pl.project_id = p.project_id and p.status = 100
                inner join section_category m on p.project_category_id = m.project_category_id
                
                where pl.is_deleted = 0 and pl.is_active = 1" .$sqlSection."
                group by pl.project_id
                order by sum_plings desc ,sum_plings_all desc
                limit 20        
        ";
       
        $resultSet = $this->getAdapter()->fetchAll($sql);
        return $resultSet;    
    }

    /**
     *  ignore if supporter still active 
     * inner join (
                    select distinct su2.member_id
                    from section_support_paypements ss
                    JOIN support su2 ON su2.id = ss.support_id
                    where yearmonth = DATE_FORMAT(NOW()- INTERVAL 1 MONTH,'%Y%m')
                ) ss on pl.member_id = ss.member_id
     */
    public function fetchTopPlingedProductsPerCategory($cat_id)
    {
        $sql = "select pl.project_id
                ,count(1) as sum_plings 
                ,(select count(1) from project_plings pls where pls.project_id=pl.project_id and pls.is_deleted=0) as sum_plings_all
                ,p.title
                ,p.image_small
                ,p.laplace_score
                ,p.count_likes
                ,p.count_dislikes   
                ,p.member_id 
                ,p.profile_image_url
                ,p.username
                ,p.cat_title as catTitle
                ,p.project_changed_at
                ,p.version
                ,p.description
                ,p.package_names
                ,p.count_comments
                ,p.changed_at
                ,p.created_at
                from project_plings pl
                inner join stat_projects p on pl.project_id = p.project_id and p.status = 100                                
                where pl.is_deleted = 0 and pl.is_active = 1 and p.project_category_id=:cat_id
                group by pl.project_id
                order by sum_plings desc 
                limit 20     ";
        $resultSet = $this->getAdapter()->fetchAll($sql, array("cat_id"=>$cat_id));
        return $resultSet;    
    }


    public function fetchTopProductsPerCategory($cat_id)
    {
        $sql = "select 
                p.project_id,
                p.member_id,
                p.project_category_id,
                p.title,
                p.description,
                p.created_at,
                p.changed_at,
                p.image_small,
                p.username,
                p.profile_image_url,
                p.cat_title,
                p.laplace_score,
                sum(m.credits_plings)/100 AS probably_payout_amount
                from stat_projects p,micro_payout m
                where  p.project_id = m.project_id
                     and m.paypal_mail is not null and m.paypal_mail <> ''
                     and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
                      and m.yearmonth = DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')  and m.is_license_missing = 0 and m.is_source_missing=0 and m.is_pling_excluded = 0 
                        and m.is_member_pling_excluded=0           
                            and p.project_category_id = :cat_id 
					 GROUP BY m.project_id   
                order by sum(m.credits_plings) desc 
                limit 20";
        $resultSet = $this->getAdapter()->fetchAll($sql, array("cat_id"=>$cat_id));
        return $resultSet;    
    }

    
    public function fetchProbablyPayoutLastMonth($section_id)
    {
        if($section_id)
        {
            $sqlSection = " and s.section_id = ".$section_id;
        }else
        {
            $sqlSection = " ";
        }
        /*
        $sql = "select  sum(probably_payout_amount) probably_payout_amount
            from member_dl_plings m, section s, section_category c
            where  s.section_id = c.section_id and c.project_category_id = m.project_category_id
           ".$sqlSection."
            and m.paypal_mail is not null and m.paypal_mail <> ''
            and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
            and m.yearmonth = DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')  and m.is_license_missing = 0 and m.is_source_missing=0 and m.is_pling_excluded = 0 
            and m.is_member_pling_excluded=0
            ";
        */
        $sql = "SELECT s.sum_amount_payout AS probably_payout_amount FROM section_funding_stats s
                WHERE s.yearmonth = DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
               ".$sqlSection."
                ";
        $resultSet = $this->getAdapter()->fetchRow($sql);
        
        return $resultSet['probably_payout_amount'];

    }

    public function fetchTopPlingedCreatorPerSection($section_id=null)
    {
        if($section_id)
        {
            $sqlSection = " and mm.section_id = ".$section_id;
        }else
        {
            $sqlSection = " ";
        }

        /**
         * inner join (
			select distinct su2.member_id
			from section_support_paypements ss
			JOIN support su2 ON su2.id = ss.support_id
			where yearmonth = DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m')
		) ss on pl.member_id = ss.member_id
         */
        $sql = "select p.member_id,
        count(1) as cnt,
        (select count(1) from project_plings pls , stat_projects ppp where pls.project_id=ppp.project_id and pls.is_deleted=0 and ppp.member_id=p.member_id) as sum_plings_all,
        m.username,
        m.profile_image_url,
        m.created_at
        from stat_projects p
        join project_plings pl on p.project_id = pl.project_id
        join member m on p.member_id = m.member_id
        inner join section_category mm on p.project_category_id = mm.project_category_id
        
        where p.status = 100
        and pl.is_deleted = 0 and pl.is_active = 1 ".$sqlSection."  
        group by p.member_id
        order by cnt desc 
        limit 20
        ";
        $resultSet = $this->getAdapter()->fetchAll($sql);
        return $resultSet;    
    }

    public function fetchTopCreatorPerSection($section_id=null)
    {
        if($section_id)
        {
            $sqlSection = " and s.section_id = ".$section_id;
        }else
        {
            $sqlSection = " ";
        }
        $sql = "
            select                 
                me.username,
                me.profile_image_url,
                m.member_id,
                sum(m.credits_plings)/100 probably_payout_amount
                from micro_payout m, section s, section_category c, member me
                where s.section_id = c.section_id and c.project_category_id = m.project_category_id AND me.member_id = m.member_id
                and m.paypal_mail is not null and m.paypal_mail <> ''
                and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
                ".$sqlSection."  
                and m.yearmonth =  DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m') 
					 and m.is_license_missing = 0 and m.is_source_missing=0 and m.is_pling_excluded = 0 
                and m.is_member_pling_excluded=0
                group by me.username,me.profile_image_url,m.member_id
                order by sum(m.credits_plings) desc
                limit 20
        ";
        
        $resultSet = $this->getAdapter()->fetchAll($sql);
        return $resultSet;    
    }

    public function fetchTopPlingedCreatorPerCategory($cat_id)
    {
        
        $sql = "select p.member_id,
                    count(1) as cnt,
                    (select count(1) from project_plings pls , stat_projects ppp where pls.project_id=ppp.project_id and pls.is_deleted=0 and ppp.member_id=p.member_id) as sum_plings_all,
                    m.username,
                    m.profile_image_url,
                    m.created_at
                    from stat_projects p
                    join project_plings pl on p.project_id = pl.project_id
                    join member m on p.member_id = m.member_id                   
                    inner join (
                        select distinct su2.member_id
                        from section_support_paypements ss
                        JOIN support su2 ON su2.id = ss.support_id
                        where yearmonth = DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m')
                    ) ss on pl.member_id = ss.member_id
                    where p.status = 100 and p.project_category_id=:cat_id
                    and pl.is_deleted = 0 and pl.is_active = 1  
                    group by p.member_id
                    order by cnt desc 
                    limit 20";
        $resultSet = $this->getAdapter()->fetchAll($sql,array("cat_id"=>$cat_id));
        return $resultSet;    
    }

     public function fetchTopCreatorPerCategory($cat_id)
    {
        
        $sql = "select              
                p.username,
                p.profile_image_url,
                p.member_id,
                SUM(m.credits_plings)/100 probably_payout_amount
                from stat_projects p, micro_payout m
                where p.member_id = m.member_id and p.project_id = m.project_id
                and m.paypal_mail is not null and m.paypal_mail <> ''
                and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
                 and m.yearmonth =  DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m') and m.is_license_missing = 0 and m.is_source_missing=0 and m.is_pling_excluded = 0 
                and m.is_member_pling_excluded=0
                and p.project_category_id = :cat_id
                group by p.username,p.profile_image_url,p.member_id
                order by sum(m.credits_plings) desc 
                limit 20";
        $resultSet = $this->getAdapter()->fetchAll($sql,array("cat_id"=>$cat_id));
        return $resultSet;    
    }

    public function fetchFirstSectionForStoreCategories($category_array)
    {
        $sql = "
            SELECT *
            FROM section
            JOIN section_category on section_category.section_id = section.section_id
            WHERE is_active = 1
            AND section_category.project_category_id in (:category_id)
            LIMIT 1
        ";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('category_id' => $category_array));

        return $resultSet;
    }
    
    public function fetchSectionForCategory($category_id)
    {
        $sql = "
            SELECT *
            FROM section
            JOIN section_category on section_category.section_id = section.section_id
            WHERE is_active = 1
            AND section_category.project_category_id = :category_id
            LIMIT 1
        ";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('category_id' => $category_id));

        return $resultSet;
    }
    
    public function isMemberSectionSupporter($section_id, $member_id) {
        $sql = "
            SELECT *
            FROM section_support 
            JOIN section ON section.section_id = section_support.section_id
            JOIN support ON support.id = section_support.support_id AND support.status_id = 2
            WHERE section_support.is_active = 1
            AND section.section_id = :section_id
            AND support.member_id = :member_id
            LIMIT 1
        ";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('section_id' => $section_id, 'member_id' => $member_id));
        
        if($resultSet) {
            return true;
        }
        return false;
    }
    
    
    public function wasMemberSectionSupporter($section_id, $member_id) {
        $sql = "
            SELECT *
            FROM section_support 
            JOIN section ON section.section_id = section_support.section_id
            JOIN support ON support.id = section_support.support_id AND support.status_id >= 2
            WHERE section_support.is_active = 1
            AND section.section_id = :section_id
            AND support.member_id = :member_id
            LIMIT 1
        ";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('section_id' => $section_id, 'member_id' => $member_id));
        
        if($resultSet) {
            return true;
        }
        return false;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    private function getAdapter()
    {
        return Zend_Db_Table::getDefaultAdapter();
    }

    /**
     * @param int $yearmonth
     *
     * @return array
     */
    public function fetchAllSectionStats($yearmonth = null, $isForAdmin = false)
    {
        $sql = "SELECT * FROM section_funding_stats p
                WHERE p.yearmonth = :yearmonth";
        
        if(!$isForAdmin) {
            $sql .= " AND p.yearmonth >= DATE_FORMAT((NOW() - INTERVAL 1 MONTH),'%Y%m')";
        }
        
        if(empty($yearmonth)) {
            $yearmonth = "DATE_FORMAT(NOW(),'%Y%m')";
        }
        $resultSet = $this->getAdapter()->fetchAll($sql, array('yearmonth' => $yearmonth));

        return $resultSet;
    }
    
    
    /**
     * @param int $yearmonth
     *
     * @return array
     */
    public function fetchSectionStats($yearmonth = null, $section_id, $isForAdmin = false)
    {
        $sql = "SELECT * FROM section_funding_stats p
                WHERE p.yearmonth = :yearmonth
                AND p.section_id = :section_id";
        
        if(!$isForAdmin) {
            $sql .= " AND p.yearmonth >= DATE_FORMAT((NOW()),'%Y%m')";
        }
        
        if(empty($yearmonth)) {
            $yearmonth = "DATE_FORMAT(NOW(),'%Y%m')";
        }
        $resultSet = $this->getAdapter()->fetchRow($sql, array('yearmonth' => $yearmonth, 'section_id' => $section_id));

        return $resultSet;
    }
    
    /**
     * @param int $yearmonth
     *
     * @return array
     */
    public function fetchSectionStatsLastMonth($section_id)
    {
        $sql = "SELECT * FROM section_funding_stats p
                WHERE p.yearmonth = DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y%m')
                AND p.section_id = :section_id";
        
        $resultSet = $this->getAdapter()->fetchRow($sql, array('section_id' => $section_id));

        return $resultSet;
    }
    
    
    /**
     * @param int $yearmonth
     *
     * @return array
     */
    public function fetchSectionSupportStats($yearmonth = null, $section_id, $isForAdmin = false)
    {
        $sql = "SELECT p.yearmonth, p.section_id, SUM(p.tier) AS sum_support, null AS sum_sponsor, null AS sum_dls, null AS sum_dls_payout, null AS sum_amount_payout, null AS sum_amount
					,(SELECT COUNT(1) AS num_supporter FROM (
						 	SELECT COUNT(1) AS num_supporter,ss.section_id, su2.member_id FROM section_support_paypements ss
	                    JOIN support su2 ON su2.id = ss.support_id
	                    WHERE ss.yearmonth = :yearmonth
	                    GROUP BY ss.section_id, su2.member_id
                    ) A
                    WHERE A.section_id = p.section_id
                ) AS num_supporter FROM section_support_paypements p
					WHERE p.yearmonth = :yearmonth
					AND p.section_id = :section_id ";
        
        if(!$isForAdmin) {
            $sql .= " AND p.yearmonth >= DATE_FORMAT((NOW() - INTERVAL 1 MONTH),'%Y%m')";
        }
        
        $sql .= " GROUP BY p.yearmonth, p.section_id";
        if(empty($yearmonth)) {
            $yearmonth = "DATE_FORMAT(NOW(),'%Y%m')";
        }
        $resultSet = $this->getAdapter()->fetchRow($sql, array('yearmonth' => $yearmonth, 'section_id' => $section_id));

        return $resultSet;
    }
    
    
    public function fetchSection($section_id)
    {
        $sql = "
            SELECT *
            FROM section
            WHERE is_active = 1 and section_id = :section_id
        ";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('section_id' => $section_id));

        return $resultSet;
    }
    
    
    public function getAllDownloadYears($isForAdmin = false)
    {
        $sql = "
                SELECT 
                    SUBSTR(`member_dl_plings`.`yearmonth`,1,4) as year,
                    MAX(`member_dl_plings`.`yearmonth`) as max_yearmonth
                FROM
                    `member_dl_plings`";
        if(!$isForAdmin) {
            $sql .= " WHERE SUBSTR(`member_dl_plings`.`yearmonth`,1,4) = DATE_FORMAT(NOW(),'%Y')";
        }
        
        $sql .= " GROUP BY SUBSTR(`member_dl_plings`.`yearmonth`,1,4)
                ORDER BY SUBSTR(`member_dl_plings`.`yearmonth`,1,4) DESC
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql);

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }
    
    
    public function getAllDownloadMonths($year, $isForAdmin = false)
    {
        $sql = "
                SELECT 
                    DISTINCT `member_dl_plings`.`yearmonth`
                FROM
                    `member_dl_plings`
                WHERE
                SUBSTR(`member_dl_plings`.`yearmonth`,1,4) = :year ";
                
        if(!$isForAdmin) {
            $sql .= " AND `member_dl_plings`.`yearmonth` >= DATE_FORMAT((NOW() - INTERVAL 1 MONTH),'%Y%m')";
        }

        $sql .= " ORDER BY `member_dl_plings`.`yearmonth` DESC";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('year' => $year));

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }
    
}