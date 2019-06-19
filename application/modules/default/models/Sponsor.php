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

class Default_Model_Sponsor
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
    
    public function fetchAllSponsors()
    {
        $sql = "
            SELECT sponsor.name, sponsor.sponsor_id
            FROM sponsor
        ";
        $resultSet = $this->getAdapter()->fetchAll($sql);

        return $resultSet;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    private function getAdapter()
    {
        return Zend_Db_Table::getDefaultAdapter();
    }

    /**
     * @param int $section_id
     *
     * @return array
     */
    public function fetchSectionItems($section_id)
    {
        $sql = "SELECT section_sponsor.section_sponsor_id
                    , section_sponsor.section_id
                    , sponsor.sponsor_id
                    , section_sponsor.percent_of_sponsoring
		    , sponsor.name AS sponsor_name
                    , sponsor.fullname AS sponsor_fullname
                    , sponsor.description AS sponsor_description
                    , sponsor.is_active
             FROM section_sponsor 
             JOIN sponsor ON sponsor.sponsor_id = section_sponsor.sponsor_id 
             WHERE section_id = :section_id";
        $resultSet = $this->getAdapter()->fetchAll($sql, array('section_id' => $section_id));

        return $resultSet;
    }

    /**
     * @param int    $sectionId
     * @param string $sponsorId
     * @param int    $percent
     *
     * @return array
     */
    public function assignSponsor($sectionId, $sponsorId, $percent)
    {
        $section_sponsor_id = $this->saveSectionSponsor($sectionId, $sponsorId, $percent);
        $resultSet = $this->fetchOneSectionItem($section_sponsor_id);

        return $resultSet;
    }



    /**
     * @param string $tag_name
     *
     * @return int
     */
    public function saveTag($tag_name,$tag_fullname, $tag_description,$is_active=1)
    {
        $tag_name = strtolower($tag_name);
        $sql = "SELECT tag_id FROM tag WHERE tag_name = :tagName";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('tagName' => $tag_name));
        if (empty($resultSet)) {
            $this->getAdapter()->insert('tag', array('tag_name' => $tag_name, 'tag_fullname' => $tag_fullname, 'tag_description' => $tag_description,'is_active' => $is_active));
            $resultId = $this->getAdapter()->lastInsertId();
        } else {
            $resultId = $resultSet['tag_id'];
        }

        return $resultId;
    }

    /**
     * @param int $group_id
     * @param int $tag_id
     *
     * @return int
     */
    public function saveSectionSponsor($section_id, $sponsor_id, $percent)
    {
        $sql = "SELECT section_sponsor_id FROM section_sponsor WHERE section_id = :section_id AND sponsor_id = :sponsor_id";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('section_id' => $section_id, 'sponsor_id' => $sponsor_id));
        if (empty($resultSet)) {
            $this->getAdapter()->insert('section_sponsor', array('section_id' => $section_id, 'sponsor_id' => $sponsor_id));
            $resultId = $this->getAdapter()->lastInsertId();
        } else {
            $resultId = $resultSet['section_sponsor_id'];
        }

        return $resultId;
    }

    /**
     * @param int $section_sponsor_id
     *
     * @return array|false
     */
    public function fetchOneSectionItem($section_sponsor_id)
    {
        $sql = "SELECT section_sponsor.section_sponsor_id
                    , section_sponsor.section_id
                    , sponsor.sponsor_id
		    , sponsor.name AS sponsor_name
                    , sponsor.fullname AS sponsor_fullname
                    , sponsor.description AS sponsor_description
                    , sponsor.is_active
             FROM section_sponsor 
             JOIN sponsor ON sponsor.sponsor_id = section_sponsor.sponsor_id 
             WHERE section_sponsor_id = :section_sponsor_id";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('section_sponsor_id' => $section_sponsor_id));

        return $resultSet;
    }

    public function updateSectionSponsor($sectionSponsorId, $sectionId, $sponsorId, $percent)
    {        
            $updateValues = array(
                'section_id' =>$sectionId,
                'sponsor_id' => $sponsorId,
                'percent_of_sponsoring' => $percent
            );
        
            $this->getAdapter()->update('section_sponsor', $updateValues, array('section_sponsor_id = ?' => $sectionSponsorId));        
    }

    public function deleteSectionSponsor($sectionSponsorId)
    {
        $this->getAdapter()->delete('section_sponsor', array('section_sponsor_id = ?' => $sectionSponsorId));
    }

}