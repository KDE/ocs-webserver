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

namespace Application\View\Helper;


use Application\Model\Repository\ConfigStoreRepository;
use Application\Model\Service\CurrentStoreReader;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Config\Config;
use Laminas\Json\Encoder;

class FetchMetaheaderMenuJson
{
    /**
     * @var AbstractAdapter
     */
    private $cache;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var ConfigStoreRepository
     */
    private $config_store_repository;
    /**
     * @var CurrentStoreReader
     */
    private $current_store;

    public function __construct(
        AbstractAdapter $cache,
        Config $config,
        ConfigStoreRepository $config_store_repository,
        CurrentStoreReader $current_store_reader
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->config_store_repository = $config_store_repository;
        $this->current_store = $current_store_reader;
    }

    public function __invoke()
    {

        $sname = $this->current_store->getCurrentStore()->getConfig()->host;
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . md5($sname) . '_new';

        if (false == ($domainobjects = $cache->getItem($cacheName))) {
            $tbl = $this->config_store_repository;
            $result = $tbl->fetchDomainObjects();
            // sort Desktop manually to the front
            $arrayDesktop = array();
            $arrayRest = array();

            foreach ($result as $obj) {
                $o = $obj['order'];
                $curOrder = floor($obj['order'] / 1000);
                if ($curOrder < 10 or $curOrder > 50) {
                    continue;
                }
                $obj['calcOrder'] = $curOrder;

                $tmp = array();
                $tmp['order'] = $obj['order'];
                $tmp['calcOrder'] = $obj['calcOrder'];
                $tmp['host'] = $obj['host'];
                $tmp['name'] = $obj['name'];
                $tmp['is_show_in_menu'] = $obj['is_show_in_menu'];
                $tmp['is_show_real_domain_as_url'] = $obj['is_show_real_domain_as_url'];

                if ($curOrder == 30) {
                    // Desktop set calcOrder = 9 manually put desktop in front
                    $tmp['calcOrder'] = 9;
                    $arrayDesktop[] = $tmp;
                } else {
                    $arrayRest[] = $tmp;
                }
            }
            $domainobjects = array_merge($arrayDesktop, $arrayRest);


            $baseurl = $this->config->settings->client->default->baseurl_store;
            // set group name manully
            foreach ($domainobjects as &$obj) {

                if ($sname == $obj['host']) {
                    $obj['menuactive'] = 1;
                } else {
                    $obj['menuactive'] = 0;
                }

                $order = $obj['order'];
                //OLD: z.b 150001 ende ==1 go real link otherwise /s/$name
                /*$last_char_check = substr($order, -1);
                if($last_char_check=='1')
                {
                    $obj['menuhref'] = $obj['host'];
                }else{
                    $obj['menuhref'] = $baseurl.'/s/'.$obj['name'];
                }
                 *
                 */
                $domainAsUrl = $obj['is_show_real_domain_as_url'];
                if ($domainAsUrl) {
                    $obj['menuhref'] = $obj['host'];
                } else {
                    $obj['menuhref'] = $baseurl . '/s/' . $obj['name'];
                }

                switch ($obj['calcOrder']) {
                    case 9:
                        $obj['menugroup'] = 'Desktops';
                        break;
                    case 10:
                        $obj['menugroup'] = 'Applications';
                        break;
                    case 20:
                        $obj['menugroup'] = 'Addons';
                        break;
                    case 40:
                        $obj['menugroup'] = 'Artwork';
                        break;
                    case 50:
                        $obj['menugroup'] = 'Other';
                        break;
                }

            }

            $cache->setItem($cacheName, $domainobjects);
        }

        return Encoder::encode($domainobjects);
    }

}