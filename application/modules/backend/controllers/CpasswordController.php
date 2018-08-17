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
 * Created: 06.08.2018
 */

class Backend_CpasswordController extends Local_Controller_Action_CliAbstract
{

    public function runAction()
    {
        $password = $this->getParam("p");
        echo "--- OCS password type ---\n";
        $encrypted = Local_Auth_Adapter_Ocs::getEncryptedPassword($password, Default_Model_DbTable_Member::PASSWORD_TYPE_OCS);
        $packed = base64_encode(pack("H*", $encrypted));
        echo $encrypted;
        echo "\n";
        echo $packed;
        echo "\n";
        echo "--- HIVE password type ---\n";
        $encrypted = Local_Auth_Adapter_Ocs::getEncryptedPassword($password, Default_Model_DbTable_Member::PASSWORD_TYPE_HIVE);
        $packed = base64_encode(pack("H*", $encrypted));
        echo $encrypted;
        echo "\n";
        echo $packed;
        echo "\n";
    }

}