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
 * */

use YoHang88\LetterAvatar\LetterAvatar;        
class Backend_MemberLetterAvatarCliController extends Local_Controller_Action_CliAbstract
{

    /**
     * Run php code as cronjob.
     * I.e.:
     * /usr/bin/php /var/www/pling.it/pling/scripts/cron.php -a /backend/member-payout-cli/run/action/payout/context/all >> /var/www/ocs-www/logs/masspay.log $
     *
     * @see Local_Controller_Action_CliInterface::runAction()
     */
    public function runAction()
    {
        require_once 'vendor/autoload.php';
        echo "Start runAction\n";                
         $sql = '
                        select member_id,username
                        from tmp_member_hive_nopic m
                        where avatar = 0                                                        
                        order by member_id desc          
                    ';
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
        foreach ($result as $m) {
            $name = substr($m['username'],0,1).' '.substr($m['username'],1);
            $avatar = new LetterAvatar($name,'square', 100);   
            $tmpImagePath = IMAGES_UPLOAD_PATH . 'tmp/la/'.$m['member_id'].'.png';
            $avatar->saveAs($tmpImagePath, LetterAvatar::MIME_TYPE_PNG);        

            $sql = 'update tmp_member_hive_nopic set avatar = 1 where member_id = '.$m['member_id'];
            Zend_Db_Table::getDefaultAdapter()->query($sql);
            echo $m['member_id']."\n";
        }
        echo 'done!';
    }

}
