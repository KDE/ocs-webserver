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
 *    Created: 14.09.2016
 **/
class Backend_RssController extends Local_Controller_Action_CliAbstract
{

    public function runAction()
    {
        $this->initGlobalApplicationVars();
        foreach (Zend_Registry::get('application_store_category_list') as $hostname => $listCategory) {
            $latestProducts = $this->getLatestProductsForHost($listCategory, $hostname);

            $rssWriter = Zend_Feed::importArray($latestProducts);
            $resultXml = $rssWriter->saveXml();
            $config = Zend_Registry::get('application_store_config_list')[$hostname];
            $config_name = $config['config_id_name'];
            $this->saveXmlFile($resultXml, $hostname);
        }
    }

    private function initGlobalApplicationVars()
    {
        $modelDomainConfig = new Default_Model_DbTable_ConfigStore();
        Zend_Registry::set('application_store_category_list', $modelDomainConfig->fetchAllStoresAndCategories());
        Zend_Registry::set('application_store_config_list', $modelDomainConfig->fetchAllStoresConfigArray());
    }

    private function getLatestProductsForHost($listCategories, $hostname)
    {
        $filter['category'] = $listCategories;
        $filter['order'] = 'latest';
        $pageLimit = 10;
        $offset = 0;

        $modelProject = new Default_Model_Project();
        $requestedElements = $modelProject->fetchProjectsByFilter($filter, $pageLimit, $offset);

        $dataArray = $this->createBaseInformation('Latest Products', $hostname);
        $dataArray['entries'] = $this->generateFeedData($requestedElements['elements'], $hostname);

        return $dataArray;
    }

    private function createBaseInformation($title, $hostname)
    {
        $storeData = Zend_Registry::get('application_store_config_list')[$hostname];
        $storeConfig = $this->getStoreTemplate($storeData['config_id_name']);

        return $importArray = array(
            'title'      => $storeConfig['head']['browser_title_prepend'] . ' ' . $title,
            // required
            'link'       => 'https://' . $hostname . '/content.rdf',
            // required
            'lastUpdate' => time(),
            // optional
            //            'published'   => time(),                                                          // optional
            'charset'    => 'utf-8',
            // required
            //            'description' => 'short description of the feed',                                 // optional
            'author'     => $storeConfig['head']['meta_author'],
            // optional
            'email'      => 'contact@opendesktop.org',
            // optional
            'copyright'  => 'All rights reserved. All trademarks are copyright by their respective owners. All contributors are responsible for their uploads.',
            // optional
            'image'      => 'https://' . $hostname . $storeConfig['logo'],
            // optional
            'generator'  => $hostname . ' atom feed generator',
            // optional
            'language'   => 'en-us',
            // optional
            'ttl'        => '15'
            // optional, ignored if atom is used
        );
    }

    private function getStoreTemplate($storeConfigName)
    {
        $storeTemplate = array();

        $fileNameConfig = APPLICATION_PATH . '/configs/client_' . $storeConfigName . '.ini.php';

        if (file_exists($fileNameConfig)) {
            $storeTemplate = require APPLICATION_PATH . '/configs/client_' . $storeConfigName . '.ini.php';
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - ' . $storeConfigName
                . ' :: can not access config file for store context.')
            ;
        }

        return $storeTemplate;
    }

    private function generateFeedData($requestedElements, $hostname)
    {
        $helperTruncate = new Default_View_Helper_Truncate();
        $returnValues = array();
        foreach ($requestedElements as $requestedElement) {
            $returnValues[] = array(
                'title'       => $requestedElement->title . ' [' . $requestedElement->cat_title . ']',
                // required
                'link'        => 'https://' . $hostname . '/p/' . $requestedElement->project_id,
                // required
                'description' => $helperTruncate->truncate(strip_tags($requestedElement->description)),
                // only text, no html, required
                //                'guid' => 'id of the article, if not given link value will used',             // optional
                'content'     => $this->createContent($requestedElement, $hostname),
                // can contain html, optional
                'lastUpdate'  => strtotime($requestedElement->project_changed_at),
                // optional
                //                'comments' => 'comments page of the feed entry',                              // optional
                //                'commentRss' => 'the feed url of the associated comments',                    // optional
                'category'    => array(
                    array(
                        'term' => $requestedElement->cat_title,                                 // required,
                    ),
                ),
                // list of the attached categories                                              // optional
                //                'enclosure'    => array(
                //                array(
                //                    'url' => 'url of the linked enclosure',                                   // required
                //                    'type' => 'mime type of the enclosure',                                   // optional
                //                    'length' => 'length of the linked content in octets',                     // optional
                //                ),
                //                ) // list of the enclosures of the feed entry                                 // optional
            );
        }

        return $returnValues;
    }

    private function createContent($requestedElement, $hostname)
    {
        $link = 'https://' . $hostname . '/p/' . $requestedElement->project_id;
        $helperImage = new Default_View_Helper_Image();
        $image = $helperImage->Image($requestedElement->image_small, array('height' => 40, 'width' => 40));

        return '<a href="' . $link . '"><img src="' . $image
            . '" alt="Thumbnail" class="thumbnail" align="left" hspace="10" vspace="10" border="0" /></a><b><big><a href="' . $link
            . '" style="font-weight:bold;color:#333333;text-decoration:none;">' . $requestedElement->title . '</a></big></b><br /> ('
            . $requestedElement->cat_title . ')<br />' . $requestedElement->description . '<br /><br /><a href="' . $link
            . '" style="font-weight:bold;color:#333333;text-decoration:none;">[read more]</a><br /><br />';
    }

    private function saveXmlFile($resultXml, $hostname)
    {
        $filename = str_replace('.', '_', $hostname);
        $path = APPLICATION_PATH . '/../httpdocs/rss/' . $filename . '-content.rss';
        if (is_dir(dirname($path)) AND is_writable(dirname($path))) {
            file_put_contents($path, $resultXml);
        } else {
            throw new Zend_Exception('directory for rss feed files doesn`t exist or is not writable: ' . $path);
        }
    }

}