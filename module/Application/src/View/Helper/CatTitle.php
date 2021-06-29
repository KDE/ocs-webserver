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

namespace Application\View\Helper;

use Application\Model\Repository\ProjectCategoryRepository;
use Laminas\View\Helper\AbstractHelper;

class CatTitle extends AbstractHelper
{

    const PROJECT_CATEGORY_ID = 'project_category_id';
    protected $table;
    protected $cache;

    public function __construct(
        ProjectCategoryRepository $table
    ) {
        $this->table = $table;
        $this->cache = $GLOBALS['ocs_cache'];
    }

    /**
     * @param int $catId
     *
     * @return string
     */
    public function __invoke($catId)
    {
        return $this->catTitle($catId);
    }

    public function catTitle($catId)
    {
        if (empty($catId) or $catId == '' or count($catId) == 0) {
            return 'All';
        }

        $id = $catId;

        if (is_array($catId)) {
            if (array_key_exists(self::PROJECT_CATEGORY_ID, $catId)) {
                $id = $catId[self::PROJECT_CATEGORY_ID];
            } else {
                return null;
            }
        }

        $cat = $this->table->fetchActive($id);

        if (count($cat)) {
            return $cat[0]['title'];
        } else {
            return '';
        }
    }

}
