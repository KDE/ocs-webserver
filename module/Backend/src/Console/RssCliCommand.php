<?php
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

namespace Backend\Console;

use Application\Model\Repository\ConfigStoreRepository;
use Application\Model\Service\ProjectService;
use Application\Model\Service\Util;
use Application\View\Helper\Image;
use Application\View\Helper\Truncate;
use DOMDocument;
use Exception;
use Laminas\Config\Config;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Log\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RssCliCommand
 *
 * @package Backend\Console
 */
class RssCliCommand extends Command
{
    // the name of the command (the part after "scripts/console")
    protected static $defaultName = 'app:rss';

    /** @var Config */
    protected $_config;
    /** @var Logger */
    protected $_logger;

    protected $db;
    protected $cache;
    protected $application_store_config_list;

    public function __construct()
    {
        parent::__construct();

        $this->db = GlobalAdapterFeature::getStaticAdapter();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Command to refresh the RSS-Feeds for all Stores')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Command to refresh the RSS-Feeds for all Stores.');
    }

    /**
     * Run php code as cronjob.
     * I.e.:
     * /usr/bin/php /var/www/pling.it/pling/scripts/cron.php -a
     * /backend/member-payout-cli/run/action/payout/context/all >> /var/www/ocs-www/logs/masspay.log $
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     * @see CliInterface::runAction()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initVars();

        $modelDomainConfig = new ConfigStoreRepository($this->db, $this->cache);
        $application_store_category_list = $modelDomainConfig->fetchAllStoresAndCategories();
        $this->application_store_config_list = $modelDomainConfig->fetchAllStoresConfigArray();

        foreach ($application_store_category_list as $hostname => $listCategory) {
            $latestProducts = $this->getLatestProductsForHost($listCategory, $hostname);

            $rssWriter = $this->createXML($latestProducts, $hostname);
            $resultXml = $rssWriter->saveXml();
            $config = $this->application_store_config_list[$hostname];
            $config_name = $config['config_id_name'];
            $this->saveXmlFile($resultXml, $hostname);
        }
    }

    public function initVars()
    {
        //init
        $this->_config = $GLOBALS['ocs_config'];
        $this->_logger = $GLOBALS['ocs_log'];
        $this->cache = $GLOBALS['ocs_cache'];
    }

    private function getLatestProductsForHost($listCategories, $hostname)
    {
        $filter['category'] = $listCategories;
        $filter['order'] = 'latest';
        $pageLimit = 10;
        $offset = 0;

        $tagFilter = isset($GLOBALS['ocs_config_store_tags']) ? $GLOBALS['ocs_config_store_tags'] : null;
        if ($tagFilter) {
            $filter['tag'] = $tagFilter;
        }

        $modelProject = new ProjectService($this->db);
        $requestedElements = $modelProject->fetchProjectsByFilter($filter, $pageLimit, $offset);

        $dataArray = $this->createBaseInformation('Latest Products', $hostname);
        $dataArray['entries'] = $this->generateFeedData($requestedElements['elements'], $hostname);

        return $dataArray;
    }

    private function createBaseInformation($title, $hostname)
    {
        $storeData = $this->application_store_config_list[$hostname];
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

        $fileNameConfig = './data/stores/templates/client_' . $storeConfigName . '.ini.php';

        if (file_exists($fileNameConfig)) {
            $storeTemplate = require './data/stores/templates/client_' . $storeConfigName . '.ini.php';
        } else {
            $this->_logger->warn(__METHOD__ . ' - ' . $storeConfigName . ' :: can not access config file for store context. path:' . $fileNameConfig);
        }

        return $storeTemplate;
    }

    private function generateFeedData($requestedElements, $hostname)
    {
        $helperTruncate = new Truncate();
        $returnValues = array();
        foreach ($requestedElements as $requestedElement) {
            $requestedElement = Util::arrayToObject($requestedElement);
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
                'lastUpdate'  => ($requestedElement->project_changed_at),
                // optional
                //                'comments' => 'comments page of the feed entry',                              // optional
                //                'commentRss' => 'the feed url of the associated comments',                    // optional
                'category'    => $requestedElement->cat_title,
                // required,
                // list of the attached categories                                              // optional
                //                'enclosure'    => array(
                //                array(
                //                    'url' => 'url of the linked enclosure',                                   // required
                //                    'type' => 'mime type of the enclosure',                                   // optional
                //                    'length' => 'length of the linked content in octets',                     // optional
                //                ),
                //                ) // list of the enclosures of the feed entry                                 // optional
                'username'    => $requestedElement->username,
                'url'         => '/p/' . $requestedElement->project_id,
            );
        }

        return $returnValues;
    }

    private function createContent($requestedElement, $hostname)
    {
        $link = 'https://' . $hostname . '/p/' . $requestedElement->project_id;
        $helperImage = new Image();
        $image = $helperImage->Image($requestedElement->image_small, array('height' => 40, 'width' => 40));

        return '<a href="' . $link . '"><img src="' . $image . '" alt="Thumbnail" class="thumbnail" align="left" hspace="10" vspace="10" border="0" /></a><b><big><a href="' . $link . '" style="font-weight:bold;color:#333333;text-decoration:none;">' . $requestedElement->title . '</a></big></b><br /> (' . $requestedElement->cat_title . ')<br />' . $requestedElement->description . '<br /><br /><a href="' . $link . '" style="font-weight:bold;color:#333333;text-decoration:none;">[read more]</a><br /><br />';
    }

    private function createXML($data, $hostname)
    {
        //create the xml document
        $xmlDoc = new DOMDocument();


        $feed = $xmlDoc->createElement("feed");
        $feed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $root = $xmlDoc->appendChild($feed);
        $root->appendChild($xmlDoc->createElement("id", $data['link']));
        $title = $xmlDoc->createElement("title");
        $cdata = $xmlDoc->createCDATASection(($data['title']));
        $title->appendChild($cdata);
        $root->appendChild($title);
        $root->appendChild($xmlDoc->createElement("updated", date(DATE_RFC3339)));
        $link = $xmlDoc->createElement("link");
        $link->setAttribute('rel', 'self');
        $link->setAttribute('href', $data['link']);
        $link->setAttribute('hreflang', 'en-us');
        $root->appendChild($link);
        $root->appendChild($xmlDoc->createElement("logo", $data['image']));
        $root->appendChild($xmlDoc->createElement("rights", $data['copyright']));
        $root->appendChild($xmlDoc->createElement("generator", $data['generator']));

        foreach ($data['entries'] as $entry) {
            if (!empty($entry)) {
                $entryTab = $xmlDoc->createElement("entry");
                $domain = parse_url($entry['link'], PHP_URL_HOST);
                $path = parse_url($entry['link'], PHP_URL_PATH);
                $id = "tag:{$domain}," . date("Y-m-d") . ":{$path}";
                $entryTab->appendChild($xmlDoc->createElement("id", $id));

                $link = $xmlDoc->createElement("link");
//                $link->setAttribute('rel', 'self');
                $link->setAttribute('href', $entry['link']);
                $link->setAttribute('hreflang', 'en-us');
                $entryTab->appendChild($link);

                $title = $xmlDoc->createElement("title");
                $cdata = $xmlDoc->createCDATASection(($entry['title']));
                $title->appendChild($cdata);
                $entryTab->appendChild($title);
                $entryTab->appendChild(
                    $xmlDoc->createElement(
                        "updated", date(DATE_RFC3339, strtotime($entry['lastUpdate']))
                    )
                );

                $summary = $xmlDoc->createElement("summary");
                $cdata = $xmlDoc->createCDATASection(($entry['description']));
                $summary->appendChild($cdata);
                $entryTab->appendChild($summary);

                $content = $xmlDoc->createElement("content");
                $content->setAttribute('type', 'html');
                $cdata = $xmlDoc->createCDATASection(($entry['content']));
                $content->appendChild($cdata);
                $entryTab->appendChild($content);

                $category = $xmlDoc->createElement("category");
                $category->setAttribute('term', $entry['category']);
                $entryTab->appendChild($category);

                //<author>
                //  <name>John Doe</name>
                //  <email>JohnDoe@example.com</email>
                //  <uri>http://example.com/~johndoe</uri>
                //</author>
                $author = $xmlDoc->createElement("author");
                $author->appendChild($xmlDoc->createElement("name", $entry['username']));
                $author->appendChild($xmlDoc->createElement("uri", 'https://' . $hostname . '/u/' . $entry['username']));
                $entryTab->appendChild($author);

                $root->appendChild($entryTab);
            }
        }

        //make the output pretty
        $xmlDoc->formatOutput = true;

        //save xml file
        //$file_name = str_replace(' ', '_',$title).'_'.time().'.xml';
        //$xmlDoc->save("files/" . $file_name);

        //return xml
        return $xmlDoc;
    }

    private function saveXmlFile($resultXml, $hostname)
    {
        $filename = str_replace('.', '_', $hostname);
        $path = __DIR__ . '/../../../../httpdocs/rss/' . $filename . '-content.rss';
        if (is_dir(dirname($path)) and is_writable(dirname($path))) {
            file_put_contents($path, $resultXml);
        } else {
            throw new Exception('directory for rss feed files doesn`t exist or is not writable: ' . $path);
        }
    }

}