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
class Backend_Form_Ranking extends Zend_Form
{
    public function init()
    {
        parent::init();
        $this->setMethod(Zend_Form::METHOD_POST);

        $weight_plings = new Zend_Form_Element_Text('weight_plings');
        $weight_plings->setLabel('Weight for Plings');
        $this->addElement($weight_plings);

        $weight_views = new Zend_Form_Element_Text('weight_views');
        $weight_views->setLabel('Weight for Views');
        $this->addElement($weight_views);

        $weight_updates = new Zend_Form_Element_Text('weight_updates');
        $weight_updates->setLabel('Weight for Updates');
        $this->addElement($weight_updates);

        $weight_comments = new Zend_Form_Element_Text('weight_comments');
        $weight_comments->setLabel('Weight for Comments');
        $this->addElement($weight_comments);

        $weight_followers = new Zend_Form_Element_Text('weight_followers');
        $weight_followers->setLabel('Weight for Followers');
        $this->addElement($weight_followers);

        $weight_supporters = new Zend_Form_Element_Text('weight_supporters');
        $weight_supporters->setLabel('Weight for Supporters');
        $this->addElement($weight_supporters);

        $weight_money = new Zend_Form_Element_Text('weight_money');
        $weight_money->setLabel('Weight for Money');
        $this->addElement($weight_money);

        $this->addElement('submit', 'send', array('label' => 'Speichern'));

    }

}
