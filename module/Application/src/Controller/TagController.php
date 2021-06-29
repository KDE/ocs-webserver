<?php /** @noinspection PhpUndefinedFieldInspection */

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

use Application\Model\Repository\TagsRepository;
use Application\Model\Service\HtmlPurifyService;
use Application\Model\Service\TagService;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;
use Laminas\View\Model\JsonModel;

/**
 * Class TagController
 *
 * @package Application\Controller
 */
class TagController extends DomainSwitch
{

    protected $tagService;

    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        TagService $tagService
    ) {
        parent::__construct($db, $config, $request);
        parent::init();
        $this->tagService = $tagService;

        $this->_authMember = $this->view->getVariable('ocs_user');
        $this->configArray = $config;
    }

    public function indexAction()
    {
        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => '',
                'data'    => array(),
            )
        );
    }

    public function addAction()
    {
        $tag = $this->getParam('t');
        $projectid = (int)$this->getParam('p');

        if (strlen($tag) > 45) {

            return new JsonModel(
                array(
                    'status'  => 'error',
                    'message' => 'Max. length 45 chars',
                    'data'    => array('pid' => $projectid, 'tag' => $tag),
                )
            );
        }

        if (!preg_match('/^[\w-]+$/', $tag)) {
            return new JsonModel(
                array(
                    'status'  => 'error',
                    'message' => 'Must be letter or number and can include hyphens',
                    'data'    => array('pid' => $projectid, 'tag' => $tag),
                )
            );
        }

        $model = $this->tagService;
        $cnt = $model->getTagsUserCount($projectid, TagsRepository::TAG_TYPE_PROJECT);
        if ($cnt < 5) {
            if ($model->isTagsUserExisting($projectid, $tag)) {
                $resultJson = new JsonModel(
                    array(
                        'status'  => 'existing',
                        'message' => 'tag existing.',
                        'data'    => array('pid' => $projectid, 'tag' => $tag),
                    )
                );
            } else {
                $model->addTagUser($projectid, $tag, TagsRepository::TAG_TYPE_PROJECT);
                $resultJson = new JsonModel(
                    array(
                        'status'  => 'ok',
                        'message' => '',
                        'data'    => array('pid' => $projectid, 'tag' => $tag),
                    )
                );
            }
        } else {
            $resultJson = new JsonModel(
                array(
                    'status'  => 'error',
                    'message' => 'Max. 5 Tags',
                    'data'    => array('pid' => $projectid, 'tag' => $tag),
                )
            );
        }

        return $resultJson;
    }

    public function delAction()
    {
        $projectid = (int)$this->getParam('p');
        $tag = $this->getParam('t');

        $model = $this->tagService;
        $model->deleteTagUser($projectid, $tag, TagsRepository::TAG_TYPE_PROJECT);

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => 'Removed',
                'data'    => array('pid' => $projectid, 'tag' => $tag),
            )
        );
    }

    public function assignAction()
    {
        $objectId = (int)$this->getParam('oid');
        $objectType = (int)$this->getParam('ot', 10);
        $tag = HtmlPurifyService::purify($this->getParam('tag'));

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => '',
                'data'    => array('oid' => $objectId, 'tag' => $tag, 'type' => $objectType),
            )
        );
    }

    public function removeAction()
    {
        $objectId = (int)$this->getParam('oid');
        $objectType = (int)$this->getParam('ot', 10);
        $tag = HtmlPurifyService::purify($this->getParam('tag'));

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => '',
                'data'    => array('oid' => $objectId, 'tag' => $tag, 'type' => $objectType),
            )
        );
    }

    public function filterAction()
    {
        $model = $this->tagService;
        $filter = $this->getParam('q');
        $filter = strtolower($filter);
        $tags = $model->filterTagsUser($filter, 10);
        $result = array();
        foreach ($tags as $tag) {
            $result[] = array(
                'id'       => $tag['tag_name'],
                'text'     => $tag['tag_name'],
                'tag_id'   => $tag['tag_id'],
                'tag_name' => $tag['tag_name'],
            );
        }

        return new JsonModel(
            array(
                'status' => 'ok',
                'filter' => $filter,
                'data'   => array('tags' => $result),
            )
        );
    }

}
