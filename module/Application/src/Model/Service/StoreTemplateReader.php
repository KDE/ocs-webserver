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


use Application\Model\Service\Interfaces\StoreTemplateReaderInterface;
use Exception;
use Laminas\Config\Config;
use Laminas\Log\Logger;

class StoreTemplateReader implements StoreTemplateReaderInterface
{

    /**
     * @var Config
     */
    private $config;
    /**
     * @var Logger
     */
    private $logger;

    public function __construct($config, Logger $log)
    {
        $this->config = $config;
        $this->logger = $log;
    }

    /**
     * @param string $storeConfigName
     *
     * @return array
     * @throws Exception
     */
    public function getStoreTemplate($storeConfigName)
    {
        $templatePath = $this->config->settings->store->template->path;

        $fileNameStoreTemplate = $templatePath . '/client_' . $storeConfigName . '.ini.php';

        if (file_exists($fileNameStoreTemplate)) {
            $storeTemplate = require $templatePath . '/client_' . $storeConfigName . '.ini.php';
        } else {
            $this->logger->warn(__METHOD__ . ' - ' . $storeConfigName . ' :: can not access template file for store context. Use default template.');
            $storeTemplate = $this->getStoreDefaultTemplate();
        }

        return $storeTemplate;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getStoreDefaultTemplate()
    {
        $templatePath = $this->config->settings->store->template->path;
        $defaultStoreName = $this->config->settings->store->template->default;

        $fileNameDefaultTemplate = $templatePath . '/client_' . $defaultStoreName . '.ini.php';

        if (file_exists($fileNameDefaultTemplate)) {
            $storeTemplate = require $templatePath . '/client_' . $defaultStoreName . '.ini.php';
        } else {
            $this->logger->err(__METHOD__ . ' :: can not access default template file for store context: ' . $fileNameDefaultTemplate);
            throw new Exception(__METHOD__ . ' :: can not access default template file for store context: ' . $fileNameDefaultTemplate);
        }

        return $storeTemplate;
    }

}