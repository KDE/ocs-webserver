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

$_import_counter = 0;
$_import_file_counter = 0;
$_is_import_done = false;

class Backend_HiveuserController extends Local_Controller_Action_Backend
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const DATA_ID_NAME = 'id';
    protected $_errorMsg = null;

    protected $import_info;


    /** @var Default_Model_DbTable_HiveContent */
    protected $_model;

    protected $_modelName = 'Default_Model_DbTable_HiveUser';
    protected $_pageTitle = 'Import Hive01 Users';

    public function init()
    {
        $this->_model = new $this->_modelName();

        $this->view->pageTitle = $this->_pageTitle;

        parent::init();
    }

    public function indexAction()
    {
        $params = $this->getAllParams();

        if (empty($params['step'])) {
            $this->view->step = 0;
        } else {
            $this->view->step = $params['step'];
        }
        if ($this->view->step == 0) {
            $this->step0();
        } else {
            if ($this->view->step == 1) {
                $this->step1();
            } else {
                if ($this->view->step == 2) {
                    $this->step2();
                } else {
                    if ($this->view->step == 3) {
                        $this->step3();
                    } else {
                        if ($this->view->step == 4) {
                            $this->step4();
                        } else {
                            if ($this->view->step == 10) {
                                $this->step10();
                            } else {
                                if ($this->view->step == 20) {
                                    $this->step20();
                                } else {
                                    if ($this->view->step == 30) {
                                        $this->step30();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function step0()
    {
        $countProjects = null;
        $contentTable = new Default_Model_DbTable_HiveUser();
        try {
            $count = $contentTable->fetchCountUsers();
            $this->view->info = "Erfolgreich geladen aus DB";
            $countProjects = $count;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - ' . "Fehler bei fetchCountProjects");
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));

            $this->view->info = "Fehler bei laden aus DB:" . $e->getMessage();
            $countProjects = 0;
        }

        $this->view->coutAll = $countProjects;
    }

    private function step1()
    {
    }

    private function step2()
    {
    }

    private function step3()
    {
    }

    private function step4()
    {
    }

    private function step10()
    {
    }

    private function step20()
    {
    }

    private function step30()
    {
    }

    public function startImportAllAjaxAction()
    {
        global $_import_counter;
        global $_import_file_counter;
        global $_is_import_done;

        $_import_counter = 0;
        $_import_file_counter = 0;
        $_is_import_done = false;

        $this->_helper->layout->disableLayout();
        $params = $this->getAllParams();

        $info = null;

        $startIndex = null;
        $limit = intval($params['limit']);
        if (empty($startIndex)) {
            $startIndex = 0;
        }
        if (empty($limit)) {
            $limit = 5;
        }
        $hiveUserTable = new Default_Model_DbTable_HiveUser();
        $memberTable = new Default_Model_Member();

        $count = $hiveUserTable->fetchCountUsers();

        try {
            $users = $hiveUserTable->fetchAllUsers($startIndex, $limit);

            foreach ($users as $user) {
                $info .= " ## User: id = " . $user['id'] . ", name = " . $user['login'] . "  ";
                $start = microtime(true);
                $_import_counter++;
                $member = $memberTable->fetchRow('source_id = 1 AND source_pk = ' . $user['id']);

                if ($member) {
                    $info .= " - Update Member; ";
                    $info .= $this->updateMember($user);
                } else {
                    $info .= " - Insert Member; ";
                    $info .= $this->insertMember($user);
                }
                $time_elapsed_secs = microtime(true) - $start;
                $info .= $time_elapsed_secs . " secs";
                $info .= " - Done... ";

                //Mark user as imported
                $hiveUserTable->update(array("is_imported" => 1), "id = " . $user['id']);
            }
            $result['Message'] = $info;
            $_is_import_done = true;
        } catch (Exception $e) {
            $this->view->info = $e->getMessage();
            $_is_import_done = true;

            $result['Result'] = self::RESULT_ERROR;
            $result['Message'] = "Fehler bei laden aus DB:" . $e->getMessage();
        }

        $result['Result'] = self::RESULT_OK;
        $result['IsImportDone'] = $_is_import_done;
        $result['TotalCounter'] = $count;
        $result['ImportCounter'] = $_import_counter;
        $result['ImportFileCounter'] = $_import_file_counter;
        $result['limit'] = $limit;
        $result['offset'] = $startIndex + $limit;

        $this->_helper->json($result);
    }

    private function updateMember($user)
    {
        $memberTable = new Default_Model_Member();
        $updatearray = $this->makeMemberFromHiveUser($user);

        $member = $memberTable->fetchMemberFromHiveUserName($user['login']);
        $member_id = null;
        if ($member) {
            $member_id = $member['member_id'];
            $updatearray['member_id'] = $member_id;
            $memberTable->update($updatearray, 'member_id = ' . $member_id);
        } else {
            $member_id = $this->insertMember($user);
        }

        return $member_id;
    }

    private function makeMemberFromHiveUser($user)
    {
        $member = array();
        $member['source_id'] = 1;
        $member['source_pk'] = $user['id'];
        $member['username'] = $user['login'];
        $member['mail'] = $user['email'];
        $member['password'] = $user['passwd'];
        $member['roleId'] = 300;
        $member['type'] = 0;
        $member['is_active'] = 1;
        $member['is_deleted'] = 0;
        $member['mail_checked'] = 1;
        $member['agb'] = 1;
        $member['newsletter'] = $user['newsletter'];
        $member['login_method'] = 'local';
        $member['firstname'] = $user['firstname'];
        $member['lastname'] = $user['name'];
        $member['street'] = $user['street'];
        $member['zip'] = $user['zip'];
        $member['city'] = $user['city'];
        $member['country'] = $user['country'];
        $member['phone'] = $user['phonenumber'];
        $member['last_online'] = $user['last_online'];
        $member['biography'] = $user['description'];
        $member['paypal_mail'] = $user['paypalaccount'];
        //user pic
        $pic = $this->getHiveUserPicture($user['login'], $user['userdb']);
        if (empty($pic)) {
            $pic = 'hive/user-pics/nopic.png';
        }
        $member['profile_image_url'] = $pic;
        $member['profile_img_src'] = 'local';

        $member['link_facebook'] = $user['link_facebook'];
        $member['link_twitter'] = $user['link_twitter'];
        $member['validated'] = 0;
        $member['created_at'] = $user['created_at'];
        $member['changed_at'] = new Zend_Db_Expr('Now()');

        return $member;
    }

    private function getHiveUserPicture($username, $userdb)
    {
        $imageModel = new Default_Model_DbTable_Image();
        $path = 'http://cp1.hive01.com/CONTENT/user-bigpics/' . $userdb . '/' . $username . '.';
        $fileFolder = 'user-bigpics';
        $fileUrl = null;
        $fileExtention = null;
        if ($this->check_img($path . 'png')) {
            $fileUrl = ($path . 'png');
            $fileExtention = 'png';
        } else {
            if ($this->check_img($path . 'gif')) {
                $fileUrl = ($path . 'gif');
                $fileExtention = 'gif';
            } else {
                if ($this->check_img($path . 'jpg')) {
                    $fileUrl = ($path . 'jpg');
                    $fileExtention = 'jpg';
                } else {
                    if ($this->check_img($path . 'jpeg')) {
                        $fileUrl = ($path . 'jpeg');
                        $fileExtention = 'jpeg';
                    }
                }
            }
        }

        if ($fileUrl == null) {
            $path = 'http://cp1.hive01.com/CONTENT/user-pics/' . $userdb . '/' . $username . '.';
            $fileFolder = 'user-pics';
            if ($this->check_img($path . 'png')) {
                $fileUrl = ($path . 'png');
                $fileExtention = 'png';
            } else {
                if ($this->check_img($path . 'gif')) {
                    $fileUrl = ($path . 'gif');
                    $fileExtention = 'gif';
                } else {
                    if ($this->check_img($path . 'jpg')) {
                        $fileUrl = ($path . 'jpg');
                        $fileExtention = 'jpg';
                    } else {
                        if ($this->check_img($path . 'jpeg')) {
                            $fileUrl = ($path . 'jpeg');
                            $fileExtention = 'jpeg';
                        }
                    }
                }
            }
        }

        $cnFileUrl = null;
        if ($fileUrl != null) {
            $cnFileUrl = 'hive/' . $fileFolder . '/' . $userdb . '/' . $username . '.' . $fileExtention;
        }

        //var_dump($info);
        return $cnFileUrl;
    }

    private function check_img($file)
    {
        $response = false;
        $x = getimagesize($file);

        switch ($x['mime']) {
            case "image/gif":
                $response = true;
                break;
            case "image/jpeg":
                $response = true;
                break;
            case "image/png":
                $response = true;
                break;
            default:
                $response = false;
                break;
        }

        return $response;
    }

    private function insertMember($user)
    {
        $memberTable = new Default_Model_Member();
        $projectTable = new Default_Model_Project();
        $updatearray = $this->makeMemberFromHiveUser($user);
        //save member
        $member_id = $memberTable->insert($updatearray);
        $member = $memberTable->fetchMemberData($member_id);
        //save .me project
        $project = $this->makeProjectFromUser($member);
        $project_id = $projectTable->insert($project);

        $updatearray = array();
        $updatearray['main_project_id'] = $project_id;
        $memberTable->update($updatearray, 'member_id = ' . $member_id);

        return $member_id;
    }

    private function makeProjectFromUser($user)
    {
        $project = array();
        $project['source_id'] = 1;
        $project['source_pk'] = $user['member_id'];
        $project['source_type'] = 'user';
        $project['member_id'] = $user['member_id'];
        $project['content_type'] = 'text';
        $project['project_category_id'] = 0;
        $project['is_active'] = 1;
        $project['is_deleted'] = 0;
        $project['status'] = 100;
        $project['type_id'] = 0;
        $project['description'] = $user['biography'];
        $project['created_at'] = $user['created_at'];
        $project['changed_at'] = new Zend_Db_Expr('Now()');

        return $project;
    }

    private function deleteMember($user)
    {
        $memberTable = new Default_Model_Member();
        $member = $memberTable->fetchMemberFromHiveUserName($user['login']);
        $member_id = null;
        if ($member) {
            $member_id = $member['member_id'];
            $memberTable->setDeleted($member_id);
        }

        return true;
    }
}
