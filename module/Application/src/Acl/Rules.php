<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */

/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Acl;

use Laminas\Permissions\Acl\Acl;

/**
 * Class Rules
 *
 * @package Application\Acl
 */
class Rules
{
    /**
     * @var Acl
     */
    private $acl;
    private $route_params;

    public function __construct(Acl $acl, $route_params)
    {
        $this->acl = $acl;
        $this->route_params = $route_params;
    }

    public function getRules()
    {
        $this->acl->allow(Roles::ROLENAME_GUEST, array("SanSessionToolbar\Controller\SessionToolbar"));

        // general rules
        // -------------------------------------------------

        $this->acl->allow(Roles::ROLENAME_GUEST, array(
            \Application\Controller\AuthController::class,
            \Application\Controller\CollectionController::class,
            \Application\Controller\CommunityController::class,
            \Application\Controller\ContentController::class,
            \Application\Controller\DlController::class,
            \Application\Controller\ExploreController::class,
            \Application\Controller\GatewayController::class,
            \Application\Controller\HomeController::class,
            \Application\Controller\ReactController::class,
            \Application\Controller\JsonController::class,
            \Application\Controller\LoginController::class,
            \Application\Controller\MembersettingController::class,
            \Application\Controller\Ocsv1Controller::class,
            \Application\Controller\PasswordController::class,
            \Application\Controller\SearchController::class,
            \Application\Controller\SupportersController::class,
            \Application\Controller\HiveController::class,
        ));

        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, array(
            \Application\Controller\SettingsController::class,
            \Application\Controller\TagController::class,
            \Application\Controller\LogoutController::class,
        ));

        $this->acl->allow(Roles::ROLENAME_STAFF, array(
            \Application\Controller\ProductcommentController::class
        ));

        $this->acl->allow(Roles::ROLENAME_ADMIN);

        // resource dependent rules
        // ---------------------------------------------------------------------

        // guest

        // resource product
        $this->acl->allow(Roles::ROLENAME_GUEST, \Application\Controller\ProductController::class, array(
            'index',
            'show',
            'getupdatesajax',
            'updates',
            'follows',
            'fetch',
            'search',
            'startdownload',
            'ppload',
            'loadratings',
            'loadfilesjson',
            'loadinstallinstruction',
            'gettaggroupsforcatajax',
            'getfilesajax',
            'getfiletagsajax',
            'startvideoajax',
            'stopvideoajax',
            'startmediaviewajax',
            'stopmediaviewajax',
            'loadfirstfilejson',
            'loadtagrating',
            'loadRight',
            'loadFiles',           
            'load-comments',
            'indexReact'
        ));

        // resource collection
        $this->acl->allow(Roles::ROLENAME_GUEST, \Application\Controller\CollectionController::class, array(
            'index',
            'show',
            'getupdatesajax',
            'updates',
            'follows',
            'fetch',
            'search',
            'loadratings',
            'gettaggroupsforcatajax',
        ));

        // resource home
        $this->acl->allow(Roles::ROLENAME_GUEST, \Application\Controller\HomeController::class, array(
            'baseurlajax',
            'forumurlajax',
            'blogurlajax',
            'storenameajax',
            'domainsajax',
            'userdataajax',
            'loginurlajax',
            'metamenujs',
            'metamenubundlejs',
            'fetchforgit',
        ));        

        // resource login
        $this->acl->allow(Roles::ROLENAME_GUEST, \Application\Controller\LoginController::class,
            array('set', 'settheme', 'fp'));

        // resource supscription
        $this->acl->allow(Roles::ROLENAME_GUEST, \Application\Controller\SubscriptionController::class,
            array('index', 'support', 'supportpredefinded'));

        // resource user
        $this->acl->allow(Roles::ROLENAME_GUEST, \Application\Controller\UserController::class, array(
            'index',
            'aboutme',
            'share',
            'report',
            'about',
            'tooltip',
            'avatar',
            'userdataajax',
            'showoriginal',
            'moreproducts',
            'morecomments',
            'morerates',
            'moreplings',
            'morelikes',                   
            'userMorecollections',
            'userMorecomments',
            'userMorefeatured',
            'userMorecollections',
            'userMorelikes',
            'userMoreplings',
            'userMoreproducts',
            'userMorerates',
            'userShoworiginal',
        ));

        // resource hive
        $this->acl->allow(Roles::ROLENAME_GUEST, \Application\Controller\HiveController::class,
            array('show', 'usersearch'));

