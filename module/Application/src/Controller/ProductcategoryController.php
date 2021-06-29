<?php /** @noinspection PhpUnused */

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
 * */

namespace Application\Controller;

use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Service\ProjectService;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\JsonModel;

/**
 * Class ProductcategoryController
 *
 * @package Application\Controller
 */
class ProductcategoryController extends DomainSwitch
{

    const CATEGORY_ID = 'cat_id';

    public function __construct(AdapterInterface $db, array $config, Request $request)
    {
        parent::__construct($db, $config, $request);
        parent::init();
    }

    public function init()
    {
        parent::init();

        $this->_authMember = $this->view->getVariable('ocs_user');
    }

    public function fetchchildrenAction()
    {

        $identifier = $this->getParam(self::CATEGORY_ID);

        $tabCategories = new ProjectCategoryRepository($this->db, $this->cache);

        if (is_array($identifier)) {
            $result_array = array();
            foreach ($identifier as $element) {
                $result = $tabCategories->fetchImmediateChildren($element, $tabCategories::ORDERED_TITLE);
                $result_array = array_merge($result_array, $result);
            }
            $resultJson = new JsonModel($result_array);
        } else {
            $resultJson = new JsonModel(
                $tabCategories->fetchImmediateChildren($identifier, $tabCategories::ORDERED_TITLE)
            );
        }

        return $resultJson;
    }

    public function fetchsourceneededAction()
    {
        $identifier = $this->getParam(self::CATEGORY_ID);

        $tabCategories = new ProjectCategoryRepository($this->db, $this->cache);

        return new JsonModel($tabCategories->findCategory($identifier));

    }

    public function fetchcategoriesAction()
    {
        $tabCategories = new ProjectCategoryRepository($this->db, $this->cache);
        $categories = $tabCategories->fetchTree(true, false, 1);

        $paginator = new Paginator(new ArrayAdapter($categories));
        $paginator->setItemCountPerPage(5);
        $paginator->setCurrentPageNumber(1);

        return new JsonModel((array)$paginator->getCurrentItems());
    }

    public function fetchcategoryproductsAction()
    {
        $identifier = $this->getParam(self::CATEGORY_ID);

        $tableProject = new ProjectService($this->db, $this->infoService);
        $products = $tableProject->fetchProductsByCategory($identifier, 5);


        return new JsonModel($products);
    }

} 