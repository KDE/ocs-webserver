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

use Application\Model\Service\CurrentStoreReader;
use Laminas\View\Helper\AbstractHelper;

/**
 * Class CurrentStore
 *
 * @package Application\View\Helper
 */
class CurrentStore extends AbstractHelper
{

    /**
     * @var CurrentStoreReader
     */
    private $current_store_reader;
    /**
     * @var \Application\Model\Entity\CurrentStore
     */
    private $current_store;

    public function __construct(CurrentStoreReader $current_store_reader)
    {
        $this->current_store_reader = $current_store_reader;
    }

    /**
     * @return \Application\Model\Entity\CurrentStore
     */
    public function __invoke()
    {
        return $this->current_store_reader->getCurrentStore();
    }

}