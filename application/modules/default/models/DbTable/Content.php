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
class Default_Model_DbTable_Content extends Zend_Db_Table_Abstract
{

    protected $_name = "content";

    public function getContent($contentId)
    {
        $selectArr = $this->_db->fetchRow('SELECT content FROM ' . $this->_name . ' WHERE content_id="' . $contentId . '"');
        #Zend_Debug::dump($selectArr);
        $content = $selectArr['content'];
        return $content;
    }

    public function getPage($contentId)
    {
        $statement = $this->select()
            ->where('content_id=?', $contentId);

        return $this->fetchRow($statement);
    }

    public function getPageByName($url_name)
    {
        $statement = $this->select()
            ->where('url_name=?', $url_name)
            ->where('is_active = 1');

        return $this->fetchRow($statement);
    }


    public function setStatus($status, $id)
    {
        $updateValues = array(
            'is_active' => $status,
            'changed_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, 'content_id=' . $id);
    }

    public function setDelete($id)
    {
        $updateValues = array(
            'is_deleted' => 1,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, 'content_id=' . $id);
    }
}