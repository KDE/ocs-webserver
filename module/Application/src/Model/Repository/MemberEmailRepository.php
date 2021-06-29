<?php /** @noinspection PhpUnused */

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

use Application\Model\Entity\MemberEmail;
use Application\Model\Interfaces\MemberEmailInterface;
use Application\Model\Service\MemberDeactivationLogService;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;

class MemberEmailRepository extends BaseRepository implements MemberEmailInterface
{
    const EMAIL_DELETED = 1;
    const EMAIL_NOT_DELETED = 0;

    const EMAIL_PRIMARY = 1;
    const EMAIL_NOT_PRIMARY = 0;

    protected $_defaultValues = array(
        'email_member_id' => 0,
        'email_address'   => null,
        'email_primary'   => 0,
        'email_deleted'   => 0,
        'email_created'   => null,
        'email_checked'   => null,
    );

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "member_email";
        $this->_key = "email_id";
        $this->_prototype = MemberEmail::class;
    }

    /**
     * @param int $email_id
     *
     * @return int
     */
    public function setChecked($email_id)
    {
        $stmt = $this->update(['email_checked' => new Expression('now()')], ['email_id' => $email_id]);

        return $stmt->getAffectedRows();
    }

    /**
     * @param int $email_id
     *
     * @return int
     */
    public function setPrimary($email_id)
    {
        $values = array();
        $values['email_primary'] = 1;

        //$savedRow = $this->db->update($values, 'collection_id = '.$collection_id . ' AND project_id = ' . $project_id);
        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($values)->where([$this->_key => $email_id]);
        $statement = $sql->prepareStatementForSqlObject($update);
        $stmt = $statement->execute();

        return $stmt->getAffectedRows();
    }

    /**
     * @param int $member_id
     *
     * @return int
     */
    public function setDeletedByMember($member_id)
    {
        $sql = "SELECT `email_id` FROM `member_email` WHERE `email_member_id` = :member_id AND `email_deleted` = 0";
        $statement = $this->db->query($sql);
        /* @var $results ResultSet */
        $resultSet = $statement->execute(['member_id' => $member_id]);


        foreach ($resultSet as $item) {
            $this->setDeleted($member_id, $item['email_id']);
        }

        /*
        $sql = "UPDATE `{$this->_name}` SET `email_deleted` = 1 WHERE `email_member_id` = :memberId";
        $stmnt = $this->_db->query($sql, array('memberId' => $member_id));
        return $stmnt->rowCount();
        */
        $values = array();
        $values['email_deleted'] = 1;

        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($values)->where(['email_member_id' => $member_id]);
        $statement = $sql->prepareStatementForSqlObject($update);
        $obj = $statement->execute();

        return $obj->getAffectedRows();
    }

    /**
     * @param int $member_id
     * @param int $identifier
     *
     * @return int
     */
    public function setDeleted($member_id, $identifier)
    {
        $memberLog = new MemberDeactivationLogService($this->db);
        $memberLog->logMemberEmailAsDeleted($member_id, $identifier);

        return $this->delete($identifier);
    }

    /**
     * @param int $email_id
     *
     * @return int
     */
    public function delete($email_id)
    {
        $values = array();
        $values['email_deleted'] = 1;

        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($values)->where([$this->_key => $email_id]);
        $statement = $sql->prepareStatementForSqlObject($update);
        $stmnt = $statement->execute();

        return $stmnt->getAffectedRows();
    }

    /**
     * @param $member_id
     *
     * @return void
     */
    public function setActivatedByMember($member_id)
    {
        $sql = "SELECT `e`.`email_id` 
                FROM `member_email` `e`
                JOIN `member_deactivation_log` `l` ON `l`.`object_type_id` = 2 AND `l`.`object_id` = `e`.`email_id` AND `l`.`deactivation_id` = `e`.`email_member_id`  AND `l`.`is_deleted` = 0
                WHERE `e`.`email_member_id` = :member_id AND `email_deleted` = 1";
        $statement = $this->db->query($sql);
        /* @var $results ResultSet */
        $resultSet = $statement->execute(['member_id' => $member_id]);
        foreach ($resultSet as $item) {
            $this->setActive($member_id, $item['email_id']);
        }
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return int
     */
    public function setActive($member_id, $identifer)
    {
        $memberLog = new MemberDeactivationLogService($this->db);
        $memberLog->removeLogMemberEmailAsDeleted($member_id, $identifer);

        return $this->activate($identifer);
    }

    /**
     * @param int $email_id
     *
     * @return int
     */
    public function activate($email_id)
    {
        $values = array();
        $values['email_deleted'] = 0;

        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($values)->where([$this->_key => $email_id]);
        $statement = $sql->prepareStatementForSqlObject($update);
        $stmnt = $statement->execute();

        return $stmnt->getAffectedRows();
    }

}
