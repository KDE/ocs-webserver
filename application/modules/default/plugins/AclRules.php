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
class Default_Plugin_AclRules extends Zend_Acl
{
    const ROLENAME_GUEST = 'guest';
    const ROLENAME_COOKIEUSER = 'cookieuser';
    const ROLENAME_FEUSER = 'feuser';
    const ROLENAME_MODERATOR = 'moderator';
    const ROLENAME_STAFF = 'staff';
    const ROLENAME_ADMIN = 'admin';
    const ROLENAME_SYSUSER = 'sysuser';

    function __construct()
    {
        $this->addRole(new Zend_Acl_Role (self::ROLENAME_GUEST));
        $this->addRole(new Zend_Acl_Role (self::ROLENAME_COOKIEUSER), self::ROLENAME_GUEST);
        $this->addRole(new Zend_Acl_Role (self::ROLENAME_FEUSER), self::ROLENAME_COOKIEUSER);
        $this->addRole(new Zend_Acl_Role (self::ROLENAME_MODERATOR), self::ROLENAME_FEUSER);
        $this->addRole(new Zend_Acl_Role (self::ROLENAME_STAFF), self::ROLENAME_FEUSER);
        $this->addRole(new Zend_Acl_Role (self::ROLENAME_ADMIN));
        $this->addRole(new Zend_Acl_Role (self::ROLENAME_SYSUSER));

        $this->addResource(new Zend_Acl_Resource ('default_logout'));
        $this->addResource(new Zend_Acl_Resource ('default_oauth'));

        $this->addResource(new Zend_Acl_Resource ('default_authorization'));
        $this->addResource(new Zend_Acl_Resource ('default_button'));
        $this->addResource(new Zend_Acl_Resource ('default_categories'));
        $this->addResource(new Zend_Acl_Resource ('default_community'));
        $this->addResource(new Zend_Acl_Resource ('default_content'));
        $this->addResource(new Zend_Acl_Resource ('default_discovery'));
        $this->addResource(new Zend_Acl_Resource ('default_donationlist'));
        $this->addResource(new Zend_Acl_Resource ('default_support'));
        $this->addResource(new Zend_Acl_Resource ('default_subscription'));
        $this->addResource(new Zend_Acl_Resource ('default_error'));
        $this->addResource(new Zend_Acl_Resource ('default_explore'));
        $this->addResource(new Zend_Acl_Resource ('default_gateway'));
        $this->addResource(new Zend_Acl_Resource ('default_hive'));
        $this->addResource(new Zend_Acl_Resource ('default_home'));
        $this->addResource(new Zend_Acl_Resource ('default_ocsv1')); // OCS API
        $this->addResource(new Zend_Acl_Resource ('default_embedv1')); // embed API
        $this->addResource(new Zend_Acl_Resource ('default_membersetting'));
        $this->addResource(new Zend_Acl_Resource ('default_json'));
        $this->addResource(new Zend_Acl_Resource ('default_productcategory'));
        $this->addResource(new Zend_Acl_Resource ('default_productcomment'));
        $this->addResource(new Zend_Acl_Resource ('default_product'));
        $this->addResource(new Zend_Acl_Resource ('default_report'));
        $this->addResource(new Zend_Acl_Resource ('default_rectification'));
        $this->addResource(new Zend_Acl_Resource ('default_rss'));
        $this->addResource(new Zend_Acl_Resource ('default_settings'));
        $this->addResource(new Zend_Acl_Resource ('default_supporterbox'));
        $this->addResource(new Zend_Acl_Resource ('default_plingbox'));
        $this->addResource(new Zend_Acl_Resource ('default_user'));
        $this->addResource(new Zend_Acl_Resource ('default_widget'));
        $this->addResource(new Zend_Acl_Resource ('default_file'));
        $this->addResource(new Zend_Acl_Resource ('default_plings'));
        $this->addResource(new Zend_Acl_Resource ('default_gitfaq'));
        $this->addResource(new Zend_Acl_Resource ('default_spam'));
        $this->addResource(new Zend_Acl_Resource ('default_moderation'));
        $this->addResource(new Zend_Acl_Resource ('default_duplicates'));
        $this->addResource(new Zend_Acl_Resource ('default_newproducts'));
        $this->addResource(new Zend_Acl_Resource ('default_misuse'));
        $this->addResource(new Zend_Acl_Resource ('default_credits'));
        $this->addResource(new Zend_Acl_Resource ('default_ads'));
        $this->addResource(new Zend_Acl_Resource ('default_dl'));
        $this->addResource(new Zend_Acl_Resource ('default_password'));
        $this->addResource(new Zend_Acl_Resource ('default_verify'));
        $this->addResource(new Zend_Acl_Resource ('default_login'));
        $this->addResource(new Zend_Acl_Resource ('default_collection'));
        $this->addResource(new Zend_Acl_Resource ('default_funding'));

        $this->addResource(new Zend_Acl_Resource ('default_stati'));
        $this->addResource(new Zend_Acl_Resource ('default_tag'));
        $this->addResource(new Zend_Acl_Resource ('default_section'));

        
        $this->addResource(new Zend_Acl_Resource ('backend_categories'));
        $this->addResource(new Zend_Acl_Resource ('backend_vcategories'));
        $this->addResource(new Zend_Acl_Resource ('backend_categorytag'));
        $this->addResource(new Zend_Acl_Resource ('backend_categorytaggroup'));
        $this->addResource(new Zend_Acl_Resource ('backend_claim'));
        $this->addResource(new Zend_Acl_Resource ('backend_comments'));
        $this->addResource(new Zend_Acl_Resource ('backend_content'));
        $this->addResource(new Zend_Acl_Resource ('backend_faq'));
        $this->addResource(new Zend_Acl_Resource ('backend_hive'));
        $this->addResource(new Zend_Acl_Resource ('backend_hiveuser'));
        $this->addResource(new Zend_Acl_Resource ('backend_index'));
        $this->addResource(new Zend_Acl_Resource ('backend_mail'));
        $this->addResource(new Zend_Acl_Resource ('backend_member'));
        $this->addResource(new Zend_Acl_Resource ('backend_memberpayout'));
        $this->addResource(new Zend_Acl_Resource ('backend_memberpaypaladdress'));
        $this->addResource(new Zend_Acl_Resource ('backend_paypalvalidstatus'));
        $this->addResource(new Zend_Acl_Resource ('backend_payoutstatus'));
        $this->addResource(new Zend_Acl_Resource ('backend_operatingsystem'));
        $this->addResource(new Zend_Acl_Resource ('backend_project'));
        $this->addResource(new Zend_Acl_Resource ('backend_ranking'));
        $this->addResource(new Zend_Acl_Resource ('backend_reportcomments'));
        $this->addResource(new Zend_Acl_Resource ('backend_reportproducts'));
        $this->addResource(new Zend_Acl_Resource ('backend_search'));
        $this->addResource(new Zend_Acl_Resource ('backend_storecategories'));
        $this->addResource(new Zend_Acl_Resource ('backend_vstorecategories'));
        $this->addResource(new Zend_Acl_Resource ('backend_store'));
        $this->addResource(new Zend_Acl_Resource ('backend_tag'));
        $this->addResource(new Zend_Acl_Resource ('backend_user'));
        $this->addResource(new Zend_Acl_Resource ('backend_tags'));
        $this->addResource(new Zend_Acl_Resource ('backend_ghnsexcluded'));
        $this->addResource(new Zend_Acl_Resource ('backend_letteravatar'));
        $this->addResource(new Zend_Acl_Resource ('backend_group'));
        $this->addResource(new Zend_Acl_Resource ('backend_spamkeywords'));
        $this->addResource(new Zend_Acl_Resource ('backend_projectclone'));
        
        $this->addResource(new Zend_Acl_Resource ('backend_section'));
        $this->addResource(new Zend_Acl_Resource ('backend_sectioncategories'));
        $this->addResource(new Zend_Acl_Resource ('backend_sponsor'));
        
        $this->addResource(new Zend_Acl_Resource ('backend_browselisttype'));

        $this->addResource(new Zend_Acl_Resource ('backend_cdiscourse'));
        $this->addResource(new Zend_Acl_Resource ('backend_cgitlab'));
        $this->addResource(new Zend_Acl_Resource ('backend_cldap'));
        $this->addResource(new Zend_Acl_Resource ('backend_coauth'));
        $this->addResource(new Zend_Acl_Resource ('backend_cexport'));
        $this->addResource(new Zend_Acl_Resource ('backend_statistics'));

        $this->addResource(new Zend_Acl_Resource ('statistics_data'));

        $this->allow(self::ROLENAME_GUEST, array(
            'statistics_data'
        ));

        $this->allow(self::ROLENAME_GUEST, array(
            'default_logout',
            'default_authorization',
            'default_button',
            'default_categories',
            'default_content',
            'default_community',
            'default_donationlist',
            'default_error',
            'default_explore',
            'default_gateway',
            'default_hive',
            'default_home',
            'default_membersetting',
            'default_json',
            'default_ocsv1', // OCS API
            'default_embedv1', // embed API
            'default_productcategory',
            'default_rss',
            'default_support',
            'default_subscription',
            'default_supporterbox',
            'default_plingbox',
            'default_oauth',
            'default_plings',
            'default_gitfaq',
            'default_ads',
            'default_dl',
            'default_stati',
            'default_password',
            'default_verify',
            'default_login',
            'default_collection'
        ));

        $this->allow(self::ROLENAME_SYSUSER, array(
            'default_authorization',
            'default_button',
            'default_categories',
            'default_content',
            'default_community',
            'default_donationlist',
            'default_error',
            'default_explore',
            'default_gateway',
            'default_hive',
            'default_home',
            'default_ocsv1', // OCS API
            'default_embedv1', // embed API
            'default_productcategory',
            'default_report',
            'default_rss',
            'default_supporterbox',
            'default_plingbox',
            'default_oauth',
            'default_plings',
            'default_ads',
            'default_dl',
            'default_stati',
            'default_password'
        ));

        $this->allow(self::ROLENAME_COOKIEUSER, array(
            'default_logout',
            'default_productcomment',
            'default_settings',            
            'default_tag',
            'default_rectification'
        ));

        $this->allow(self::ROLENAME_STAFF, array(
            'backend_index',
            'backend_categories',
            'backend_categorytag',
            'backend_claim',
            'backend_comments',
            'backend_content',
            'backend_store',
            'backend_storecategories',
            'backend_operatingsystem',
            'backend_reportcomments',
            'backend_reportproducts',
            'backend_search',
            'backend_group'
        ));

        $this->allow(self::ROLENAME_ADMIN);

        // resource access rights in detail
        $this->allow(self::ROLENAME_GUEST, 'backend_group', array('newgroup'));

        // resource default_product
        $this->allow(self::ROLENAME_GUEST, 'default_product', array(
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
            'startvideoajax',
            'stopvideoajax',
            'loadfirstfilejson'
        ));
        
        // resource default_product
        $this->allow(self::ROLENAME_GUEST, 'default_collection', array(
            'index',
            'show',
            'getupdatesajax',
            'updates',
            'follows',
            'fetch',
            'search',
            //'startdownload',
            //'ppload',
            'loadratings',
            //'loadinstallinstruction',
            //'getfilesajax',
            'gettaggroupsforcatajax'
        ));

        // resource default_product
        $this->allow(self::ROLENAME_SYSUSER, 'default_product', array(
            'index',
            'show',
            'getupdatesajax',
            'updates',
            'follows',
            'fetch',
            'search',
            'startdownload',
            'ppload',
            'loadratings'
        ));

        $this->allow(self::ROLENAME_COOKIEUSER, 'default_product', array(
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
            'claim'
        ));
        
        $this->allow(self::ROLENAME_COOKIEUSER, 'default_collection', array(
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
            'claim'
        ));

        $this->allow(self::ROLENAME_COOKIEUSER, 'default_membersetting', array(
            'getsettings','setsettings','notification','searchmember'
        ));

        $this->allow(self::ROLENAME_MODERATOR, 'backend_project', array(
            'doghnsexclude'
        ));

        $this->allow(self::ROLENAME_MODERATOR, 'default_moderation', array(
            'index','list'
        ));
        $this->allow(self::ROLENAME_MODERATOR, 'default_duplicates', array(
            'index'
        ));
        $this->allow(self::ROLENAME_MODERATOR, 'default_newproducts', array(
            'index'
        ));




        $this->allow(self::ROLENAME_COOKIEUSER, 'default_product', array(
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
            'getprojectsajax'

        ), new Default_Plugin_Acl_IsProjectOwnerAssertion());

        // resource default_support
        $this->allow(self::ROLENAME_GUEST, 'default_support', array('index'));
        $this->allow(self::ROLENAME_COOKIEUSER, 'default_support', array('index', 'pay', 'paymentok', 'paymentcancel'));
        
        // resource default_subscription
        $this->allow(self::ROLENAME_GUEST, 'default_subscription', array('index', 'support2'));
        $this->allow(self::ROLENAME_COOKIEUSER, 'default_subscription', array('index', 'support2', 'pay', 'pay2', 'paymentok', 'paymentcancel'));

        // resource default_report
        $this->allow(self::ROLENAME_COOKIEUSER, 'default_report', array('comment', 'product', 'productfraud', 'productclone'));

        // resource default_widget
        $this->allow(self::ROLENAME_GUEST, 'default_widget', array('index', 'render'));
        $this->allow(self::ROLENAME_COOKIEUSER, 'default_widget', array('save', 'savedefault', 'config'),
            new Default_Plugin_Acl_IsProjectOwnerAssertion());

        $this->allow(self::ROLENAME_COOKIEUSER, 'default_file', array(
            'gitlink',
            'link',
        ), new Default_Plugin_Acl_IsProjectOwnerAssertion());

        // resource default_user
        $this->allow(self::ROLENAME_GUEST, 'default_home', array('baseurlajax','forumurlajax','blogurlajax','storenameajax','domainsajax', 'userdataajax', 'loginurlajax', 'metamenujs','metamenubundlejs','fetchforgit'));

        // resource default_user
        $this->allow(self::ROLENAME_GUEST, 'default_user', array('index', 'aboutme', 'share', 'report', 'about', 'tooltip', 'avatar', 'userdataajax'));

        $this->allow(self::ROLENAME_COOKIEUSER, 'default_user', array(
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
        ));
        
        //$this->allow(self::ROLENAME_GUEST, 'default_funding', array(
        //    'index',
        //    'plingsajax',
        //    'plingsmonthajax'
        //));

        $this->allow(self::ROLENAME_COOKIEUSER, 'default_tag', array('filter', 'add', 'del', 'assign', 'remove'));
    }

}
