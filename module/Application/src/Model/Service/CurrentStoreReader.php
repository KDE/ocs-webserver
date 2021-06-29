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

namespace Application\Model\Service;


use Application\Model\Entity\ConfigStore;
use Application\Model\Entity\CurrentStore;
use Application\Model\Repository\ConfigStoreRepository;
use Application\Model\Service\Interfaces\CurrentStoreReaderInterface;
use Application\Model\Service\Interfaces\StoreServiceInterface;
use Application\Model\Service\Interfaces\StoreTemplateReaderInterface;
use Exception;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Log\Logger;
use Laminas\Router\RouteMatch;

class CurrentStoreReader implements CurrentStoreReaderInterface
{
    /** @var CurrentStore $current_store */
    protected $current_store;

    /** @var Request $request */
    private $request;

    /** @var ConfigStoreRepository $configStoreRepository */
    private $configStoreRepository;

    /** @var RouteMatch $routeMatch */
    private $routeMatch;

    /** @var Logger $logger */
    private $logger;

    /** @var StoreTemplateReaderInterface */
    private $template_reader;

    /** @var StoreServiceInterface */
    private $store_service;

    public function __construct($request, $routeMatch, $logger, $templateReader, $storeService, $storeConfigRepository)
    {
        $this->request = $request;
        $this->routeMatch = $routeMatch;
        $this->configStoreRepository = $storeConfigRepository;
        $this->logger = $logger;
        $this->template_reader = $templateReader;
        $this->store_service = $storeService;
    }

    /**
     * @return CurrentStore
     */
    public function getCurrentStore()
    {
        if (isset($this->current_store)) {
            return $this->current_store;
        }

        return $this->readCurrentStoreConfig();
    }

    /**
     * @param CurrentStore $current_store
     */
    public function setCurrentStore($current_store)
    {
        $this->current_store = $current_store;
    }

    /**
     * @return CurrentStore
     */
    public function readCurrentStoreConfig()
    {
        if (isset($this->current_store)) {
            return $this->current_store;
        }

        $store_config = $this->getConfig();
//        $store_host = $this->getStoreHost();
//        $store_config_name = $this->getStoreConfigName($store_host);
//        $config_store = $this->getConfigStore($store_host);
        $store_template = $this->getStoreTemplate($store_config->config_id_name);
        $store_tags = $this->getStoreTags($store_config->store_id);
        $store_tag_groups = $this->getStoreTagGroups($store_config->store_id);
        $store_categories = $this->getStoreCategories($store_config->store_id);

        $this->current_store = new CurrentStore();
        $this->current_store->setConfig($store_config);
        $this->current_store->setTemplate($store_template);
        $this->current_store->setTags($store_tags);
        $this->current_store->setTagGroups($store_tag_groups);
        $this->current_store->setCategories($store_categories);

        return $this->current_store;
    }

    /**
     * @return ConfigStore|object
     */
    private function getConfig()
    {
        /** @var ConfigStore $store_config */
        $store_config = null;

        try {
            // search for store_id in route params
            $store_id = $this->getStoreIdFromRoute();
            if ($store_id) {
                $store_config = $this->configStoreRepository->findOneBy(
                    array(
                        'name' => $store_id,
                        'deleted_at IS NULL',
                    )
                );
            } else {
                if ($this->request instanceof \Laminas\Http\Request) {
                    // otherwise search for the config by hostname
                    $store_config = $this->configStoreRepository->findOneBy(
                        array(
                            'host' => $this->request->getUri()->getHost(),
                            'deleted_at IS NULL',
                        )
                    );
                }
            }
        } catch (Exception $e) {
            $request_url = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()
                                                                                         ->getHost() . $_SERVER['REQUEST_URI'];
            $this->logger->debug(__METHOD__ . ':' . $e->getLine() . '(' . __LINE__ . ')' . ' - `config_store` not found: ' . $request_url . ' - ' . $e->getMessage());
            // nothing found. last chance the default config.
            $store_config = $this->configStoreRepository->findOneBy(array('default' => '1', 'deleted_at IS NULL'));
        }

        return $store_config;
    }

    private function getStoreIdFromRoute()
    {
        // search for store id in route params
        $store_id = null;

        $match = array();
        $success = preg_match('|^/s/([\w-\.\s]*)/?|', urldecode($_SERVER['REQUEST_URI']), $match);
        if (1 == $success) {
            $store_id = $match[1];
        }

//        this will not work if the routing is not completely configured
//        if ($this->routeMatch->getParam('store_id')) {
//            $store_id = preg_replace('/[^-a-zA-Z0-9_\.]/', '', $this->routeMatch->getParam('store_id'));
//        }

        return $store_id;
    }

    private function getStoreTemplate($config_id_name)
    {
        $store_template = $this->template_reader->getStoreTemplate($config_id_name);
        if (empty($store_template)) {
            $store_template = $this->template_reader->getStoreDefaultTemplate();
        }

        return $store_template;
    }

    private function getStoreTags($store_id)
    {
        return $this->store_service->getTagsAsIdForStore($store_id);
    }

    private function getStoreTagGroups($store_id)
    {
        return $this->store_service->getTagGroupsAsIdForStore($store_id);
    }

    private function getStoreCategories($store_id)
    {
        return $this->store_service->getCategoriesAsIdForStore($store_id);
    }

}