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

namespace JobQueue\Jobs;

use Application\Model\Repository\ConfigStoreRepository;
use Application\Model\Service\ProjectCategoryService;
use JobQueue\Jobs\Interfaces\JobInterface;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;

/**
 * Class InitCacheStoreCategories
 *
 * @package JobQueue\Jobs
 */
class InitCacheStoreCategories extends BaseJob implements JobInterface
{
    protected $storeId;
    protected $options;

    private $projectCategoryService;
    private $configStoreRepository;

    /**
     * @param $args
     */
    public function perform($args)
    {
        $this->storeId = $args['storeId'];
        $this->options = $GLOBALS['ocs_config']->settings;
        $this->projectCategoryService = $args['projectCategoryService'];
        $this->configStoreRepository = $args['configStoreRepository'];
        $this->callInitCache($this->storeId);

    }

    protected function callInitCache($storeId)
    {
        $webCache = $this->initWebCacheAccess();
        $modelPCat = $this->projectCategoryService;
        $tree = $modelPCat->fetchCategoryTreeForStore($storeId, true);
        $webCache->setItem(ProjectCategoryService::CACHE_TREE_STORE . "_{$storeId}", $tree);

        $modelConfigStore = $this->configStoreRepository;
        $storesCat = $modelConfigStore->fetchAllStoresAndCategories(true);
        $webCache->setItem(ConfigStoreRepository::CACHE_STORES_CATEGORIES, $storesCat);
    }

    /**
     * @return AbstractAdapter
     */
    protected function initWebCacheAccess()
    {
        return $GLOBALS['ocs_cache'];
    }

}