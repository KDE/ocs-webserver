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

class Default_Model_DbTable_ProjectWidgetDefault extends Local_Model_Table
{
    protected $_key = 'widget_id';

    protected $_keyColumnsForRow = array('project_id');

    protected $_name = 'project_widget_default';

    protected $_primary = 'widget_id';

    /**
     * @param $projectId
     * @return mixed|stdClass returns default config if nothing found
     */
    public function fetchConfig($projectId)
    {
        $defaultConfig = $this->fetchRow(array('project_id = ?' => $projectId));

        if (empty($defaultConfig)) {
            return $this->getDefaultConfig($projectId);
        }
        $temp = json_decode($defaultConfig->config);
        $temp->uuid = $defaultConfig['uuid'];
        $temp->project = $defaultConfig['project_id'];
        $temp->embedCode = '';
        return $temp;
    }

    private function getDefaultConfig($projectId)
    {
        $defaults = new stdClass();
        $defaults->text = new stdClass();
        $modelProject = new Default_Model_Project();
        $dataProject = $modelProject->fetchRow(array('project_id = ?' => $projectId));
        $modelMember = new Default_Model_Member();
        $dataMember = $modelMember->fetchRow(array('member_id = ?' => $dataProject->member_id));
        if ((false === empty($dataMember->paypal_mail)) OR (false === empty($dataMember->dwolla_id))) {
            $defaults->text->content = "I'm currently raising money through opendesktop.org . Click the donate button to help!";
        } else {
            $defaults->text->content = "Discover more about my product on opendesktop.org .";
        }
        $defaults->text->headline = $dataProject->title;
        $defaults->text->button = "Pling it!";

        $defaults->amounts = new stdClass();
        $defaults->amounts->donation = 10;
        $defaults->amounts->showDonationAmount = true;
        $defaults->amounts->current = '';
        $defaults->amounts->goal = '';

        $defaults->colors = new stdClass();
        $defaults->colors->widgetBg = '#2673B0';
        $defaults->colors->widgetContent = '#ffffff';
        $defaults->colors->headline = '#ffffff';
        $defaults->colors->text = '#000000';
        $defaults->colors->button = '#428bca';
        $defaults->colors->buttonText = '#ffffff';

        $defaults->showSupporters = true;
        $defaults->showComments = true;
        $defaults->logo = 'grey';

        $defaults->project = '';
        $defaults->uuid = '';
        $defaults->embedCode = '';

        return $defaults;
    }

    public function insert(array $data)
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = md5($data['config']);
        }

        return parent::insert($data);
    }

    public function save($data)
    {
        $data['uuid'] = $this->generateConfigUuid($data);

        return parent::save($data);
    }

    private function generateConfigUuid($data)
    {
        return md5($data['config']);
    }


}