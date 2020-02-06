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
class Backend_PploadController extends Local_Controller_Action_CliAbstract
{

    /**
     * cronjob command
     * 0   1 * * * /usr/bin/php /var/www/pling.it/pling/scripts/cron.php -a /backend/ppload/run/ >> /var/www/pling.it/logs/stat_downloads.log 2>&1
     */
    public function runAction()
    {
        $this->updatePploadDownloadStat();
        $this->updateProjectDownloadStat();
    }

    protected function updatePploadDownloadStat()
    {
        $sql = "
        TRUNCATE ppload.stat_collection_download;
        INSERT INTO ppload.stat_collection_download
        SELECT 
            collection_id, count(*) as amount
        FROM
            ppload.ppload_files_downloaded_unique as ppfd
        WHERE 
	        ppfd.downloaded_timestamp > DATE_ADD(CURDATE(), INTERVAL - 3 MONTH)
        GROUP BY ppfd.collection_id
        ORDER BY amount DESC;
        ";
        Zend_Db_Table::getDefaultAdapter()->query($sql);
    }

    protected function updateProjectDownloadStat()
    {
        $sql = "
        TRUNCATE stat_downloads_quarter_year;
        INSERT INTO stat_downloads_quarter_year
        SELECT p.project_id, p.project_category_id, p.ppload_collection_id, scd.amount, pc.title as category_title
        FROM project as p
        JOIN ppload.stat_collection_download as scd on p.ppload_collection_id = scd.collection_id
        JOIN project_category as pc using (project_category_id)
        ORDER BY p.project_category_id ASC, scd.amount DESC
        ;
        ";
        Zend_Db_Table::getDefaultAdapter()->query($sql);
    }

}