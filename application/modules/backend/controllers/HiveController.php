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

class Backend_HiveController extends Local_Controller_Action_Backend
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const DATA_ID_NAME = 'id';
    var $_HIVE_BASE_URL;
    var $_OCS_CN_FILE_SYNC_URL;
    var $_OCS_FILE_SYNC_URL;
    protected $_errorMsg = null;

    protected $import_info;


    /** @var Default_Model_DbTable_HiveContent */
    protected $_model;

    protected $_modelName = 'Default_Model_DbTable_HiveContent';
    protected $_pageTitle = 'Import Hive01 Files';
    protected $_allowed = array(
        'image/jpeg'          => '.jpg',
        'image/jpg'           => '.jpg',
        'image/png'           => '.png',
        'image/gif'           => '.gif',
        'application/x-empty' => '.png'
    );

    public function init()
    {
        $this->_model = new $this->_modelName();

        $this->view->pageTitle = $this->_pageTitle;

        parent::init();
    }

    public function initVars()
    {
        if (strtolower(php_uname("n")) == 'do-pling-com') {
            $this->_HIVE_BASE_URL = 'http://cp1.hive01.com';
            $this->_OCS_CN_FILE_SYNC_URL = 'https://cn.opendesktop.org';
            $this->_OCS_FILE_SYNC_URL = 'https://www.ppload.com';
        } else {
            $this->_HIVE_BASE_URL = 'http://cp1.hive01.com';
            $this->_OCS_CN_FILE_SYNC_URL = 'https://cn.pling.ws';
            $this->_OCS_FILE_SYNC_URL = 'https://ws.ppload.com';
        }
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
        $contentTable = new Default_Model_DbTable_HiveContent();
        try {
            $count = $contentTable->fetchCountProjects();
            $this->view->info = "Erfolgreich geladen aus DB";
            $countProjects = $count;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - ' . "Error in fetchCountProjects");
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));

            $this->view->info = "Error while loading from the database:" . $e->getMessage();
            $countProjects = 0;
        }

        $this->view->coutAll = $countProjects;
    }

    private function step1()
    {
        $catArray = null;
        $contentTable = new Default_Model_DbTable_HiveContent();
        try {
            $catArray = $contentTable->fetchHiveCategories();
            $this->view->info = "Successfully loaded from the database";
        } catch (Exception $e) {
            $this->view->info = "Error while loading from the database:" . $e->getMessage();
            $catArray = null;
        }

        $this->view->categories = $catArray;
    }

    private function step2()
    {
        $params = $this->getAllParams();
        $cat_id = $params['cat_id'];
        $cat = null;
        $contentTable = new Default_Model_DbTable_HiveContent();
        try {
            $count = $contentTable->fetchCountProjectsForCategory($cat_id);

            $cat = $contentTable->fetchHiveCategory($cat_id);
            $this->view->info .= "Successfully loaded from the database";
        } catch (Exception $e) {
            $this->view->info .= "Error while loading from the database:" . $e->getMessage();
            $cat = null;
            $count = 0;
        }

        $this->view->cat_id = $cat['id'];
        $this->view->cat_desc = $cat['desc'];
        $this->view->count = $count;

        try {
            $catArray = $contentTable->fetchOcsCategories();
            $this->view->info = "Successfully loaded from the database";
        } catch (Exception $e) {
            $this->view->info = "Error while loading from the database:" . $e->getMessage();
            $catArray = null;
        }

        $this->view->categories = $catArray;
    }

    private function step3()
    {
        $params = $this->getAllParams();
        $cat_id = $params['cat_id'];
        $ocs_cat_id = $params['ocs_cat_id'];
        $cat = null;
        $contentTable = new Default_Model_DbTable_HiveContent();
        try {
            $count = $contentTable->fetchCountProjectsForCategory($cat_id);
            $cat = $contentTable->fetchHiveCategory($cat_id);
            $this->view->info .= "Successfully loaded from the database";
            $this->view->cat_id = $cat['id'];
            $this->view->cat_desc = $cat['desc'];
            $this->view->count = $count;

            $ocs_cat = $contentTable->fetchOcsCategory($ocs_cat_id);
            $this->view->info .= "Successfully loaded from the database";
            $this->view->ocs_cat_id = $ocs_cat['id'];
            $this->view->ocs_cat_desc = $ocs_cat['desc'];
        } catch (Exception $e) {
            $this->view->info = "Error while loading from the database:" . $e->getMessage();
            $cat = null;
            $count = 0;
        }

        try {
            $catArray = $contentTable->fetchOcsCategories();
            $this->view->info .= "Successfully loaded from the database";
        } catch (Exception $e) {
            $this->view->info .= "Error while loading from the database:" . $e->getMessage();
            $catArray = null;
        }

        $this->view->categories = $catArray;
    }

    private function step4()
    {
        $params = $this->getAllParams();
        $cat_id = $params['cat_id'];
        $ocs_cat_id = $params['ocs_cat_id'];
        $importGalleryPics = $params['import_previews'];
        $importFiles = $params['import_files'];
        $this->view->cat_id = $cat_id;
        $this->view->ocs_cat_id = $ocs_cat_id;
        $this->view->import_files = $importFiles;
        $this->view->import_previews = $importGalleryPics;
    }

    private function step10()
    {
    }

    private function step20()
    {
        $catArray = null;
        $params = $this->getAllParams();
        $cat_ids = $params['cat_ids'];

        $contentTable = new Default_Model_DbTable_HiveContentCategory();
        try {
            if (isset($cat_ids)) {
                $catArray = $contentTable->fetchHiveCategories($cat_ids);
                $this->view->info = "Successfully loaded from the database";
            } else {
                $this->view->info = "No category selected!";
            }
        } catch (Exception $e) {
            $this->view->info = "Error while loading from the database:" . $e->getMessage();
            $catArray = null;
        }

        $this->view->categories = $catArray;
        $this->view->cat_ids = $cat_ids;
    }

    private function step30()
    {
        $catArray = null;
        $params = $this->getAllParams();
        $cat_ids = $params['cat_ids'];
        $this->view->cat_ids = $cat_ids;

        $importGalleryPics = $params['import_previews'];
        $importFiles = $params['import_files'];
        $this->view->import_files = $importFiles;
        $this->view->import_previews = $importGalleryPics;
    }

    public function countAction()
    {
        $this->_helper->layout->disableLayout();
        $cat_id = (int)$this->getParam('cat_id');
        $contentTable = new Default_Model_DbTable_HiveContent();
        $count = $contentTable->fetchCountProjectsForCategory($cat_id);

        $result = array();
        $result['Result'] = self::RESULT_OK;
        $result['TotalRecordCount'] = $count;

        $this->_helper->json($result);
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
        $cat_id = $params['cat_id'];
        $hiveCatTable = new Default_Model_DbTable_HiveContentCategory();

        $ocs_cat_id = $hiveCatTable->fetchOcsCategoryForHiveCategory($cat_id);

        if (!isset($ocs_cat_id)) {
            $info .= " - No Ocs-Category found!";
            exit;
        }

        $info = null;

        $importGalleryPics = false;
        if (isset($params['import_previews']) && $params['import_previews'] != '') {
            $importGalleryPics = true;
        }
        $info .= " - With Gallery Pics? " . $importGalleryPics;

        $importFiles = false;
        if (isset($params['import_files']) && $params['import_files'] != '') {
            $importFiles = true;
        }
        $info .= " - With Files? " . $importFiles;

        $startIndex = null;
        $limit = intval($params['limit']);
        if (empty($startIndex)) {
            $startIndex = 0;
        }
        if (empty($limit)) {
            $limit = 5;
        }

        $result = array();
        $contentTable = new Default_Model_DbTable_HiveContent();
        $memberTable = new Default_Model_Member();
        $projectTable = new Default_Model_Project();
        $hiveMemeberTable = new Default_Model_DbTable_HiveUser();
        try {
            $projects = $contentTable->fetchAllProjectsForCategory($cat_id, $startIndex, $limit);

            foreach ($projects as $project) {
                $_import_counter++;

                $info .= " ## Poject: id = " . $project['id'] . ", name = " . $project['name'] . "  ";
                $start = microtime(true);

                //1. Download/Upload Project-Picture
                //$info .= " - Project-Picture ";
                //$start = microtime(true);
                //$cnFilePath = $this->uploadProjectPicture($project['id']);
                $cnFilePath = $this->getProjectPicture($project['id']);
                //$time_elapsed_secs = microtime(true) - $start;
                //$info .= $time_elapsed_secs." secs";

                //2. Create Ocs Project
                //$info .= " - Project ";
                //$start = microtime(true);
                try {
                    $hiveUser = $hiveMemeberTable->fetchRow("login = '" . $project['user']."'");
                    if(!empty($hiveUser)) {
                        $projectId = $this->createUpdateOcsProjects($hiveUser, $project, $ocs_cat_id, $cnFilePath);
                    }
                } catch (Exception $e) {
                    //Error: log error and go on
                    $error = array();
                    $error['import_error'] = $e;
                    $error['is_imported'] = 1;
                    $contentTable->update($error, 'id = ' . $project['id']);
                }
                //$time_elapsed_secs = microtime(true) - $start;
                //$info .= $time_elapsed_secs." secs";

                if ($projectId) {

                    //3. Upload files
                    if ($importFiles) {
                        $pploadError = null;
                        $info .= " - Files ";
                        $start = microtime(true);
                        try {
                            $_import_file_counter = $this->uploadFilesAndLinks($project, $projectId);
                        } catch (Exception $e) {
                            $pploadError .= $e;
                            $info .= $pploadError;
                        }
                        $info .= " - Files Uploaded: " . $_import_file_counter . " -  ";
                        $time_elapsed_secs = microtime(true) - $start;
                        $info .= $time_elapsed_secs . " secs";
                    } else {
                        $_import_file_counter = 1;
                    }

                    if (true) {

                        //4. Gallery Pics
                        if ($importGalleryPics) {

                            $info .= " - Gallery Pics ";
                            //$start = microtime(true);
                            $previewPicsArray = array();
                            if (!empty($project['preview1'])) {
                                //$cnFilePathPre = $this->uploadPreviewPicture($project['id'], 1, $project['preview1']);
                                $cnFilePathPre = $this->getPreviewPicture($project['id'], 1, $project['preview1']);
                                //add preview pic to ocs-project
                                if (!empty($cnFilePathPre)) {
                                    $previewPicsArray[] = $cnFilePathPre;
                                    $info .= " - PreviewPic1 ";
                                }
                            }
                            if (!empty($project['preview2'])) {
                                //$cnFilePathPre = $this->uploadPreviewPicture($project['id'], 2, $project['preview2']);
                                $cnFilePathPre = $this->getPreviewPicture($project['id'], 2, $project['preview2']);
                                if (!empty($cnFilePathPre)) {
                                    $previewPicsArray[] = $cnFilePathPre;
                                    $info .= " - PreviewPic2 ";
                                }
                            }
                            if (!empty($project['preview3'])) {
                                //$cnFilePathPre = $this->uploadPreviewPicture($project['id'], 3, $project['preview3']);
                                $cnFilePathPre = $this->getPreviewPicture($project['id'], 3, $project['preview3']);
                                if (!empty($cnFilePathPre)) {
                                    $previewPicsArray[] = $cnFilePathPre;
                                    $info .= " - PreviewPic3 ";
                                }
                            }
                            if (!empty($previewPicsArray)) {
                                $projectTable->updateGalleryPictures($projectId, $previewPicsArray);
                            }
                        }
                        //$time_elapsed_secs = microtime(true) - $start;
                        //$info .= $time_elapsed_secs." secs";

                        //5. Mark project as imported
                        //$info .= " - Mark project as imported ";
                        //$start = microtime(true);
                        $contentTable->update(array("is_imported" => 1), "id = " . $project['id']);
                        //$time_elapsed_secs = microtime(true) - $start;
                        //$info .= $time_elapsed_secs." secs";

                    } else {
                        $info .= " - NO Files Uploaded";
                        $contentTable->update(array(
                            "is_imported"  => 1,
                            "import_error" => "Error on fileupload to cc.ppload.com Exception: " . $pploadError
                        ), "id = " . $project['id']);
                    }
                } else {
                    $info .= " - Project NOT created! ";
                }

                $time_elapsed_secs = microtime(true) - $start;
                $info .= $time_elapsed_secs . " secs";
                $info .= " - Done... ";
            }
            $result['Message'] = $info;
            $_is_import_done = true;
        } catch (Exception $e) {
            $this->view->info = $e->getMessage();
            $_is_import_done = true;

            $result['Result'] = self::RESULT_ERROR;
            $result['Message'] = "Error while loading from database:" . $e->getMessage();
        }

        $count = $contentTable->fetchCountProjectsForCategory($cat_id);

        $result['Result'] = self::RESULT_OK;
        $result['IsImportDone'] = $_is_import_done;
        $result['TotalCounter'] = $count;
        $result['ImportCounter'] = $_import_counter;
        $result['ImportFileCounter'] = $_import_file_counter;
        $result['limit'] = $limit;
        $result['offset'] = $startIndex + $limit;

        $this->_helper->json($result);
    }

    private function getProjectPicture($hiveProjectId)
    {
        $imageModel = new Default_Model_DbTable_Image();
        $path = 'https://cn.opendesktop.org/img/hive/content-pre1/' . $hiveProjectId . '-1.';
        $fileUrl = null;
        $fileExtention = null;
        $info = '';

        if ($this->check_img($path . 'gif')) {
            $fileUrl = ($path . 'gif');
            $fileExtention = 'gif';
        }
        if ($this->check_img($path . 'png')) {
            $fileUrl = ($path . 'png');
            $fileExtention = 'png';
        }
        if ($this->check_img($path . 'jpg')) {
            $fileUrl = ($path . 'jpg');
            $fileExtention = 'jpg';
        }
        if ($this->check_img($path . 'jpeg')) {
            $fileUrl = ($path . 'jpeg');
            $fileExtention = 'jpeg';
        }
        if ($this->check_img($path . 'mockup')) {
            $fileUrl = ($path . 'mockup');
            $fileExtention = 'mockup';
        }
        if ($this->check_img($path . 'GIF')) {
            $fileUrl = ($path . 'GIF');
            $fileExtention = 'GIF';
        }
        if ($this->check_img($path . 'PNG')) {
            $fileUrl = ($path . 'PNG');
            $fileExtention = 'PNG';
        }
        if ($this->check_img($path . 'JPG')) {
            $fileUrl = ($path . 'JPG');
            $fileExtention = 'JPG';
        }
        if ($this->check_img($path . 'JPEG')) {
            $fileUrl = ($path . 'JPEG');
            $fileExtention = 'JPEG';
        }
        if ($this->check_img($path . 'MOCKUP')) {
            $fileUrl = ($path . 'MOCKUP');
            $fileExtention = 'MOCKUP';
        }
        $cnFileUrl = null;
        if ($fileUrl != null) {
            $config = Zend_Registry::get('config');
            $cnFileUrl = '/hive/content-pre1/' . $hiveProjectId . '-1.' . $fileExtention;

            /**
             * //Workaround for gifs: don't use cache on cdn
             * $pos = strrpos($cnFileUrl, ".gif");
             * if ($pos>0) { // Beachten sie die drei Gleichheitszeichen
             * //gefunden ...
             * $cnFileUrl = str_replace('/cache/120x96-2', '', $cnFileUrl);
             * }
             **/
            $info .= "ImageUpload successful: " . $cnFileUrl;
        } else {
            $path = 'https://cn.opendesktop.org/img/hive/content-pre2/' . $hiveProjectId . '-2.';
            $fileUrl = null;
            $fileExtention = null;
            $info = '';

            if ($this->check_img($path . 'gif')) {
                $fileUrl = ($path . 'gif');
                $fileExtention = 'gif';
            }
            if ($this->check_img($path . 'png')) {
                $fileUrl = ($path . 'png');
                $fileExtention = 'png';
            }
            if ($this->check_img($path . 'jpg')) {
                $fileUrl = ($path . 'jpg');
                $fileExtention = 'jpg';
            }
            if ($this->check_img($path . 'jpeg')) {
                $fileUrl = ($path . 'jpeg');
                $fileExtention = 'jpeg';
            }
            if ($this->check_img($path . 'mockup')) {
                $fileUrl = ($path . 'mockup');
                $fileExtention = 'mockup';
            }
            if ($this->check_img($path . 'GIF')) {
                $fileUrl = ($path . 'GIF');
                $fileExtention = 'GIF';
            }
            if ($this->check_img($path . 'PNG')) {
                $fileUrl = ($path . 'PNG');
                $fileExtention = 'PNG';
            }
            if ($this->check_img($path . 'JPG')) {
                $fileUrl = ($path . 'JPG');
                $fileExtention = 'JPG';
            }
            if ($this->check_img($path . 'JPEG')) {
                $fileUrl = ($path . 'JPEG');
                $fileExtention = 'JPEG';
            }
            if ($this->check_img($path . 'MOCKUP')) {
                $fileUrl = ($path . 'MOCKUP');
                $fileExtention = 'MOCKUP';
            }
            $cnFileUrl = null;
            if ($fileUrl != null) {
                $config = Zend_Registry::get('config');
                $cnFileUrl = '/hive/content-pre2/' . $hiveProjectId . '-2.' . $fileExtention;

                /**
                 * //Workaround for gifs: don't use cache on cdn
                 * $pos = strrpos($cnFileUrl, ".gif");
                 * if ($pos>0) { // Beachten sie die drei Gleichheitszeichen
                 * //gefunden ...
                 * $cnFileUrl = str_replace('/cache/120x96-2', '', $cnFileUrl);
                 * }
                 **/
                $info .= "ImageUpload successful: " . $cnFileUrl;
            } else {
                $path = 'https://cn.opendesktop.org/img/hive/content-pre3/' . $hiveProjectId . '-3.';
                $fileUrl = null;
                $fileExtention = null;
                $info = '';

                if ($this->check_img($path . 'gif')) {
                    $fileUrl = ($path . 'gif');
                    $fileExtention = 'gif';
                }
                if ($this->check_img($path . 'png')) {
                    $fileUrl = ($path . 'png');
                    $fileExtention = 'png';
                }
                if ($this->check_img($path . 'jpg')) {
                    $fileUrl = ($path . 'jpg');
                    $fileExtention = 'jpg';
                }
                if ($this->check_img($path . 'jpeg')) {
                    $fileUrl = ($path . 'jpeg');
                    $fileExtention = 'jpeg';
                }
                if ($this->check_img($path . 'mockup')) {
                    $fileUrl = ($path . 'mockup');
                    $fileExtention = 'mockup';
                }
                if ($this->check_img($path . 'GIF')) {
                    $fileUrl = ($path . 'GIF');
                    $fileExtention = 'GIF';
                }
                if ($this->check_img($path . 'PNG')) {
                    $fileUrl = ($path . 'PNG');
                    $fileExtention = 'PNG';
                }
                if ($this->check_img($path . 'JPG')) {
                    $fileUrl = ($path . 'JPG');
                    $fileExtention = 'JPG';
                }
                if ($this->check_img($path . 'JPEG')) {
                    $fileUrl = ($path . 'JPEG');
                    $fileExtention = 'JPEG';
                }
                if ($this->check_img($path . 'MOCKUP')) {
                    $fileUrl = ($path . 'MOCKUP');
                    $fileExtention = 'MOCKUP';
                }
                $cnFileUrl = null;
                if ($fileUrl != null) {
                    $config = Zend_Registry::get('config');
                    $cnFileUrl = '/hive/content-pre3/' . $hiveProjectId . '-3.' . $fileExtention;

                    /**
                     * //Workaround for gifs: don't use cache on cdn
                     * $pos = strrpos($cnFileUrl, ".gif");
                     * if ($pos>0) { // Beachten sie die drei Gleichheitszeichen
                     * //gefunden ...
                     * $cnFileUrl = str_replace('/cache/120x96-2', '', $cnFileUrl);
                     * }
                     **/
                    $info .= "ImageUpload successful: " . $cnFileUrl;
                } else {
                    $info .= "No preview pic";
                }
            }
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

    private function createUpdateOcsProjects($hiveUser, $project, $ocs_cat_id, $cnFilePath)
    {
        $projectTable = new Default_Model_Project();
        $memberTable = new Default_Model_Member();
        $info = '';
        $projectId = null;
        $uuid = null;
        $count_likes = null;
        $count_dislikes = null;
        try {
            $projectsResult = $projectTable->fetchAll("source_type = 'project' AND source_id = 1 AND source_pk = " . $project['id']);

            if (count($projectsResult) > 0) {
                $info .= "Project load successfull: " . $projectsResult[0]['project_id'];
                $projectId = $projectsResult[0]['project_id'];
                $uuid = $projectsResult[0]['uuid'];
            }
        } catch (Exception $e) {
            $info .= (__FUNCTION__ . '::ERROR load Project: ' . $e);
        }

        $memberId = null;
        try {
            $member = $memberTable->fetchMemberFromHiveUserId($hiveUser['id']);
            if ($member) {
                $info .= "Member load successfull: " . $member['member_id'];
                $memberId = $member['member_id'];
            } else {
                throw new Exception(__FUNCTION__ . '::ERROR load member: Member not found: Username = ' . $project['user']);
            }
        } catch (Exception $e) {
            throw new Exception(__FUNCTION__ . '::ERROR load member: ' . $e);
        }

        $projectObj = array();
        $projectObj['member_id'] = $memberId;
        $projectObj['content_type'] = 'text';
        $projectObj['project_category_id'] = $ocs_cat_id;
        $projectObj['hive_category_id'] = $project['type'];

        //Project not deleted?
        if ($project['deletedat'] == 0 && $project['status'] == 1) {
            $projectObj['is_deleted'] = 0;
            $projectObj['deleted_at'] = $project['deleted_at'];
            $projectObj['is_active'] = 1;
            $projectObj['status'] = 100;
        } else {
            $projectObj['is_deleted'] = 1;
            $projectObj['deleted_at'] = $project['deleted_at'];
            $projectObj['is_active'] = 0;
            $projectObj['status'] = 30;
        }

        $projectObj['pid'] = null;
        $projectObj['type_id'] = 1;
        $projectObj['title'] = $project['name_utf8'];
        $projectObj['description'] = $project['description_utf8'];
        $projectObj['version'] = $project['version'];
        $projectObj['image_big'] = $cnFilePath;
        $projectObj['image_small'] = $cnFilePath;
        $projectObj['start_date'] = null;
        $projectObj['content_url'] = null;
        $projectObj['created_at'] = $project['created_at'];
        $projectObj['changed_at'] = $project['changed_at'];
        $projectObj['creator_id'] = $memberId;

        $projectObj['count_downloads_hive'] = $project['downloads'];

        //     	$projectObj['facebook_code'] = null;
        //     	$projectObj['twitter_code'] = null;
        //     	$projectObj['google_code'] = null;
        //     	$projectObj['link_1'] = null;
        //     	$projectObj['embed_code'] = null;
        //     	$projectObj['ppload_collection_id'] = null;
        //     	$projectObj['validated'] = null;
        //     	$projectObj['validated_at'] = null;
        //     	$projectObj['featured'] = null;
        //     	$projectObj['amount'] = null;
        //     	$projectObj['amount_period'] = null;
        //     	$projectObj['claimable'] = null;
        //     	$projectObj['claimed_by_member'] = null;
        $projectObj['source_id'] = 1;
        $projectObj['source_pk'] = $project['id'];
        $projectObj['source_type'] = 'project';

        if (!isset($uuid)) {
            $uuid = Local_Tools_UUID::generateUUID();
            $projectObj['uuid'] = $uuid;
        }

        if ($projectId) {
            try {
                $votingTable = new Default_Model_DbTable_ProjectRating();

                $votingTable->delete('member_id = 0 AND project_id = ' . $projectId);
                //insert the old hive votings
                $votearray = array();
                $votearray['member_id'] = 0;
                $votearray['project_id'] = $projectId;
                $votearray['user_like'] = $project['scoregood'];
                $votearray['user_dislike'] = $project['scorebad'];
                $newVote = $votingTable->save($votearray);

                $ratingSum = $votingTable->fetchRating($projectId);
                $count_likes = $ratingSum['count_likes'];
                $count_dislikes = $ratingSum['count_dislikes'];
                $projectObj['count_likes'] = $count_likes;
                $projectObj['count_dislikes'] = $count_dislikes;

                //update project
                $updateCount = $projectTable->update($projectObj, "project_id = " . $projectId);
                $info .= "Update Project successful: Updated rows: " . $updateCount;

                //update changelog?
                if (isset($project['changelog']) && $project['changelog'] != '') {
                    $projectUpdatesTable = new Default_Model_ProjectUpdates();
                    $projectUpdate =
                        $projectUpdatesTable->fetchRow('project_id = ' . $projectId . ' AND source_id = 1 AND source_pk = '
                            . $project['id']);
                    if ($projectUpdate) {
                        $projectUpdate = $projectUpdate->toArray();
                        if ($projectUpdate['text'] != $project['changelog']) {
                            $projectUpdate['text'] = $project['changelog_utf8'];
                            $projectUpdate['changed_at'] = $projectObj['changed_at'];
                            $projectUpdatesTable->save($projectUpdate);
                        }
                    } else {
                        $data = array();
                        $data['project_id'] = $projectId;
                        $data['member_id'] = $projectObj['member_id'];
                        $data['public'] = 1;
                        $data['text'] = $project['changelog_utf8'];
                        $data['created_at'] = $projectObj['created_at'];
                        $data['changed_at'] = $projectObj['changed_at'];
                        $data['source_id'] = 1;
                        $data['source_pk'] = $project['id'];

                        $projectUpdatesTable->save($data);
                    }
                }
            } catch (Exception $e) {
                throw new Exception(__FUNCTION__ . '::ERROR update project: ' . $e);
            }
        } else {
            try {
                //Create new project
                $newProjectObj = $projectTable->save($projectObj);
                $info .= "Create Project successful: " . $newProjectObj['project_id'];
                $projectId = $newProjectObj['project_id'];

                $votingTable = new Default_Model_DbTable_ProjectRating();

                $votingTable->delete('member_id = 0 AND project_id = ' . $projectId);
                //insert the old hive votings
                $votearray = array();
                $votearray['member_id'] = 0;
                $votearray['project_id'] = $projectId;
                $votearray['user_like'] = $project['scoregood'];
                $votearray['user_dislike'] = $project['scorebad'];
                $newVote = $votingTable->save($votearray);

                $ratingSum = $votingTable->fetchRating($projectId);
                $count_likes = $ratingSum['count_likes'];
                $count_dislikes = $ratingSum['count_dislikes'];
                $projectObj['count_likes'] = $count_likes;
                $projectObj['count_dislikes'] = $count_dislikes;

                //update project
                $updateCount = $projectTable->update($projectObj, "project_id = " . $projectId);

                //Add changelog
                if (isset($project['changelog']) && $project['changelog'] != '') {
                    $projectUpdatesTable = new Default_Model_ProjectUpdates();
                    $data = array();
                    $data['project_id'] = $projectId;
                    $data['member_id'] = $projectObj['member_id'];
                    $data['public'] = 1;
                    $data['text'] = $project['changelog'];
                    $data['created_at'] = $projectObj['created_at'];
                    $data['changed_at'] = $projectObj['changed_at'];
                    $data['source_id'] = 1;
                    $data['source_pk'] = $project['id'];

                    $projectUpdatesTable->save($data);
                }

                if (null == $newProjectObj || null == $newProjectObj['project_id']) {
                    throw new Exception(__FUNCTION__ . '::ERROR save project: ' . implode(",", $newProjectObj));
                }
            } catch (Exception $e) {
                throw new Exception(__FUNCTION__ . '::ERROR save project: ' . $e);
            }
        }

        return $projectId;
    }

    private function uploadFilesAndLinks($project, $projectId)
    {
        $_import_file_counter = 0;
        //First real files
        $file1 = null;
        $info = '';

        //Clean up old collection data
        $pploadApi = new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));

        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($projectId)->current();
        $oldFiles = array();

        if ($projectData->ppload_collection_id) {
            $param = array();
            $param['collection_id'] = $projectData->ppload_collection_id;
            $oldFiles = $pploadApi->getFiles($param);

            $pploadApi->deleteCollection($projectData->ppload_collection_id);
            $projectData->ppload_collection_id = null;
            $projectTable->save($projectData->toArray());
        }

        if ($project['downloadtyp1'] == 0) {
            //a real file
            $file1 = $project['download1'];
            //$file1 = str_replace(' ', '%20', $file1);
            $pploadError = null;
            if (!empty($file1)) {
                try {
                    $downloadCounter = 0;
                    if (isset($oldFiles->files)) {
                        foreach ($oldFiles->files as $oldFile) {
                            $filename = $this->getFilenameFromUrl($this->_HIVE_BASE_URL . '/CONTENT/content-files/' . $file1);
                            //var_dump('check file: '. $oldFile->name . ' AND ' . $filename);
                            if ($oldFile->name == $filename) {
                                $downloadCounter = $oldFile->downloaded_count;
                            }
                        }
                    }

                    //$uploadFileResult = $this->uploadFileToPpload($projectId, 'http://cp1.hive01.com/CONTENT/content-files/'.$file1);
                    $uploadFileResult = $this->saveFileInPpload($projectId, $project['downloadname1'], $project['licensetype'],
                        base64_encode($project['license']), $downloadCounter,
                        $this->_HIVE_BASE_URL . '/CONTENT/content-files/' . $file1);
                    $info .= "Upload file successful: " . $uploadFileResult;
                    if ($uploadFileResult == true) {
                        $_import_file_counter++;
                    } else {
                        throw new Exception(__FUNCTION__ . '::ERROR Upload file: ' . $uploadFileResult);
                    }
                } catch (Exception $e) {
                    throw new Exception(__FUNCTION__ . '::ERROR Upload file: ' . $e);
                }
            }
        } else {
            //a link
            try {
                $link1 = $project['downloadlink1'];
                if ($link1 != 'http://' && !empty($link1)) {
                    $link1 = urlencode($link1);
                    $linkName1 = $project['downloadname1'];
                    if (empty($linkName1)) {
                        $linkName1 = "link";
                    }
                    $downloadCounter = 0;
                    $uploadFileResult = $this->saveFileInPpload($projectId, $project['downloadname1'], $project['licensetype'],
                        base64_encode($project['license']), 0, $this->_HIVE_BASE_URL . '/CONTENT/content-files/link', $link1,
                        $linkName1);
                    $info .= "Upload file successful: " . $uploadFileResult;
                    if ($uploadFileResult == true) {
                        $_import_file_counter++;
                    }
                }
            } catch (Exception $e) {
                throw new Exception(__FUNCTION__ . '::ERROR Upload file: ' . $e);
            }
        }

        //Then links...
        for ($i = 2; $i <= 12; $i++) {
            try {
                $link1 = $project['downloadlink' . $i];
                if ($link1 != 'http://' && !empty($link1)) {
                    $link1 = urlencode($link1);
                    $linkName1 = $project['downloadname' . $i];
                    if (empty($linkName1)) {
                        $linkName1 = "link";
                    }
                    $downloadCounter = 0;
                    $uploadFileResult = $this->saveFileInPpload($projectId, $project['downloadname' . $i], $project['licensetype'],
                        base64_encode($project['license']), 0, $this->_HIVE_BASE_URL . '/CONTENT/content-files/link', $link1,
                        $linkName1);
                    $info .= "Upload file successful: " . $link1;
                    if ($uploadFileResult == true) {
                        $_import_file_counter++;
                    }
                }
            } catch (Exception $e) {
                //throw new Exception(__FUNCTION__ . '::ERROR Upload file: ' . $e);
            }
        }

        if ($_import_file_counter == 0) {
            return $info;
        }

        return $_import_file_counter;
    }

    private function getFilenameFromUrl($url)
    {
        $x = pathinfo($url);
        $fileName = $x['basename'];

        return $fileName;
    }

    private function saveFileInPpload(
        $projectId,
        $fileDescription,
        $licensetype,
        $license,
        $downloads,
        $fileUrl,
        $link = null,
        $linkName = null
    ) {
        $pploadInfo = "Start upload file " . $fileUrl . " for project " . $projectId;

        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($projectId)->current();

        if ($projectData) {
            $pploadInfo .= "Project found! ProjectId: " . $projectData->project_id . ", MemberId: " . $projectData->member_id;
        } else {
            $pploadInfo .= "ERROR::Project not found: " . $projectId;
            throw new Exception($pploadInfo);

            return false;
        }

        $filename = null;
        if (!empty($link)) {
            //take emtpy dummy file
            $filename = $this->getFilenameFromUrl($fileUrl);
            $tmpFilepath = "/hive/H01/CONTENT/content-files/link";
            $tmpFilename = $linkName;
        } else {
            //upload to ocs-www
            $filename = $this->getFilenameFromUrl($fileUrl);
            $tmpFilepath = "/hive/H01/CONTENT/content-files/" . $filename;
            $tmpFilename = $filename;
            if (!empty($filename)) {
                $pploadInfo .= "FileName found: " . $filename;
            } else {
                $pploadInfo .= "ERROR::FileName not found: " . $filename;
                throw new Exception($pploadInfo);

                return false;
            }
        }

        $pploadApi = new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));

        $fileRequest = array(
            'local_file_path' => $tmpFilepath,
            'local_file_name' => $tmpFilename,
            'owner_id'        => $projectData->member_id
        );
        if ($projectData->ppload_collection_id) {
            // Append to existing collection
            $fileRequest['collection_id'] = $projectData->ppload_collection_id;
        }
        if (!empty($fileDescription)) {
            $fileRequest['description'] = mb_substr($fileDescription, 0, 140);
        }
        if (!empty($downloads)) {
            $fileRequest['downloaded_count'] = $downloads;
        }
        $tags = '';
        if (!empty($link)) {
            $tags .= "link##" . $link . ',';
        }
        if (!empty($licensetype)) {
            $tags .= "licensetype-" . $licensetype . ',';
        }
        if (!empty($license)) {
            $tags .= "license##" . $license . ',';
        }
        if (!empty($tags)) {
            $fileRequest['tags'] = $tags;
        }

        //upload to ppload
        $fileResponse = $pploadApi->postFile($fileRequest);

        if (!empty($fileResponse)) {
            $pploadInfo .= "File uploaded to ppload! ";
        } else {
            $pploadInfo .= "ERROR::File NOT uploaded to ppload! Response: " . $fileResponse;
            throw new Exception($pploadInfo);

            return $pploadInfo;
        }

        //delete tmpFile
        //unlink($tmpFilename);

        //unlink($tmpFilename);

        if (!empty($fileResponse->file->collection_id)) {
            $pploadInfo .= "CollectionId: " . $fileResponse->file->collection_id;
            if (!$projectData->ppload_collection_id) {
                // Save collection ID
                $projectData->ppload_collection_id = $fileResponse->file->collection_id;
                //$projectData->changed_at = new Zend_Db_Expr('NOW()');
                $projectData->save();

                // Update profile information
                $memberTable = new Default_Model_DbTable_Member();
                $memberSettings = $memberTable->find($projectData->member_id)->current();
                $mainproject = $projectTable->find($memberSettings->main_project_id)->current();
                $profileName = '';
                if ($memberSettings->firstname
                    || $memberSettings->lastname) {
                    $profileName = trim($memberSettings->firstname . ' ' . $memberSettings->lastname);
                } else {
                    if ($memberSettings->username) {
                        $profileName = $memberSettings->username;
                    }
                }
                $profileRequest = array(
                    'owner_id'    => $projectData->member_id,
                    'name'        => $profileName,
                    'email'       => $memberSettings->mail,
                    'homepage'    => $memberSettings->link_website,
                    'description' => $mainproject->description
                );
                $profileResponse = $pploadApi->postProfile($profileRequest);
                // Update collection information
                $collectionCategory = $projectData->project_category_id;
                if (Default_Model_Project::PROJECT_ACTIVE == $projectData->status) {
                    $collectionCategory .= '-published';
                }
                $collectionRequest = array(
                    'title'       => $projectData->title,
                    'description' => $projectData->description,
                    'category'    => $collectionCategory
                );
                $collectionResponse = $pploadApi->putCollection($projectData->ppload_collection_id, $collectionRequest);
                // Store product image as collection thumbnail
                $this->_updatePploadMediaCollectionthumbnail($projectData);
            }

            //return $fileResponse->file;
            return true;
        } else {
            //return false;
            $pploadInfo .= "ERROR::No CollectionId in ppload-file! Response Status: " . json_encode($fileResponse);
            throw new Exception($pploadInfo);

            return $pploadInfo;
        }

        return $pploadInfo;
    }

    private function _updatePploadMediaCollectionthumbnail($projectData)
    {
        if (empty($projectData->ppload_collection_id)
            || empty($projectData->image_small)) {
            return false;
        }

        $pploadApi = new Ppload_Api(array(
            'apiUri'   => "https://dl.opendesktop.org/api/",
            'clientId' => "1387085484",
            'secret'   => "34gtd3w024deece710e1225d7bfe5e7337b1f45d"
        ));

        $filename = sys_get_temp_dir() . '/' . $projectData->image_small;
        if (false === file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        /**
         * $viewHelperImage = new Default_View_Helper_Image();
         * $uri = $viewHelperImage->Image(
         * $projectData->image_small,
         * array(
         * 'width' => 600,
         * 'height' => 600
         * )
         * );**/
        $uri = $this->_OCS_CN_FILE_SYNC_URL . '/cache/600x600/img' . $projectData->image_small;

        file_put_contents($filename, file_get_contents($uri));

        $mediaCollectionthumbnailResponse =
            $pploadApi->postMediaCollectionthumbnail($projectData->ppload_collection_id, array('file' => $filename));

        unlink($filename);

        if (isset($mediaCollectionthumbnailResponse->status)
            && $mediaCollectionthumbnailResponse->status == 'success') {
            return true;
        }

        return false;
    }

    private function getPreviewPicture($hiveProjectId, $previewNum, $hivePreviewFileExtension)
    {
        $imageModel = new Default_Model_DbTable_Image();

        $config = Zend_Registry::get('config');

        $fileName = $hiveProjectId . '-' . $previewNum . '.' . $hivePreviewFileExtension;
        $cnFileUrl = '/hive/content-pre' . $previewNum . '/' . $fileName;

        /**
         * if ($this->check_img($cnFileUrl)) {
         * if($hivePreviewFileExtension == 'gif') {
         * $cnFileUrl = $config->images->media->server.'/img/hive/content-pre'.$previewNum.'/'.$fileName;
         * }
         * }
         **/
        return $cnFileUrl;
    }

    public function startImportAjaxAction()
    {
        global $_import_counter;
        global $_import_file_counter;
        global $_is_import_done;

        $_import_counter = 0;
        $_import_file_counter = 0;
        $_is_import_done = false;

        $this->_helper->layout->disableLayout();
        $params = $this->getAllParams();
        $cat_id = $params['cat_id'];
        $ocs_cat_id = $params['ocs_cat_id'];

        $info = null;

        $importGalleryPics = false;
        if (isset($params['import_previews'])) {
            $importGalleryPics = true;
        }
        $info .= " - With Gallery Pics? " . $importGalleryPics;

        $importFiles = false;
        if (isset($params['import_files'])) {
            $importFiles = true;
        }
        $info .= " - With Files? " . $importFiles;

        $startIndex = null;
        $limit = intval($params['limit']);
        if (empty($startIndex)) {
            $startIndex = 0;
        }
        if (empty($limit)) {
            $limit = 5;
        }

        $result = array();
        $contentTable = new Default_Model_DbTable_HiveContent();
        $memberTable = new Default_Model_Member();
        $projectTable = new Default_Model_Project();
        $hiveMemeberTable = new Default_Model_DbTable_HiveUser();
        
        try {
            $projects = $contentTable->fetchAllProjectsForCategory($cat_id, $startIndex, $limit);

            foreach ($projects as $project) {
                $_import_counter++;

                $info .= " ## Poject: id = " . $project['id'] . ", name = " . $project['name'] . "  ";
                $start = microtime(true);

                //1. Download/Upload Project-Picture
                //$info .= " - Project-Picture ";
                //$start = microtime(true);
                //$cnFilePath = $this->uploadProjectPicture($project['id']);
                $cnFilePath = $this->getProjectPicture($project['id']);
                //$time_elapsed_secs = microtime(true) - $start;
                //$info .= $time_elapsed_secs." secs";

                //2. Create ocs Project
                //$info .= " - Project ";
                //$start = microtime(true);
                try {
                    $hiveUser = $hiveMemeberTable->fetchRow('user = ' . $project['user']);
                    if(!empty($hiveUser)) {
                        $projectId = $this->createUpdateOcsProjects($hiveUser, $project, $ocs_cat_id, $cnFilePath);
                    }
                } catch (Exception $e) {
                    //Error: log error and go on
                    $error = array();
                    $error['import_error'] = $e;
                    $error['is_imported'] = 1;
                    $contentTable->update($error, 'id = ' . $project['id']);
                }
                //$time_elapsed_secs = microtime(true) - $start;
                //$info .= $time_elapsed_secs." secs";

                if ($projectId) {

                    //3. Upload files
                    if ($importFiles) {
                        $pploadError = null;
                        $info .= " - Files ";
                        $start = microtime(true);
                        try {
                            $_import_file_counter = $this->uploadFilesAndLinks($project, $projectId);
                        } catch (Exception $e) {
                            $pploadError .= $e;
                        }
                        $info .= " - Files Uploaded: " . $_import_file_counter . " -  ";
                        $time_elapsed_secs = microtime(true) - $start;
                        $info .= $time_elapsed_secs . " secs";
                    } else {
                        $_import_file_counter = 1;
                    }
                    if ($_import_file_counter > 0) {

                        //4. Gallery Pics
                        if ($importGalleryPics) {

                            $info .= " - Gallery Pics ";
                            //$start = microtime(true);
                            $previewPicsArray = array();
                            if (!empty($project['preview1'])) {
                                //$cnFilePathPre = $this->uploadPreviewPicture($project['id'], 1, $project['preview1']);
                                $cnFilePathPre = $this->getPreviewPicture($project['id'], 1, $project['preview1']);
                                //add preview pic to ocs-project
                                if (!empty($cnFilePathPre)) {
                                    $previewPicsArray[] = $cnFilePathPre;
                                    $info .= " - PreviewPic1 ";
                                }
                            }
                            if (!empty($project['preview2'])) {
                                //$cnFilePathPre = $this->uploadPreviewPicture($project['id'], 2, $project['preview2']);
                                $cnFilePathPre = $this->getPreviewPicture($project['id'], 2, $project['preview2']);
                                if (!empty($cnFilePathPre)) {
                                    $previewPicsArray[] = $cnFilePathPre;
                                    $info .= " - PreviewPic2 ";
                                }
                            }
                            if (!empty($project['preview3'])) {
                                //$cnFilePathPre = $this->uploadPreviewPicture($project['id'], 3, $project['preview3']);
                                $cnFilePathPre = $this->getPreviewPicture($project['id'], 3, $project['preview3']);
                                if (!empty($cnFilePathPre)) {
                                    $previewPicsArray[] = $cnFilePathPre;
                                    $info .= " - PreviewPic3 ";
                                }
                            }
                            if (!empty($previewPicsArray)) {
                                $projectTable->updateGalleryPictures($projectId, $previewPicsArray);
                            }
                        }
                        //$time_elapsed_secs = microtime(true) - $start;
                        //$info .= $time_elapsed_secs." secs";

                        //5. Mark project as imported
                        //$info .= " - Mark project as imported ";
                        //$start = microtime(true);
                        $contentTable->update(array("is_imported" => 1), "id = " . $project['id']);
                        //$time_elapsed_secs = microtime(true) - $start;
                        //$info .= $time_elapsed_secs." secs";

                    } else {
                        $info .= " - NO Files Uploaded";
                        $contentTable->update(array(
                            "is_imported"  => 1,
                            "import_error" => "Error on fileupload to cc.ppload.com Exception: " . $pploadError
                        ), "id = " . $project['id']);
                    }
                } else {
                    $info .= " - Project NOT created! ";
                }

                $time_elapsed_secs = microtime(true) - $start;
                $info .= $time_elapsed_secs . " secs";
                $info .= " - Done... ";
            }
            $result['Message'] = $info;
            $_is_import_done = true;
        } catch (Exception $e) {
            $this->view->info = $e->getMessage();
            $_is_import_done = true;

            $result['Result'] = self::RESULT_ERROR;
            $result['Message'] = "Fehler bei laden aus DB:" . $e->getMessage();
        }

        $count = $contentTable->fetchCountProjectsForCategory($cat_id);

        $result['Result'] = self::RESULT_OK;
        $result['IsImportDone'] = $_is_import_done;
        $result['TotalCounter'] = $count;
        $result['ImportCounter'] = $_import_counter;
        $result['ImportFileCounter'] = $_import_file_counter;
        $result['limit'] = $limit;
        $result['offset'] = $startIndex + $limit;

        $this->_helper->json($result);
    }

    public function importStatusAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $cat_id = (int)$this->getParam('cat_id');
        $contentTable = new Default_Model_DbTable_HiveContent();
        $countAll = $contentTable->fetchCountAllProjectsForCategory($cat_id);
        $countImported = $contentTable->fetchCountProjectsForCategory($cat_id);

        $result = array();
        $result['Result'] = self::RESULT_OK;
        $result['IsImportDone'] = ($countAll == $countImported);
        $result['ImportCounter'] = $countImported;
        $result['ProjectCounter'] = $countAll;

        $this->_helper->json($result);
    }

    private function saveImageOnMediaServer($filePathName, $fileExtention, $content_type)
    {
        if (empty($filePathName)) {
            throw new Exception(__FUNCTION__ . "ERROR::No image path");
        }
        $srcPathOnMediaServer = $this->sendImageToMediaServer($filePathName, $content_type);
        if (!$srcPathOnMediaServer) {
            throw new Exception("Error in upload to CDN-Server. \n Server message:\n" . $this->_errorMsg);
        }

        return $srcPathOnMediaServer;
    }

    protected function sendImageToMediaServer($fullFilePath, $mimeType)
    {
        $config = Zend_Registry::get('config');
        $url = $config->images->media->upload;

        $client = new Zend_Http_Client($url);
        $client->setFileUpload($fullFilePath, basename($fullFilePath), null, $mimeType);
        $response = $client->request('POST');

        if ($response->getStatus() > 200) {
            $this->_errorMsg = $response->getBody();

            return null;
        }

        return $response->getBody();
    }

    private function uploadFileToPpload($projectId, $fileUrl, $link = null, $linkName = null)
    {
        $pploadInfo = "Start upload file " . $fileUrl . " for project " . $projectId;

        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($projectId)->current();

        if ($projectData) {
            $pploadInfo .= "Project found! ProjectId: " . $projectData->project_id . ", MemberId: " . $projectData->member_id;
        } else {
            $pploadInfo .= "ERROR::Project not found: " . $projectId;
            throw new Exception($pploadInfo);

            return false;
        }

        //upload to ocs-www
        $file = $this->file_get_contents_curl($fileUrl);
        if ($file) {
            $pploadInfo .= "File found!";
        } else {
            $pploadInfo .= "ERROR::File not found: " . $fileUrl;
            throw new Exception($pploadInfo);

            return false;
        }
        $filename = null;
        if (!empty($link)) {
            //take emtpy dummy file
            $filename = $linkName;
        } else {
            //upload to ocs-www
            $filename = $this->getFilenameFromUrl($fileUrl);
            if (!empty($filename)) {
                $pploadInfo .= "FileName found: " . $filename;
            } else {
                $pploadInfo .= "ERROR::FileName not found: " . $filename;
                throw new Exception($pploadInfo);

                return false;
            }
        }
        file_put_contents(IMAGES_UPLOAD_PATH . 'tmp/' . $filename, $file);

        $tmpFilename = IMAGES_UPLOAD_PATH . 'tmp/' . $filename;

        $mime = mime_content_type($tmpFilename);
        if (empty($mime) || $mime == 'text/html') {
            $pploadInfo .= "ERROR::File NOT found!";
            throw new Exception($pploadInfo);

            return false;
        }

        if (file_exists($tmpFilename)) {
            $pploadInfo .= "File uploaded to ocs-www!";
        } else {
            $pploadInfo .= "ERROR::File NOT uploaded to ocs-www!";
            throw new Exception($pploadInfo);

            return false;
        }

        $pploadApi = new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));

        $fileRequest = array(
            'file'     => $tmpFilename,
            'owner_id' => $projectData->member_id
        );
        if ($projectData->ppload_collection_id) {
            // Append to existing collection
            $fileRequest['collection_id'] = $projectData->ppload_collection_id;
        }
        //if (isset($fileDescription)) {
        //	$fileRequest['description'] = mb_substr($fileDescription, 0, 140);
        //}
        if (!empty($link)) {
            $fileRequest['tags'] = "link##" . $link;
        }

        //upload to ppload
        $fileResponse = $pploadApi->postFile($fileRequest);

        if (!empty($fileResponse)) {
            $pploadInfo .= "File uploaded to ppload! ";
        } else {
            $pploadInfo .= "ERROR::File NOT uploaded to ppload! Response: " . $fileResponse;
            throw new Exception($pploadInfo);

            return $pploadInfo;
        }

        //delete tmpFile
        unlink($tmpFilename);

        //unlink($tmpFilename);

        if (!empty($fileResponse->file->collection_id)) {
            $pploadInfo .= "CollectionId: " . $fileResponse->file->collection_id;
            if (!$projectData->ppload_collection_id) {
                // Save collection ID
                $projectData->ppload_collection_id = $fileResponse->file->collection_id;
                //$projectData->changed_at = new Zend_Db_Expr('NOW()');
                $projectData->save();

                // Update profile information
                $memberTable = new Default_Model_DbTable_Member();
                $memberSettings = $memberTable->find($projectData->member_id)->current();
                $mainproject = $projectTable->find($memberSettings->main_project_id)->current();
                $profileName = '';
                if ($memberSettings->firstname
                    || $memberSettings->lastname) {
                    $profileName = trim($memberSettings->firstname . ' ' . $memberSettings->lastname);
                } else {
                    if ($memberSettings->username) {
                        $profileName = $memberSettings->username;
                    }
                }
                $profileRequest = array(
                    'owner_id'    => $projectData->member_id,
                    'name'        => $profileName,
                    'email'       => $memberSettings->mail,
                    'homepage'    => $memberSettings->link_website,
                    'description' => $mainproject->description
                );
                $profileResponse = $pploadApi->postProfile($profileRequest);
                // Update collection information
                $collectionCategory = $projectData->project_category_id;
                if (Default_Model_Project::PROJECT_ACTIVE == $projectData->status) {
                    $collectionCategory .= '-published';
                }
                $collectionRequest = array(
                    'title'       => $projectData->title,
                    'description' => $projectData->description,
                    'category'    => $collectionCategory
                );
                $collectionResponse = $pploadApi->putCollection($projectData->ppload_collection_id, $collectionRequest);
                // Store product image as collection thumbnail
                $this->_updatePploadMediaCollectionthumbnail($projectData);
            }

            //return $fileResponse->file;
            return true;
        } else {
            //return false;
            $pploadInfo .= "ERROR::No CollectionId in ppload-file! Response Status: " . json_encode($fileResponse);
            throw new Exception($pploadInfo);

            return $pploadInfo;
        }

        return $pploadInfo;
    }

    private function file_get_contents_curl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}