        // resource productcomment
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER,\Application\Controller\ProductcommentController::class,
                          array('index','showcommentsUX1', 'showcommentsUX2', 'showcommentsUX3')
        );

        //# ------------------------------------------------------------------------------------

        // cookieuser which is the same as feuser

        // resource product
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, \Application\Controller\ProductController::class, array(
            'add',
            'rating',
            'follow',
            'unfollow',
            'plingproject',
            'followproject',
            'unplingproject',
            'add',
            'pling',
            'pay',
            'dwolla',
            'paymentok',
            'paymentcancel',
            'saveproduct',
            'claim',
            'votetagrating',
        ));

        // resource product for product owner
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, \Application\Controller\ProductController::class, array(
            'edit',
            'saveupdateajax',
            'deleteupdateajax',
            'update',
            'preview',
            'delete',
            'unpublish',
            'publish',
            'verifycode',
            'makerconfig',
            'addpploadfile',
            'updatepploadfile',
            'deletepploadfile',
            'deletepploadfiles',
            'updatefiletag',
            'getcollectionprojectsajax',
            'getprojectsajax',

        ), new \Application\Acl\Assertions\IsProjectOwner($this->getParamFromRequest('project_id')));

        // resource collection
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, \Application\Controller\CollectionController::class, array(
            'add',
            'rating',
            'follow',
            'unfollow',
            'plingproject',
            'followproject',
            'unplingproject',
            'pling',
            'pay',
            'dwolla',
            'paymentok',
            'paymentcancel',
            'saveproduct',
            'claim',
        ));

        // resource membersetting
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, \Application\Controller\MembersettingController::class, array(
            'getsettings',
            'setsettings',
            'notification',
            'searchmember',
        ));

        // resource supscription
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, \Application\Controller\SubscriptionController::class, array(
                'index',
                'support2',
                'pay',
                'pay2',
                'paymentok',
                'paymentcancel',
                'supportpredefinded',
                'paypredefined',
            ));

        // resource report
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, \Application\Controller\ReportController::class,
            array('comment', 'product', 'productfraud', 'productclone'));

        // resource file
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, \Application\Controller\FileController::class, array(
            'gitlink',
            'link',
        ), new \Application\Acl\Assertions\IsProjectOwner($this->getParamFromRequest('project_id')));

        // resource user
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, \Application\Controller\UserController::class, array(
            'follow',
            'unfollow',
            'settings',
            'products',
            'collections',
            'news',
            'activities',
            'payments',
            'income',
            'payout',
            'payouthistory',
            'plings',
            'plingsold',
            'plingsajax',
            'plingsmonthajax',
            'downloadhistory',
            'likes',
            'funding',
            'sectionsajax',
            'sectionsmonthajax',
            'sectionplingsmonthajax',
            'sectioncreditsmonthajax',
            'sectionaffiliatesmonthdetailajax',
        ));

        // resource tags
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, \Application\Controller\TagController::class,
            array('filter', 'add', 'del', 'assign', 'remove'));

        // resource productcategory
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER, \Application\Controller\ProductcategoryController::class,
            array('fetchsourceneeded', 'fetchchildren'));

        // resource productcomment
        $this->acl->allow(Roles::ROLENAME_COOKIEUSER,\Application\Controller\ProductcommentController::class,
            array('addreply', 'addreplyreviewnew', )
        );
        //# ------------------------------------------------------------------------------------


        $this->acl->allow(Roles::ROLENAME_MODERATOR, \Application\Controller\ModerationController::class, array(
            'index',
            'list',
        ));
        $this->acl->allow(Roles::ROLENAME_MODERATOR, \Application\Controller\DuplicatesController::class, array(
            'index',
        ));
        $this->acl->allow(Roles::ROLENAME_MODERATOR, \Backend\Controller\ProjectController::class, array(
            'doghnsexclude',
        ));

        //# ------------------------------------------------------------------------------------


        //# ------------------------------------------------------------------------------------
        //TODO: Move definition to module
        $this->acl->allow(Roles::ROLENAME_GUEST, \Portal\Controller\PortalController::class);

        // resource reactcontroller
        $this->acl->allow(Roles::ROLENAME_GUEST, \Application\Controller\ReactController::class, array(
            'home', 
            'detail',  
            'explore',
            'user'
        )); 
        $this->acl->allow(Roles::ROLENAME_MODERATOR,  \Application\Controller\ReactController::class, array(
            'userDeleted',
            'userUnpublished',
            'userDuplicates'

        ));      

        return $this->acl;
    }

    private function getParamFromRequest($name)
    {
        return empty($this->route_params[$name]) ? null : $this->route_params[$name];
    }

}