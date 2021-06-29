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

use Application\Model\Entity\MemberRole;
use Application\Model\Interfaces\MemberRoleInterface;
use Laminas\Db\Adapter\AdapterInterface;


class MemberRoleRepository extends BaseRepository implements MemberRoleInterface
{
    const ROLE_DEFAULT = '';
    const ROLE_NAME_MODERATOR = 'moderator';
    const ROLE_NAME_STAFF = 'staff';
    const ROLE_NAME_ADMIN = 'admin';

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "member_role";
        $this->_key = "member_role_id";
        $this->_prototype = MemberRole::class;
    }

}
