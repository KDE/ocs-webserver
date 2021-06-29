<?php /** @noinspection PhpUnusedPrivateMethodInspection */

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

use Application\Model\Service\PploadService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Validator\Hostname;
use Laminas\Validator\Regex;
use Laminas\Validator\Uri;
use Laminas\View\Model\JsonModel;

/**
 * Class FileController
 *
 * @package Application\Controller
 */
class FileController extends AbstractActionController
{
    /** @var PploadService */
    protected $pploadService;

    public function __construct(PploadService $pploadService)
    {
        $this->pploadService = $pploadService;
    }

    public function linkAction()
    {
        if (false == $this->validateLink($this->getParam('u'))) {
            return new JsonModel(
                array(
                    'status'  => 'error',
                    'message' => 'Link is not valid',
                    'data'    => false,
                )
            );
        }
        $modelPpLoad = $this->pploadService;
        $project_id = (int)$this->getParam('project_id');
        $url = $this->getParam('u');
        $filename = $this->getParam('fn') ? $this->getParam('fn') : $this->getFilenameFromUrl($this->getParam('u'));
        $fileDescription = $this->getParam('fd');
        $result = $modelPpLoad->uploadEmptyFileWithLink($project_id, $url, $filename, $fileDescription);

        return new JsonModel(
            array(
                'status'  => 'success',
                'message' => '',
                'data'    => $result,
            )
        );
    }

    private function validateLink($getParam)
    {
        // However, you can allow "unwise" characters
        $validator = new Uri();

        if (false == $validator->isValid($getParam)) {
            return false;
        }

        $validator = new Hostname();
        $url_fragments = parse_url($getParam);
        if (false == $validator->isValid($url_fragments['host'])) {
            return false;
        }

        return true;
    }

    public function getParam($string)
    {
        $val = $this->params()->fromQuery($string);
        if (null == $val) {
            $val = $this->params()->fromRoute($string, null);
        }
        if (null == $val) {
            $val = $this->params()->fromPost($string, null);
        }

        return $val;
    }

    private function getFilenameFromUrl($getParam)
    {
        $url = parse_url($getParam);

        return isset($url['path']) ? basename($url['path']) : 'link';
    }

    public function gitlinkAction()
    {
        if (false == $this->validateGithubLink($this->getParam('u'))) {
            return new JsonModel(false);
        }
        $modelPpLoad = $this->pploadService;
        $project_id = (int)$this->getParam('project_id');
        $url = $this->getParam('u');
        $filename = $this->getParam('fn') ? $this->getParam('fn') : $this->getFilenameFromUrl($this->getParam('u'));
        $fileDescription = $this->getParam('fd');
        $result = $modelPpLoad->uploadEmptyFileWithLink($project_id, $url, $filename, $fileDescription);

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => '',
                'data'    => $result,
            )
        );
    }

    private function validateGithubLink($getParam)
    {
        /** regex tested in https://regex101.com/r/VFsvSd/1 */
        $validate = new Regex(
            '/^https:\/\/(?:(?:(?:www\.)?github)?|(?:raw\.githubusercontent)?)\.com\/.+$/'
        );

        return $validate->isValid($getParam);
    }

    /**
     * @return PploadService
     */
    public function getPploadService()
    {
        return $this->pploadService;
    }

    /**
     * @param PploadService $pploadService
     */
    public function setPploadService($pploadService)
    {
        $this->pploadService = $pploadService;
    }

}