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
 **/
class Statistics_Model_GoalStatistics
{

    const MYSQL_DATE_FORMAT = "Y-m-d H:i:s";
    const DEFAULT_RANKING_PLUGIN = 'Statistics_Ranking_WeightedAverageRanking';

    /** @var Statistics_Ranking_RankingInterface */
    protected $_rankingPlugin;


    /**
     * @param Statistics_Ranking_RankingInterface $rankingPlugin
     */
    function __construct(Statistics_Ranking_RankingInterface $rankingPlugin = null)
    {
        if (is_null($rankingPlugin)) {
            $default = self::DEFAULT_RANKING_PLUGIN;
            $this->_rankingPlugin = new $default;
            return $this;
        }
        $this->_rankingPlugin = $rankingPlugin;
    }

    public function setupDatabase()
    {
        exit(0);

        $sql = "
                CREATE TABLE `stat_daily` (
                  `daily_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `project_id` INT(11) NOT NULL COMMENT 'ID of the project',
                  `project_category_id` INT(11) DEFAULT '0' COMMENT 'Category',
                  `project_type` INT(11) NOT NULL COMMENT 'type of the project',
                  `count_views` INT(11) DEFAULT '0',
                  `count_plings` INT(11) DEFAULT '0',
                  `count_updates` INT(11) DEFAULT NULL,
                  `count_comments` INT(11) DEFAULT NULL,
                  `count_followers` INT(11) DEFAULT NULL,
                  `count_supporters` INT(11) DEFAULT NULL,
                  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                  `year` INT(11) DEFAULT NULL COMMENT 'z.B.: 1988',
                  `month` INT(11) DEFAULT NULL COMMENT 'z.b: 1-12',
                  `day` INT(11) DEFAULT NULL COMMENT 'z.B. 1-31',
                  `year_week` INT(11) DEFAULT NULL COMMENT 'z.b.: 201232',
                  PRIMARY KEY (`daily_id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='Store daily statistic';

                CREATE TABLE `app_config` (
                  `config_id` INT(11) NOT NULL AUTO_INCREMENT,
                  `group` VARCHAR(20) NOT NULL,
                  `name` VARCHAR(20) NOT NULL,
                  `value` VARCHAR(20) NOT NULL,
                  PRIMARY KEY (`config_id`),
                  KEY `index_group` (`group`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Store config for statistic';
               ";

        $sql_alter = "
        ALTER TABLE pling.stat_daily
          ADD ranking_value INT AFTER year_week;
        ALTER TABLE pling.stat_daily
          ADD count_money FLOAT AFTER count_supporters;
        ALTER TABLE pling.stat_daily
         CHANGE ranking_value ranking_value FLOAT;
        ALTER TABLE pling.stat_daily
         CHANGE project_type project_type_id INT(11) NOT NULL COMMENT 'type of the project';
           ";

        $database = Zend_Db_Table::getDefaultAdapter();
        $database->query($sql)->execute();
        $database->query($sql_alter)->execute();
    }

    public function dailyPageviews()
    {
       $sql = '
               insert into stat_daily_pageviews 
               select project_id
               ,count(project_id) cnt
               ,(select p.project_category_id from project p where v.project_id = p.project_id) project_category_id
               ,CURDATE() created_at
               from stat_page_views v where v.created_at > date_sub(CURDATE(), interval 6 month)
               group by project_id
       ';
       $database = Zend_Db_Table::getDefaultAdapter();
       $database->query($sql)->execute();      
    }

    public function migrateStatistics()
    {
        $database = Zend_Db_Table::getDefaultAdapter();

        $sqlTruncate = "TRUNCATE stat_daily";
        $result = $database->query($sqlTruncate);


        $sql = "
                SELECT DATE_FORMAT(stat_page_views.created_at,\"%d.%m.%Y\") AS stat_date
                  FROM pling.stat_page_views stat_page_views
                GROUP BY stat_date
                ORDER BY created_at ASC;
        ";

        $queryObject = $database->query($sql);
        $resultSet = $queryObject->fetchAll();

        $addedRows = 0;
        $resultMessage = new stdClass();

        if (count($resultSet) > 0) {
            foreach ($resultSet as $element) {
                $resultMessage = $this->generateDailyStatistics($element['stat_date']);
                if ($resultMessage->result === false) {
                    break;
                }
                $addedRows += $resultMessage->AffectedRows;
            }

        }

        $resultMessage->AffectedRows = $addedRows;
        $resultMessage->Environment = APPLICATION_ENV;

        return $resultMessage;
    }

    /**
     * @param string|null $forDate
     * @return stdClass
     */
    public function generateDailyStatistics($forDate = null)
    {
        $database = Zend_Db_Table::getDefaultAdapter();

        $dateCreatedAt = new DateTime();
        $mysqlCreatedAt = $dateCreatedAt->format(self::MYSQL_DATE_FORMAT);

        if (is_null($forDate)) {
            $generateForDateObject = new DateTime();
        } else {
            $generateForDateObject = new DateTime($forDate);
        }

        $generateForDateObject->setTime(0, 0, 0);
        $mysqlStartDate = $generateForDateObject->format(self::MYSQL_DATE_FORMAT);

        $generateForDateObject->setTime(23, 59, 59);
        $mysqlEndDate = $generateForDateObject->format(self::MYSQL_DATE_FORMAT);

        $sql = "
                  select
                      prj.project_id,
                      prj.type_id as project_type_id,
                      prj.project_category_id,
                      (select count(1) from stat_page_views pv where pv.project_id = prj.project_id and pv.created_at between '{$mysqlStartDate}' and '{$mysqlEndDate}' group by pv.project_id) AS count_views,
                      (select count(1) from plings p where p.project_id = prj.project_id and p.pling_time between '{$mysqlStartDate}' and '{$mysqlEndDate}' AND p.status_id in (2,3,4) group by p.project_id) AS count_plings,
                      (select count(1) from project pu where pu.pid = prj.project_id and (pu.created_at between '{$mysqlStartDate}' and '{$mysqlEndDate}') and pu.type_id = 2 group by pu.pid) AS count_updates,
                      (select count(1) from project_follower pf where pf.project_id = prj.project_id) AS count_followers,
                      (SELECT count(1) FROM plings WHERE status_id >= 2 AND create_time BETWEEN '{$mysqlStartDate}' AND '{$mysqlEndDate}' AND project_id = prj.project_id AND comment is not null GROUP BY project_id) AS count_comments,
                      (SELECT count(member_id) FROM (SELECT member_id, project_id FROM plings WHERE status_id >= 2 AND create_time BETWEEN '{$mysqlStartDate}' AND '{$mysqlEndDate}' GROUP BY member_id, project_id) AS tempCountProjectSupporter WHERE project_id = prj.project_id) AS count_supporters,
                      (SELECT sum(amount) FROM plings WHERE status_id >= 2 AND create_time BETWEEN '{$mysqlStartDate}' AND '{$mysqlEndDate}' AND project_id = prj.project_id GROUP BY project_id) AS count_money,
                      '" . $mysqlCreatedAt . "' AS created_at,
                      DATE_FORMAT('{$mysqlStartDate}', '%Y') AS year,
                      DATE_FORMAT('{$mysqlStartDate}', '%m') AS month,
                      DATE_FORMAT('{$mysqlStartDate}', '%d') AS day,
                      YEARWEEK('{$mysqlStartDate}',1) AS year_week
                  from
                      project as prj
                  where
                      prj.status = " . Default_Model_DbTable_Project::PROJECT_ACTIVE . "
                      and prj.type_id = " . Default_Model_DbTable_Project::PROJECT_TYPE_STANDARD . "
                  group by prj.project_id;
               ";

        $statement = $database->query($sql);

        $statTable = new Statistics_Model_DbTable_StatDaily();

        while ($row = $statement->fetch(Zend_Db::FETCH_ASSOC, Zend_Db::FETCH_ORI_NEXT)) {
            $row['ranking_value'] = $this->_rankingPlugin->calculateRankingValue($row);
            $statTable->save($row);
        }

        $resultMessage = new stdClass();
        $resultMessage->result = $statement->errorCode() == '00000' ? true : false;
        $resultMessage->errorMessage = implode(' ', $statement->errorInfo());
        $resultMessage->errorCode = $statement->errorCode();
        $resultMessage->AffectedRows = $statement->rowCount();

        return $resultMessage;

    }

    /**
     * @param string $identifier
     * @param int $year
     * @param int $month
     * @param int $day
     * @return array
     */
    public function getDailyStatistics($identifier, $year, $month, $day)
    {
        $sql = "
                SELECT sd.count_views AS views, sd.count_plings AS plings, sd.count_updates AS updates, sd.count_comments AS comments, sd.count_followers AS followers, sd.count_supporters AS supporters
                FROM stat_daily AS sd
                WHERE
                    sd.project_id = ?
                    AND sd.year = ?
                    AND sd.month = ?
                    AND sd.day = ?;
              ";
        $database = Zend_Db_Table::getDefaultAdapter();
        $sql = $database->quoteInto($sql, $identifier, 'INTEGER', 1);
        $sql = $database->quoteInto($sql, $year, 'INTEGER', 1);
        $sql = $database->quoteInto($sql, $month, 'INTEGER', 1);
        $sql = $database->quoteInto($sql, $day, 'INTEGER', 1);

        $resultSet = $database->query($sql)->fetchAll();

        return $resultSet;
    }

    /**
     * @param string $identifier
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getMonthlyStatistics($identifier, $year, $month)
    {
        $sql = "
                SELECT sd.year_week, sum(sd.count_views) AS views, sum(sd.count_plings) AS plings, sum(sd.count_updates) AS updates, sum(sd.count_comments) AS comments, avg(sd.count_followers) AS followers, avg(sd.count_supporters) AS supporters
                FROM stat_daily AS sd
                WHERE
                    sd.project_id = ?
                    AND sd.year = ?
                    AND sd.month = ?
                GROUP BY sd.year_week
                ORDER BY sd.day ASC;
        ";
        $database = Zend_Db_Table::getDefaultAdapter();
        $sql = $database->quoteInto($sql, $identifier, 'INTEGER', 1);
        $sql = $database->quoteInto($sql, $year, 'INTEGER', 1);
        $sql = $database->quoteInto($sql, $month, 'INTEGER', 1);

        $resultSet = $database->query($sql)->fetchAll();

        return $resultSet;

    }

    /**
     * @param string $identifier
     * @param int $yearWeek
     * @return array
     */
    public function getWeeklyStatistics($identifier, $yearWeek)
    {
        $sql = "
                SELECT sd.day, sd.count_views AS views, sd.count_plings AS plings, sd.count_updates AS updates, sd.count_comments AS comments, sd.count_followers AS followers, sd.count_supporters AS supporters
                FROM stat_daily AS sd
                WHERE
                    sd.project_id = ?
                    AND sd.year_week = ?
                ORDER BY sd.day ASC;
        ";
        $database = Zend_Db_Table::getDefaultAdapter();
        $sql = $database->quoteInto($sql, $identifier, 'INTEGER', 1);
        $sql = $database->quoteInto($sql, $yearWeek, 'INTEGER', 1);

        $resultSet = $database->query($sql)->fetchAll();

        return $resultSet;

    }

    /**
     * @param DateTime $forDate
     * @throws Exception
     */
    protected function generateRanking(DateTime $forDate)
    {
        throw new Exception('this code is outdated');

        $statisticsTable = new Statistics_Model_DbTable_StatDaily();

        $statement = $statisticsTable->select();
        $statement->setIntegrityCheck(false)->where('year = ?', $forDate->format('Y'))
            ->where('month = ?', $forDate->format('m'))
            ->where('day = ?', $forDate->format('d'))
            ->forUpdate(true);

        $rowSet = $statisticsTable->fetchAll($statement);

        foreach ($rowSet as $row) {
            $row->ranking_value = $this->_rankingPlugin->calculateRankingValue($row->toArray());
            $row->save();
        }

    }

}
