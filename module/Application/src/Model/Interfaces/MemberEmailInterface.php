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

namespace Application\Model\Interfaces;

use Laminas\Db\Adapter\Driver\ResultInterface;

interface MemberEmailInterface extends BaseInterface
{
    /**
     * @param int $email_id
     *
     * @return int
     */
    public function setChecked($email_id);

    /**
     * @param int $email_id
     *
     * @return ResultInterface
     */
    public function setPrimary($email_id);

    /**
     * @param int $member_id
     *
     * @return int
     */
    public function setDeletedByMember($member_id);

    /**
     * @param int $member_id
     * @param int $identifier
     *
     * @return int
     */
    public function setDeleted($member_id, $identifier);

    /**
     * @param int $email_id
     *
     * @return int|void
     */
    public function delete($email_id);

    /**
     * @param $member_id
     *
     * @return void
     */
    public function setActivatedByMember($member_id);

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return int
     */
    public function setActive($member_id, $identifer);

    /**
     * @param int $email_id
     *
     * @return int|void
     */
    public function activate($email_id);
}