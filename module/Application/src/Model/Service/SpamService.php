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

namespace Application\Model\Service;

use Application\Model\Repository\SpamKeywordsRepository;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;

class SpamService extends BaseService
{
    const SPAM_THRESHOLD = 1;
    protected $db;
    private $config;
    private $spamKeywordsRepository;
    /**
     * @var array
     */
    private $messages;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->config = $GLOBALS['ocs_config'];
        $this->spamKeywordsRepository = new SpamKeywordsRepository($db);
    }

    /**
     * naive approach for spam detection
     *
     * @param array $project_data
     *
     * @return bool
     * @todo: define a list of stop words
     *
     */
    public function hasSpamMarkers($project_data)
    {
        try {
            $active = (boolean)$this->config->settings->spam_filter->active;
        } catch (Exception $e) {
            $active = false;
        }

        if (false === $active) {
            return false;
        }

        $sql = "SELECT `spam_key_word` FROM `spam_keywords` WHERE `spam_key_is_active` = 1 AND `spam_key_is_deleted` = 0";
        $keywords = $this->spamKeywordsRepository->fetchAll($sql);
        $keywordsArray = array();
        foreach ($keywords as $keyword) {
            $keywordsArray[] = $keyword['spam_key_word'];
        }

        $needles = implode('|', $keywordsArray);
        $haystack = implode(" ", array($project_data['title'], $project_data['description']));

        $matches = null;
        if (preg_match("/({$needles})/i", $haystack, $matches)) {
            $this->messages[] = "match for (" . implode(", ", $matches) . ")";

            return true;
        }

        return false;
    }

    public function fetchSpamCandidate()
    {
        $sql = "
            SELECT *
            FROM `stat_projects`
            WHERE `stat_projects`.`amount_reports` >= :threshold AND `stat_projects`.`status` = 100
            ORDER BY `stat_projects`.`changed_at` DESC, `stat_projects`.`created_at` DESC, `stat_projects`.`amount_reports` DESC
        ";

        $result = $this->spamKeywordsRepository->fetchAll($sql, array('threshold' => self::SPAM_THRESHOLD));
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}