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

use Application\Model\Entity\MailTemplate;
use Application\Model\Interfaces\MailTemplateInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Sql\Sql;
use RuntimeException;

class MailTemplateRepository extends BaseRepository implements MailTemplateInterface
{
    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "mail_template";
        $this->_key = "mail_template_id";
        $this->_prototype = MailTemplate::class;
    }

    public function findBy($column, $value)
    {
        $sql = new Sql($this->db);
        $select = $sql->select();
        $select->from($this->_name)->where([$column => $value])->limit(1);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $mail_template = new MailTemplate();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $mail_template->exchangeArray($result->current());
        } else {
            throw new RuntimeException(sprintf('Failed retrieving ' . $this->_name . ' with sql query "%s"; unknown database error.', $sql));
        }

        return $mail_template;
    }

}
