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
 *   https://github.com/yohang88/letter-avatar
 * 
 * */

use YoHang88\LetterAvatar\LetterAvatar;        
class Backend_MemberLetterAvatarCliController extends Local_Controller_Action_CliAbstract
{

    /**
     * run the following composer to download libs put in /library/  
     * composer require yohang88/letter-avatar
     * Run php code
     * I.e.:
     * /usr/bin/php /var/www/ocs-webserver/scripts/cron.php -a /backend/member-letter-avatar-cli/run/action
     * ubuntu@ip-10-171-104-73:/var/www/pling.it/pling$ sudo -u www-data php /var/www/pling.it/pling/scripts/cron.php -a /backend/member-letter-avatar-cli/run/action        
     * @see Local_Controller_Action_CliInterface::runAction()
     */
    public function runAction()
    {
        require_once 'vendor/autoload.php';
        echo "Start runAction\n";                
         // $sql = '
         //                select member_id,username
         //                from tmp_member_hive_nopic m
         //                where avatar = 0                                                        
         //                order by member_id desc          
         //            ';
        $sql = '
            select member_id,username from tmp_member_avatar_unknow where is_auto_generated = 1 
        ';
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
        foreach ($result as $m) {
            // $name = substr($m['username'],0,1).' '.substr($m['username'],1);  // hive nopic user has two chars
            $name = $m['username'].'  ';   
            $avatar = new LetterAvatar($name,'square', 400);   
            $tmpImagePath = IMAGES_UPLOAD_PATH . 'tmp/la_new/'.$m['member_id'].'.png';
            $avatar->saveAs($tmpImagePath, LetterAvatar::MIME_TYPE_PNG);        

            $sql = 'update tmp_member_hive_nopic set avatar = 1 where member_id = '.$m['member_id'];
            Zend_Db_Table::getDefaultAdapter()->query($sql);
            echo $m['member_id']."\n";
        }
        echo 'done!';
    }


    public function runupdateAction()
    {
        
        echo "Start runupdateAction\n";                
        $sql = "select * from tmp_member_avatar_unknow where width >0 and filetype is null";
         // $sql = '
         //                select * from tmp_member_avatar_unknow where width=0 limit 2011
         //            ';
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
        foreach ($result as $m) {                        
            //$file = 'https://cn.pling.it/img/'.$m['avatar'];                    // cc  
            $file = 'https://cn.pling.com/img/'.$m['avatar'];                      //live
                        
            // echo "\n";     
             try {                     
                 list($width, $height, $type) = getimagesize($file);                
                 $sql = 'update tmp_member_avatar_unknow set width='.$width.', height='.$height.', filetype='.$type.' where member_id = '.$m['member_id'];                        
                 Zend_Db_Table::getDefaultAdapter()->query($sql);
               
             }
             catch (Exception $e) {               
                 $sql = 'update tmp_member_avatar_unknow set width=-1 where member_id = '.$m['member_id'];                        
                 Zend_Db_Table::getDefaultAdapter()->query($sql);
             }
        }
        echo 'done!';
    }

}
