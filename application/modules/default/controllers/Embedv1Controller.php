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

class Embedv1Controller extends Zend_Controller_Action
{

    protected $_format = 'json';
    protected $_params = array();

    public function init()
    {
        parent::init();
        $this->initView();
        $this->_initResponseHeader();
    }

    public function initView()
    {
        // Disable render view
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * @throws Zend_Exception

    protected function _initRequestParamsAndFormat()
    {
        // Set request parameters
        switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
            case 'GET':
                $this->_params = $_GET;
                break;
            case 'PUT':
                parse_str(file_get_contents('php://input'), $_PUT);
                $this->_params = $_PUT;
                break;
            case 'POST':
                $this->_params = $_POST;
                break;
            default:
                Zend_Registry::get('logger')->err(
                    __METHOD__ . ' - request method not supported - '
                    . $_SERVER['REQUEST_METHOD']
                );
                exit('request method not supported');
        }

        // Set format option
        if (isset($this->_params['format'])
            && strtolower($this->_params['format']) == 'json'
        ) {
            $this->_format = 'json';
        }
    }
     */


    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()
            ->setHeader('X-FRAME-OPTIONS', 'ALLOWALL', true)
//            ->setHeader('Last-Modified', $modifiedTime, true)
            ->setHeader('Expires', $expires, true)
            ->setHeader('Pragma', 'cache', true)
            ->setHeader('Cache-Control', 'max-age=1800, public', true);
    }

    public function indexAction()
    {
        $this->_sendErrorResponse(999, 'unknown request');
    }

    protected function _sendErrorResponse($statuscode, $message = '')
    {
        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'failed',
                'statuscode' => $statuscode,
                'message'    => $message
            );
        }
        $this->_sendResponse($response, $this->_format);
    }

    protected function _sendResponse($response, $format = 'json', $xmlRootTag = 'ocs')
    {
        header('Pragma: public');
        header('Cache-Control: cache, must-revalidate');
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";
        header('Expires: ' . $expires);
        $callback = $this->getParam('callback');
        if ($callback != "")
        {
            header('Content-Type: text/javascript; charset=UTF-8');
            // strip all non alphanumeric elements from callback
            $callback = preg_replace('/[^a-zA-Z0-9_]/', '', $callback);
            echo $callback. '('. json_encode($response). ')';
        }else{
             header('Content-Type: application/json; charset=UTF-8');
             echo json_encode($response);
        }
        exit;
    }


    public function projectdetailAction(){

        $product = $this->_getProject($this->getParam('projectid'));

        $html = '';
        $html = $this->_getHTMLProjectDetail($product);
        $response = array(
            'status'     => 'ok',
            'statuscode' => 100,
            'message'    => '',
            'data'       => array(),
            'html'      =>''
        );
        if (!empty($product)) {
            $response['data'] = $product;
        }
        $response['html'] = $html;
        $this->_sendResponse($response, $this->_format);
    }

    protected function _getHTMLProjectDetail($project)
    {
        $helperImage = new Default_View_Helper_Image();
        $helperPrintDate = new Default_View_Helper_PrintDate();
        $printRating= new Default_View_Helper_PrintRatingWidgetSimple();
        $html = '';
        $html = $html.'<div class="opendesktopwidget-main-detail-container-body-header">';

        $html = $html.'<div class="opendesktopwidget-img-member"><img src="'.$helperImage->Image($project['profile_image_url'], array('width' => 85, 'height' => 85)).'" /></div>';
        $html = $html.'<div class="opendesktopwidget-description">'.$project['title'];
        $html = $html.'<span class="opendesktopwidget-category">'.$project['cat_title'];
        $html = $html.'</span>';
        $html = $html.'</div>';

        $html = $html.'<div class="opendesktopwidget-rating">';
        $html = $html.$printRating->printRatingWidgetSimple($project['laplace_score'],$project['count_likes'],$project['count_dislikes']);
        $html = $html.'</div>';

        $html = $html.'</div> <!-- end of opendesktopwidget-main-detail-container-body-header -->';


        $html = $html.'<div class="opendesktopwidget-main-detail-container-body-content">';

       // carousels
        if(count($project['pics'])>0){
            $html = $html.'<div class="opendesktopwidget-imgs">';
            $html = $html.'<div id="opendesktopwidget-main-detail-carousel" data-simple-slider>';
            //$html = $html.'<img src="'.$helperImage->Image($project['pics'][0], array('height' => '600')).'" />';
            foreach ($project['pics'] as  $pic) {
                $html = $html.'<div><img src="'.$helperImage->Image($pic, array('width' => '621', 'height' => '621')).'" /></div>';
            }

            $html = $html.'</div>';
            /*
            if(count($project['pics'])>1){
                $html = $html.'<button class="prev opendesktop-widget-btn"><i class="fa fa-chevron-left opendesktop-navi" aria-hidden="true"></i></button>';
                $html = $html.'<button class="next opendesktop-widget-btn"><i class="fa fa-chevron-right opendesktop-navi" aria-hidden="true"></i></button>';
            }
            */
            $html = $html.'</div>';
        }


        // begin opendesktopwidget-content
        $html = $html.'<div class="opendesktopwidget-content">';
        $html = $html.'<div id="opendesktopwidget-content-tabs" class="pling-nav-tabs">';
        $html = $html.'<ul>';
        $html = $html.'<li class="active"><a data-wiget-target="#opendesktopwidget-content-description" data-toggle="tab">Product</a></li>';
        if($project['files'] && count($project['files'])>0){
            $html = $html.'<li><a data-wiget-target="#opendesktopwidget-content-files" data-toggle="tab">Files ('.count($project['files']).')</a></li>';
        }
        if($project['changelogs'] && count($project['changelogs'])>0){
             $html = $html.'<li><a data-wiget-target="#opendesktopwidget-content-changelogs" data-toggle="tab">Changelogs ('.count($project['changelogs']).')</a></li>';
        }
        if($project['reviews'] && count($project['reviews'])>0){
             $html = $html.'<li><a data-wiget-target="#opendesktopwidget-content-reviews" data-toggle="tab">Reviews ('.count($project['reviews']).')</a></li>';
        }
        $html = $html.'</ul>';
        $html = $html.'</div>';

        // begin opendesktopwidget-tab-pane-content
        $html = $html.'<div class="opendesktopwidget-tab-pane-content">';

        $html = $html.'<div id="opendesktopwidget-content-description" class="opendesktopwidget-tab-pane  active">';
        $html = $html.'<span class="description"> Description</span>';
        $html = $html.$project['description'];
        if($project['lastchangelog']){
            $html = $html.'<span class="description"> Last change log</span>';
            $html = $html.'<span class="title"> '.$project['lastchangelog']['title'].'</span>';
            $html = $html.'<span class="updatetime">'. $helperPrintDate->printDate($project['lastchangelog']['created_at']).'</span>';
            $html = $html.'<span class="text"> '.$project['lastchangelog']['text'].'</span>';
        }

        // comments begin
        $html = $html.'<div class="opendesktopwidgetcomments">';
        $html_comment = $this->_getHTMLPagerComments($project['comments'])
                    .'<div id="opendesktopwidget-main-container-comments">'
                    .$this->_getHTMLComments($project['comments'])
                    .'</div>';
        $html = $html.$html_comment;
        $html = $html.'</div>';
        // comments end

        $html = $html.'</div>';         // end opendesktopwidget-content-description

        // begin opendesktopwidget-content-files
        $html = $html.'<div id="opendesktopwidget-content-files" class="opendesktopwidget-tab-pane">';
        $html = $html.$this->_getHTMLFiles($project['files']);
        $html = $html.'</div>';
        // end opendesktopwidget-content-files

        // begin opendesktopwidget-content-changelogs
        $html = $html.'<div id="opendesktopwidget-content-changelogs" class="opendesktopwidget-tab-pane">';
        $html = $html.$this->_getHTMLChangelogs($project['changelogs']);
        $html = $html.'</div>';
        // end opendesktopwidget-content-changelogs

         // begin opendesktopwidget-content-reviews
        $html = $html.'<div id="opendesktopwidget-content-reviews" class="opendesktopwidget-tab-pane">';
        $html = $html.$this->_getHTMLReviews($project['reviews']);
        $html = $html.'</div>';
        // end opendesktopwidget-content-reviews


        // end opendesktopwidget-tab-pane-content
        $html = $html.'</div>';


        $html = $html.'</div>';    //opendesktopwidget-main-detail-container-body-content

        $html = $html.'</div>'; // end opendesktopwidget-content
        // end opendesktopwidget-content


        return $html;
    }

    protected function _getHTMLChangelogs($logs)
    {
        $helperPrintDate = new Default_View_Helper_PrintDate();
        $html = '<div id="opendesktopwidget-changelogs">';
        foreach ($logs as $log) {
              $html = $html.'<div class="opendesktopwidget-changelogs-title"><span class="opendesktopwidget-changelogs-title-1">'.$log['title'].'</span>';
              $html = $html.'<span class="opendesktopwidget-changelogs-title-2">'.$helperPrintDate->printDate($log['created_at']).'</span>';
              $html = $html.'</div>';
              $html = $html.'<span class="opendesktopwidget-changelogs-text">'.$log['text'].'</span>';
        }
        $html = $html.'</div>';
        return $html;
    }

    protected function _getHTMLReviews($reviews)
    {

        $helperImage = new Default_View_Helper_Image();
        $helperPrintDate = new Default_View_Helper_PrintDate();

        $cntActive = 0;
        $cntLikes = 0;
        $cntDislike = 0;
        $cntAll = count($reviews);
         foreach ($reviews as $review) {
            if($review['rating_active']==1) {
                    $cntActive =$cntActive+1;
                    $cntLikes = $cntLikes + $review['user_like'];
                    $cntDislike = $cntDislike + $review['user_dislike'];
            }
         }

         $html = '<div id="opendesktopwidget-reviews">';

         $html = $html.'<div class="opendesktopwidget-reviews-filters">';
         $html = $html.'<button id="opendesktopwidget-reviews-filters-hates" class="opendesktop-widget-btn opendesktop-widget-reviews-filters-btn">Show '
                        .'<i class="fa fa-thumbs-o-down" aria-hidden="true" style="color:red"></i> ('.$cntDislike.')</button>';
         $html = $html.'<button id="opendesktopwidget-reviews-filters-likes" class="opendesktop-widget-btn opendesktop-widget-reviews-filters-btn">Show '
                        .'<i class="fa fa-thumbs-o-up" aria-hidden="true" style="color:green"></i> ('.$cntLikes.')</button>';
         $html = $html.'<button id="opendesktopwidget-reviews-filters-active" class="opendesktop-widget-btn opendesktop-widget-reviews-filters-btn opendesktopwidget-reviews-activeRating">Show Active Reviews ('.$cntActive.')</button>';
         $html = $html.'<button id="opendesktopwidget-reviews-filters-all" class="opendesktop-widget-btn opendesktop-widget-reviews-filters-btn">Show all Reviews ('.$cntAll.')</button>';
         $html = $html.'</div>';


        foreach ($reviews as $review) {
             $clsActive = '';
             $clsLike = '';
             if($review['rating_active']==0){
                $clsActive ='opendesktopwidget-reviews-rows-inactive ';
             }else{
                $clsActive ='opendesktopwidget-reviews-rows-active ';
             }
             if($review['user_like']==1){
                $clsLike ='opendesktopwidget-reviews-rows-clsUpvotes ';
             }else{
                $clsLike ='opendesktopwidget-reviews-rows-clsDownvotes ';
             }

             $html = $html.'<div class="opendesktopwidget-reviews-rows '.$clsActive.$clsLike.'">';
              $html = $html.'<div class="opendesktopwidget-reviews-title">';
              $html = $html.'<img class="opendesktopwidget-reviews-userimg" src="'.$helperImage->Image($review['profile_image_url'], array('width' => 40, 'height' => 40)).'" />';
              $html = $html.'<span class="opendesktopwidget-reviews-title-1">'.$review['username'].'</span>';
              $html = $html.'<span class="opendesktopwidget-reviews-title-2">'.$helperPrintDate->printDate($review['created_at']).'</span>';
              if($review['user_like']==1){
                    $html = $html.'<i class="fa fa-thumbs-o-up opendesktopwidget-like" aria-hidden="true" ></i>';
              }else{
                    $html = $html.'<i class="fa fa-thumbs-o-down opendesktopwidget-dislike" aria-hidden="true" ></i>';
              }
              $html = $html.'</div>';
              $html = $html.'<span class="opendesktopwidget-reviews-text">'.$review['comment_text'].'</span>';
              $html = $html.'</div>';
        }
        $html = $html.'</div>';
        return $html;
    }

    protected function _getProject($project_id){
        $modelProduct = new Default_Model_Project();
        $project = $modelProduct->fetchProductInfo($project_id);
        if ($project==null) {
            $this->_sendErrorResponse(101, 'content not found');
        }

        $result = array();
        $result = array(
            'project_id'           => $project['project_id'],
            'member_id'           => $project['member_id'],
            'title'           => $project['title'],
            'description'           => $project['description'],
            'version'           => $project['version'],
            'project_category_id'         =>$project['project_category_id'],
            'project_created_at'         =>$project['project_created_at'],
            'project_changed_at' => $project['project_changed_at'],
            'laplace_score' => $project['laplace_score'],
            'ppload_collection_id' => $project['ppload_collection_id'],
            'image_small'    =>  $project['image_small'],
            'count_likes'    =>  $project['count_likes'],
            'count_dislikes'    =>  $project['count_dislikes'],
            'count_comments'    =>  $project['count_comments'],
            'cat_title'    =>  $project['cat_title'],
            'username'    =>  $project['username'],
            'profile_image_url'    =>  $project['profile_image_url'],
            'comments'  => array(),
            'files' =>array(),
            'lastchangelog'  => array(),
            'pics'  => array(),
            'changelogs'  => array(),
            'reviews'  => array()
        );

        // gallerypics
        $galleryPictureTable = new Default_Model_DbTable_ProjectGalleryPicture();
        $stmt = $galleryPictureTable->select()->where('project_id = ?', $project_id)->order(array('sequence'));
        $pics = array();
        foreach ($galleryPictureTable->fetchAll($stmt) as $pictureRow) {
            $pics[] = $pictureRow['picture_src'];
        }
        $result['pics'] = $pics;

        // changelogs
        $tableProjectUpdates = new Default_Model_ProjectUpdates();
        $updates = $tableProjectUpdates->fetchProjectUpdates($project_id);
        if (count($updates) > 0) {
             $logs = array();
             foreach ($updates as $update) {
                 $logs[] = array(
                    'title' => $update['title'],
                    'text' => $update['text'],
                    'created_at' => $update['created_at'],
                    );
             }
              $result['lastchangelog'] = $logs[0];
             $result['changelogs'] = $logs;
        }

        //reviews
        $tableProjectRatings = new Default_Model_DbTable_ProjectRating();
        $reviews = $tableProjectRatings->fetchRating($project_id);
        $r = array();
        foreach ($reviews as $review) {
            $r[] = array(
               'member_id' => $review['member_id'],
               'user_like' => $review['user_like'],
               'user_dislike' => $review['user_dislike'],
               'rating_active' => $review['rating_active'],
               'created_at' => $review['created_at'],
               'profile_image_url' => $review['profile_image_url'],
               'username' => $review['username'],
               'comment_text' => $review['comment_text']
               );
        }
        $result['reviews'] = $r;

        // comments
        $comments = $this->_getCommentsForProject($project_id);
        $result['comments'] = $comments;

        // pploadfiles
        $files = $this->_getPploadFiles($project['ppload_collection_id']);
        $result['files'] = $files;

       return $result;
    }

    public function commentsAction()
    {

        $project_id = $this->getParam('id');
        $page = $this->getParam('page');
        $nopage = $this->getParam('nopage');   // with param nopage will only show prudusts list otherwise show
        $pageLimit = $this->getParam('pagelimit');

        if(empty($project_id)){
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'totalitems' =>0,
                'html'      =>'',
                'data'       => array()
            );
        }else{

            if(empty($page)) $page=0;
            if(empty($pageLimit)) $pageLimit=10;
            $comments = $this->_getCommentsForProject($project_id,$page,$pageLimit);

            $commentsResult = $comments['result'];

            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'totalitems' => count($commentsResult),
                'data'       => array()
            );

            if (!empty($commentsResult)) {
                $response['data'] = $commentsResult;
                // create html
                if(empty($nopage)) {
                    //  init with comments & pager
                    $html = $this->_getHTMLPagerComments($comments)
                                .'<div id="opendesktopwidget-main-container-comments">'
                                .$this->_getHTMLComments($comments)
                                .'</div>';
                }else{
                    // for only ajax paging content
                    $html =$this->_getHTMLComments($comments);
                }
                $response['html'] =$html;
            }

        }

        $this->_sendResponse($response, $this->_format);


    }

    protected function _getHTMLComments($comments)
    {
        $commentslist = $comments['result'];
        $helperImage = new Default_View_Helper_Image();
        $helperBuildMemberUrl = new Default_View_Helper_BuildMemberUrl();
        $helperPrintDate = new Default_View_Helper_PrintDate();
        $html = '';
        foreach ($commentslist as $p) {
                $html = $html.'<div class="opendesktopwidgetcommentrow level'.$p['level'].'"  id="opendesktopwidgetcommentrow_'.$p['comment_id'].'">';

                $html = $html.'<img class="image_small" src="'.$helperImage->Image($p['profile_image_url'], array('width' => 60, 'height' => 60)).'" />';

                $html = $html.'<div class="opendesktopwidgetcommentrow-header">';
                $html = $html.'<span class="username">'.$p['username'].'</span>';
                $html = $html.'<span class="updatetime">'. $helperPrintDate->printDate($p['comment_created_at']).'</span>';
                $html = $html.'</div>';

                $html = $html.'<div class="opendesktopwidgetcomment_content">';


                $html = $html.'<div class="commenttext">'.$p['comment_text'].'</div>';

                $html = $html.'</div><div style="clear:both"/> ';
                $html = $html.'</div><!-- end of opendesktopwidgetcommentrow -->';
        }
        return $html;

    }


    protected function _getCommentsForProject($project_id,$curPage=1,$pageItemsCount=10)
    {

        $modelComments = new Default_Model_ProjectComments();
        $comments = $modelComments->getCommentTreeForProject($project_id);
        $comments->setItemCountPerPage($pageItemsCount);
        $comments->setCurrentPageNumber($curPage);

        $result = array();

        foreach ($comments as $comment) {

            $c = $comment['comment'];

            $result[] = array(
                'comment_id'   => $c['comment_id'],
                'member_id'    => $c['member_id'],
                'comment_text' =>  nl2br(Default_Model_HtmlPurify::purify($c['comment_text']),true),
                'level' => $comment['level'],
                'comment_type' => $c['comment_type'],
                'profile_image_url' => $c['profile_image_url'],
                'username' => $c['username'],
                'comment_target_id'=>$c['comment_target_id'],
                'comment_created_at' => $c['comment_created_at']
            );
        }

        $rlt = array(
                'totalItemCount' => $comments->getTotalItemCount(),
                'count'         => $comments->count(),
                'itemCountPerPage'=>$comments->getItemCountPerPage(),
                'result'     => $result
            );




        return $rlt;
    }


    public function memberprojectsAction()
    {
        $user_id = $this->getParam('memberid');
        $page = $this->getParam('page');
        $nopage = $this->getParam('nopage');   // with param nopage will only show prudusts list otherwise show member+pager+productlist
        $pageLimit = $this->getParam('pagelimit');
        $catids = $this->getParam('catids');

        if(empty($pageLimit)){
            $pageLimit = 10;
        }

         if(empty($catids)){
            $catids = null;
        }

        if(empty($page)){
            $page = 1;
        }
        if(empty($user_id)){
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'totalitems' =>0,
                'html'      =>'',
                'data'       => array()
            );
        }else{

            $userProducts = $this->_getMemberProducts($user_id, $pageLimit, $page,$catids);

            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'totalitems' => count($userProducts),
                'data'       => array()
            );
            if (!empty($userProducts)) {
                $response['data'] = $userProducts;
                // create html
                if(empty($nopage)) {
                    //  init with member & pager & products
                    $html = $this->_getHTMLMember($user_id)
                                .'<div id="opendesktopwidget-main">'
                                .$this->_getHTMLPager($user_id,$pageLimit,$page,$catids)
                                .'<div id="opendesktopwidget-main-container">'
                                .$this->_getHTMLProducts($userProducts)
                                .'</div>'
                                .'</div>';
                }else{
                    // for only ajax paging content
                    $html =$this->_getHTMLProducts($userProducts);
                }
                $response['html'] =$html;
            }

        }
        $this->_sendResponse($response, $this->_format);
    }

    protected function _getHTMLMember($user_id)
    {
        $html = '';
        $modelMember = new Default_Model_Member();
        $m = $modelMember->fetchMemberData($user_id);
        $helperImage = new Default_View_Helper_Image();
        $html = $html.'<div class="opendesktopwidgetheader">';
        $html = $html.'<a href="https://www.opendesktop.org" target="_blank"><img class="opendesktoplogo" src="https://www.opendesktop.org/images_sys/store_opendesktop/logo.png" /></a>';
        $html = $html.'<img class="profile_image" src="'.$helperImage->Image($m['profile_image_url'], array('width' => 110, 'height' => 110)).'" />';
        $html = $html.'</div> <!--end of header-->';
        return $html;

    }

    protected function _getHTMLProducts($userProducts)
    {
        $helperImage = new Default_View_Helper_Image();
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        $printRating= new Default_View_Helper_PrintRatingWidgetSimple();
        $helperPrintDate = new Default_View_Helper_PrintDate();
        $html = '';
        foreach ($userProducts as $p) {
                $html = $html.'<div class="opendesktopwidgetrow" id="opendesktopwidgetrow_'.$p['id']
                                        .' " data-project-id="'.$p['id']
                                        .' " data-ppload-collection-id="'.$p['ppload_collection_id'].'">';
                //$html = $html.'<a href="'.$this->_config['baseurl'].'p/'.$p['id'].'" target="_blank">';
                $html = $html.'<img class="image_small" src="'.$helperImage->Image($p['image_small'], array('width' => 167, 'height' => 167)).'" />';

                $html = $html.'<div class="description-container">';
                $html = $html.'<div class="description">';
                $html = $html.'<span class="title">'.$p['title'].'</span>';
                $html = $html.'<span class="version">'.$p['version'].'</span>';
                $html = $html.'<span class="cat_name">'.$p['cat_name'].'</span>';
                if($p['count_comments']>0){
                    $html = $html.'<span class="count_comments">'.$p['count_comments'].' comment' .($p['count_comments']>1?'s':'').'</span>';
                }
                $html = $html.'</div><!--end of description-->';

                $html = $html.'<div class="rating">';
                $html = $html.$printRating->printRatingWidgetSimple($p['laplace_score'],$p['count_likes'],$p['count_dislikes']);
                $html = $html.'<span class="updatetime">'. $helperPrintDate->printDate($p['changed']).'</span>';
                $html = $html.'</div>';

                $html = $html.'</div><!--end of description-container-->';
                //$html = $html.'</a><!--end of a-->';
                $html = $html.'</div> <!-- end of opendesktopwidgetrow -->';
        }
        return $html;
    }

    protected function _getHTMLPager($user_id,$pageLimit=10,$page=1,$catids=null)
    {
        $modelProject = new Default_Model_Project();
        $total_records = $modelProject->countAllProjectsForMemberCatFilter($user_id,true,$catids);
        $total_pages = ceil($total_records / $pageLimit);
        if($total_pages <=1) return '';
        $html = '<div class="opendesktopwidgetpager"><ul class="opendesktopwidgetpager">';
        for ($i=1; $i<=$total_pages; $i++) {
            if($i==$page){
                $html = $html.'<li class="active"><span>'.$i.'</span></li>';
            }else{
                $html = $html.'<li><span>'.$i.'</span></li>';
            }

        };
        $html = $html.'</ul></div>';
        return $html;
    }

    protected function _getHTMLPagerComments($comments)
    {

        $total_pages = $comments['count'];
        if($total_pages<=1) return '';
        $html = '<div class="opendesktopwidgetpager"><ul class="opendesktopwidgetpager">';
        for ($i=1; $i<=$total_pages; $i++) {
            if($i==1){
                $html = $html.'<li class="active"><span>'.$i.'</span></li>';
            }else{
                $html = $html.'<li><span>'.$i.'</span></li>';
            }
        };
        $html = $html.'</ul></div>';
        return $html;
    }



    protected function _getMemberProducts($user_id,$pageLimit=5,$page=1,$catids = null)
    {

       $modelProject = new Default_Model_Project();
        $userProjects = $modelProject->fetchAllProjectsForMemberCatFilter($user_id, $pageLimit,($page - 1) * $pageLimit, true,$catids);

        $result = array();
        foreach ($userProjects as $project) {
            $result[] = array(
                'id'           => $project['project_id'],
                'title'           => Default_Model_HtmlPurify::purify($project['title']),
                'desc'           => Default_Model_HtmlPurify::purify($project['description']),
                'version'           =>Default_Model_HtmlPurify::purify($project['version']),
                'cat_id'         =>$project['project_category_id'],
                'cat_name' => $project['catTitle'],
                'created'         =>$project['project_created_at'],
                'changed' => $project['project_changed_at'],
                'laplace_score' => $project['laplace_score'],
                'image_small'    =>  $project['image_small'] ,
                'count_dislikes' => $project['count_dislikes'],
                'count_likes' => $project['count_likes'],
                'count_comments'    =>  $project['count_comments'] ,
                'ppload_collection_id' =>  $project['ppload_collection_id']
            );

        }
        return $result;
    }


    protected function _getPploadFiles($ppload_collection_id)
    {
        $result = array();
        $pploadApi = new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));
        if ($ppload_collection_id) {
            $filesRequest = array(
                'collection_id' => $ppload_collection_id,
                'perpage'       => 100
            );

            $filesResponse = $pploadApi->getFiles($filesRequest);

            if (isset($filesResponse->status) && $filesResponse->status == 'success') {
                $i = 0;
                foreach ($filesResponse->files as $file) {
                    $downloadLink = PPLOAD_API_URI . 'files/download/' . 'id/' . $file->id . '/' . $file->name;
                    $payload = array('id' => $file->id);
                    $downloadLink = Default_Model_PpLoad::createDownloadUrlJwt($ppload_collection_id, $file->name, $payload);

                    $tags = $this->_parseFileTags($file->tags);
                    $p_type = $this->_getPackagetypeText($tags['packagetypeid']);
                    $p_lice = $this->_getLicenceText($tags['licensetype']);
                    $result[] = array(
                        'id'               => $file->id,
                        'downloadlink'     => $downloadLink,
                        'name'             => $file->name,
                        'version'          => $file->version,
                        'description'      => $file->description,
                        'type'             => $file->type,
                        'downloaded_count' => $file->downloaded_count,
                        'size'             => round($file->size / (1024 * 1024), 2),
                        'license'          => $p_lice,
                        'package_type'     => $p_type,
                        'package_arch'     => $tags['packagearch'],
                        'created'          => $file->created_timestamp,
                        'updated'          => $file->updated_timestamp
                    );
                }
            }
        }

        return $result;
    }

    protected function _getHTMLFiles($files)
    {
        if(count($files)==0) return '';
        $helperPrintDate = new Default_View_Helper_PrintDate();
         $html = '<div class="opendesktoppploadfiles">';
         $html = $html.'<table class="opendesktoppploadfilestable">';
         $html = $html.'<thead><tr><th>File</th><th>Version</th><th>Description</th><th>Filetype</th><th>Packagetype</th><th>License</th>';
         $html = $html.'<th>Downloads</th><th>Date</th><th>Filesize</th></tr></thead>';
         $html = $html.'<tbody>';
         foreach ($files as $file) {
               $html = $html.'<tr>';
               $html = $html.'<td><a href="'.$file['downloadlink'].'" >'.$file['name'].'</a></td>';
               $html = $html.'<td>'.$file['version'].'</td>';
               $html = $html.'<td>'.$file['description'].'</td>';
               $html = $html.'<td>'.$file['type'].'</td>';
               $html = $html.'<td>'.$file['package_type'].'</td>';
               $html = $html.'<td>'.$file['license'].'</td>';
               $html = $html.'<td class="opendesktoppploadfilestabletdright">'.$file['downloaded_count'].'</td>';
               $html = $html.'<td>'.$file['created'].'</td>';
               $html = $html.'<td class="opendesktoppploadfilestabletdright">'.$file['size'].'MB</td>';
               $html = $html.'</tr>';
         }
         $html = $html.'</tbody></table> </div>';
         return $html;
    }


    public function pploadAction()
    {
        $downloadItems = array();

        $ppload_collection_id = $this->getParam('ppload_collection_id');

        $count_downloads_hive = $this->getParam('count_downloads_hive');
        if(empty($count_downloads_hive)){
            $downloads = 0;
        }else{
            $downloads = $count_downloads_hive;
        }

        $files = $this->_getPploadFiles($ppload_collection_id);
        $html='';
        $html = $this->_getHTMLFiles($files);

        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'totalitems' => count($files),
                'html'       => $html
            );

            $this->_sendResponse($response, $this->_format);
        }
    }

    protected function _getLicenceText($id)
    {

        $typetext = '';
        switch ($id) {
            case 0:
                $typetext =  'Other';
                break;
            case 1:
                $typetext =  'GPLv2 or later';
                break;
            case 2:
                $typetext =  'LGPL';
                break;
            case 3:
                $typetext =  'Artistic 2.0';
                break;
            case 4:
                $typetext =  'X11';
                break;
            case 5:
                $typetext =  'QPL';
                break;
            case 6:
                $typetext =  'BSD';
                break;
            case 7:
                $typetext =  'Proprietary License';
                break;
            case 8:
                $typetext =  'GFDL';
                break;
            case 9:
                $typetext =  'CPL 1.0';
                break;
            case 10:
                $typetext =  'Creative Commons by';
                break;
            case 11:
                $typetext =  'Creative Commons by-sa';
            case 12:
                $typetext =  'Creative Commons by-nd';
                break;
            case 13:
                $typetext =  'Creative Commons by-nc';
                break;
            case 14:
                $typetext =  'Creative Commons by-nc-sa';
                break;
            case 15:
                $typetext =  'Creative Commons by-nc-nd';
                break;
            case 16:
                $typetext =  'AGPL';
                break;
            case 18:
                $typetext =  'GPLv2 only';
                break;
            case 19:
                $typetext =  'GPLv3';
                break;
        }
        return $typetext;

    }

    protected function _getPackagetypeText($typid)
    {
        $typetext = '';
        switch ($typid) {
            case 1:
                $typetext =  'AppImage';
                break;
            case 2:
                $typetext =  'Android (APK)';
                break;
            case 3:
                $typetext =  'OS X compatible';
                break;
            case 4:
                $typetext =  'Windows executable';
                break;
            case 5:
                $typetext =  'Debian';
                break;
            case 6:
                $typetext =  'Snappy';
                break;
            case 7:
                $typetext =  'Flatpak';
                break;
            case 8:
                $typetext =  'Electron-Webapp';
                break;
            case 9:
                $typetext =  'Arch';
                break;
            case 10:
                $typetext =  'open/Suse';
                break;
            case 11:
                $typetext =  'Redhat';
                break;
            case 12:
                $typetext =  'Source Code';
                break;
        }
        return $typetext;
    }
    /**
     * @param string $fileTags
     *
     * @return array
     */
    protected function _parseFileTags($fileTags)
    {
        $tags = explode(',', $fileTags);
        $parsedTags = array(
            'link'          => '',
            'licensetype'   => '',
            'packagetypeid' => '',
            'packagearch'   => ''
        );
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (strpos($tag, 'link##') === 0) {
                $parsedTags['link'] = urldecode(str_replace('link##', '', $tag));
            } else {
                if (strpos($tag, 'licensetype-') === 0) {
                    $parsedTags['licensetype'] = str_replace('licensetype-', '', $tag);
                } else {
                    if (strpos($tag, 'packagetypeid-') === 0) {
                        $parsedTags['packagetypeid'] = str_replace('packagetypeid-', '', $tag);
                    } else {
                        if (strpos($tag, 'packagearch-') === 0) {
                            $parsedTags['packagearch'] = str_replace('packagearch-', '', $tag);
                        }
                    }
                }
            }
        }
        return $parsedTags;
    }
}
