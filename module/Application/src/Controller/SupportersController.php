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

namespace Application\Controller;

use Application\Model\Service\Interfaces\InfoServiceInterface;
use Application\Model\Service\Interfaces\SectionServiceInterface;
use Application\Model\Service\Util;
use Laminas\Json\Encoder;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * Class SupportersController
 *
 * @package Application\Controller
 */
class SupportersController extends BaseController
{

    const DEFAULT_SECTION_ID = 2;
    private $infoService;
    private $sectionService;

    public function __construct(
        InfoServiceInterface $infoService,
        SectionServiceInterface $sectionService
    ) {
        parent::__construct();
        $this->infoService = $infoService;
        $this->sectionService = $sectionService;
    }

    public function indexAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $this->layout()->noheader = true;
        $viewModel = new ViewModel();
        $section_id = (int)$this->params()->fromQuery('id', self::DEFAULT_SECTION_ID);
        $products = self::fetchProducts($section_id);
        $creators = self::fetchCreators($section_id);
        $supporters = self::fetchSupporters($section_id);
        $model = $this->sectionService;
        $section = null;
        if ($section_id) {
            $section = $model->fetchSection($section_id);
        }
        $categorisWithPayout = $model->fetchCategoriesWithPlinged();
        $sections = $model->fetchAllSections();
        $data = array(
            'isAdmin'      => $this->isAdmin(),
            'sections'     => $sections,
            'details'      => $categorisWithPayout,
            'baseurlStore' => $this->ocsConfig->settings->client->default->baseurl_store,
            'products'     => $products,
            'creators'     => $creators,
            'supporters'   => $supporters,
            'section'      => $section,
        );

        $title = 'Section';
        if ($section) {
            $title = 'Section ' . $section['name'];
        }
        $viewModel->setVariable('headTitle', $title);
        $viewModel->setVariable('data', Encoder::encode($data));

        return $viewModel;
    }

    public function fetchProducts($section_id)
    {
        $model = $this->sectionService;


        $products = $model->fetchTopPlingedProductsPerSection($section_id);
        foreach ($products as &$p) {
            $p['image_small'] = Util::image($p['image_small'], array('width' => 200, 'height' => 200));
            $p['updated_at'] = Util::printDate(($p['changed_at'] == null ? $p['created_at'] : $p['changed_at']));
        }

        return $products;
    }

    public function fetchCreators($section_id)
    {
        $model = $this->sectionService;
        $creators = $model->fetchTopPlingedCreatorPerSection($section_id);
        foreach ($creators as &$p) {
            $p['profile_image_url'] = Util::image($p['profile_image_url'], array('width' => 100, 'height' => 100));

        }

        return $creators;

    }

    public function fetchSupporters($section_id)
    {

        $info = $this->infoService;

        if ($section_id) {
            $supporters = $info->getNewActiveSupportersForSection($section_id, 1000);
        } else {
            $supporters = $info->getNewActiveSupportersForSectionAll(1000);
        }

        $s = array();
        foreach ($supporters as &$p) {

            $s[] = array(
                'profile_image_url'    => Util::image($p['profile_image_url'], array('width' => 100, 'height' => 100)),
                'member_id'            => $p['member_id'],
                'username'             => $p['username'],
                'section_support_tier' => $p['section_support_tier'],
            );

            /*$p['profile_image_url'] = Util::image($p['profile_image_url'], array('width' => 100, 'height' => 100));*/
        }

        return $s;

    }

    public function topAction()
    {

        $section_id = (int)$this->params()->fromQuery('id', self::DEFAULT_SECTION_ID);
        $products = self::fetchProducts($section_id);
        $creators = self::fetchCreators($section_id);

        return new JsonModel(array('status' => 'ok', 'products' => $products, 'creators' => $creators));
    }

    public function topcatAction()
    {

        $model = $this->sectionService;

        $cat_id = (int)$this->params()->fromQuery('cat_id');
        $products = $model->fetchTopPlingedProductsPerCategory($cat_id);


        foreach ($products as &$p) {
            $p['image_small'] = Util::image($p['image_small'], array('width' => 200, 'height' => 200));
            $p['updated_at'] = Util::printDate(($p['changed_at'] == null ? $p['created_at'] : $p['changed_at']));
        }

        $creators = $model->fetchTopPlingedCreatorPerCategory($cat_id);
        foreach ($creators as &$p) {
            $p['profile_image_url'] = Util::image($p['profile_image_url'], array('width' => 100, 'height' => 100));

        }

        return new JsonModel(array('status'   => 'ok',
                                   'cat_id'   => $cat_id,
                                   'products' => $products,
                                   'creators' => $creators,
                             )
        );

    }

    public function recentplingsAction()
    {
        $section_id = (int)$this->params()->fromQuery('id');
        $model = $this->sectionService;


        $products = $model->getNewActivePlingProduct($section_id);
        foreach ($products as &$p) {
            $p['image_small'] = Util::image($p['image_small'], array('width' => '115', 'height' => '75', 'crop' => 2));
            $p['created_at'] = Util::printDate($p['created_at']);
            $p['profile_image_url'] = Util::image($p['profile_image_url'], array('width' => 100, 'height' => 100));
        }

        return new JsonModel(array('status' => 'ok', 'products' => $products));
    }

}
