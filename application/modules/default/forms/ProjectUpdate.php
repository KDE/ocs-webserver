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
class Default_Form_ProjectUpdate extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setAction('');
        $this->setAttrib('enctype', 'multipart/form-data');

    }

    public function init()
    {
        $this->setDecorators(array('FormElements', 'Form'));
        $this->setAttrib('class', 'standard-form span6 offset3');

        $filterStripSlashes = new Zend_Filter_Callback('stripslashes');

        $projectUpdateId = $this->createElement('hidden', 'upid')
            ->removeDecorator('HtmlTag')
            ->removeDecorator('Description');

        $title = $this->createElement('text', 'title')
            ->setLabel('Update Title')
            ->setRequired(true)
            ->addErrorMessage('ProjectAddFormTitleErr')
            ->setFilters(array('StringTrim', $filterStripSlashes))
            ->setDecorators(
                array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array('ViewScript', array(
                        'viewScript' => 'product/viewscripts/input_default.phtml',
                        'placement' => false
                    ))
                ));

        $description = $this->createElement('textarea', 'description', array('cols' => 30, 'rows' => 3))
            ->setLabel('Description')
            ->setRequired(true)
            ->addErrorMessage('ProjectAddFormDescErr')
            ->setDecorators(
                array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array('ViewScript', array(
                        'viewScript' => 'product/viewscripts/input_default.phtml',
                        'placement' => false
                    ))
                ));

        $embed_code = $this->createElement('textarea', 'embed_code', array('cols' => 30, 'rows' => 3))
            ->setLabel('Embedded content (Youtube, Vimeo, Soundcloud etc.)')
            ->setRequired(false)
            ->addErrorMessage('ProjectAddFormEmbedErr')
            ->setDecorators(
                array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array('ViewScript', array(
                        'viewScript' => 'product/viewscripts/input_default.phtml',
                        'placement' => false
                    ))
                ));


        $link_1 = $this->createElement('text', 'link_1', array())
            ->setLabel('Product Page Link')
            ->setRequired(false)
            ->addErrorMessage("ProjectAddFormFacebookErr")
            ->setFilters(array('StringTrim'))
            ->setDecorators(
                array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array('ViewScript', array(
                        'viewScript' => 'product/viewscripts/input_default.phtml',
                        'placement' => false
                    ))
                ))
            ->addPrefixPath('Local_Validate', 'Local/Validate', Zend_Form_Element::VALIDATE)
            ->addValidator('SanitizeUrl', true);

        $submit = $this->createElement('button', 'send');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('class', 'span3 btn btn-submit pull-right');
        $submit->setAttrib('type', 'submit');

        $cancel = $this->createElement('button', 'cancel');
        $cancel->setLabel('Cancel');
        $cancel->setDecorators(array('ViewHelper'));
        $cancel->setAttrib('class', 'span3 btn btn-submit pull-right');
        $cancel->setAttrib('type', 'submit');


        $this->addElement($title)
            ->addElement($projectUpdateId)
            ->addElement($description)
            ->addElement($embed_code)
//            ->addElement($link_1)
//            ->addElement($small_picture)
            ->addElement($cancel)
            ->addElement($submit);
    }

}

