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

namespace Application\Model\Repository;

use Application\Model\Entity\PaypalValidStatus;
use Application\Model\Interfaces\PaypalValidStatusInterface;
use Laminas\Db\Adapter\AdapterInterface;

class PaypalValidStatusRepository extends BaseRepository implements PaypalValidStatusInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "paypal_valid_status";
        $this->_key = "id";
        $this->_prototype = PaypalValidStatus::class;
    }

    /**
     * @param int $id
     */
    public function setDeleted($id)
    {
        $updateValues = array(
            'is_active' => 0,
            'id'        => $id,
        );
        $this->update($updateValues);
    }

    /**
     * @return array
     */
    public function getStatiForSelectList()
    {
        $selectArr = $this->fetchAll("SELECT id,title FROM {$this->_name} WHERE is_active=1 ORDER BY id");

        $arrayModified = array();

        $arrayModified[0] = "";
        foreach ($selectArr as $item) {
            $arrayModified[$item['id']] = stripslashes($item['title']);
        }

        return $arrayModified;
    }

}