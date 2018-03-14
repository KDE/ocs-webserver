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
/**
 * Description of Backend_Form_Project
 *
 * @author Björn Schramke
 */
class Backend_Form_Project extends Zend_Form
{

    public function init()
    {
        $filterStripSlashes = new Zend_Filter_Callback('stripslashes');

        $validStringLength = new Zend_Validate_StringLength();
        $validStringLength->setMax(140);
        $validStringLength->setMin(1);

        $imageTable = new Default_Model_DbTable_Image();


        $title = $this->createElement('text', 'title')
            ->setLabel('Product Name')
            ->setRequired(true)
            ->setFilters(array('StringTrim', $filterStripSlashes));


        $category = $this->createElement('select', 'project_category_id')
            ->setLabel('Category')
            ->setRequired(true);
        $projCattable = new Default_Model_DbTable_ProjectCategory();
        $categoryList = $projCattable->getSelectList();
        $categoryValidator = new Zend_Validate_InArray(array_keys(array_slice($categoryList, 1, null, true)));
        $category->addValidator($categoryValidator);
        $category->addMultiOptions($categoryList);


        $member = $this->createElement('select', 'member_id')
            ->setLabel('Zu welchem Mitglied gehört dieses Projekt?')
            ->setRequired(true);
        $memberTable = new Default_Model_Member();
        $memberList = $memberTable->getMembersForSelectList();
        $memberValidator = new Zend_Validate_InArray(array_keys(array_slice($memberList, 1, null, true)));
        $member->addValidator($memberValidator)
            ->addMultiOptions($memberList);


        $description = $this->createElement('textarea', 'description', array('cols' => 30, 'rows' => 3))
            ->setLabel('Product Description')
            ->setRequired(true);


        $short_description = $this->createElement('textarea', 'short_text', array('cols' => 30, 'rows' => 3))
            ->setLabel('Product Short Description')
            ->setRequired(true)
            ->addValidator($validStringLength);

        $member = $this->createElement('select', 'member_id')
            ->setLabel('Zu welchem Mitglied gehört dieses Projekt?')
            ->setRequired(true)
            ->addErrorMessage("Sie müssen ein Projekt auswählen");

        $memberTable = new Default_Model_Member();
        $memberList = $memberTable->getMembersForSelectList();
        $memberValidator = new Zend_Validate_InArray(array_keys(array_slice($memberList, 1, null, true)));
        $member->addValidator($memberValidator)
            ->addMultiOptions($memberList);


        $activeProject = $this->createElement('checkbox', 'is_active')
            ->setLabel("Projekt aktivieren und im Frontend anzeigen");


        $video = $this->createElement('textarea', 'embed_code', array('cols' => 30, 'rows' => 3))
            ->setLabel('HTML or Embed Media Code (Youtube, Vimeo, Soundcloud etc.)')
            ->setRequired(false)
            ->setAttrib('placeholder', '<iframe src="https://www.youtube.com/embed/XXXXXXXX" style="position:absolute;width:641px;height:360px;left:0" width="641" height="360" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>')
            ->setAttrib("stylestyle", "padding: 0;");


        $link_1 = $this->createElement('text', 'link_1', array())
            ->setLabel('Link to your product')
            ->setRequired(false)
            ->setFilters(array('StringTrim'));

        $facebook = $this->createElement('text', 'facebook_code', array())
            ->setLabel('Your Product on Facebook')
            ->setRequired(false)
            ->setFilters(array('StringTrim'));

        $twitter_code = $this->createElement('text', 'twitter_code', array())
            ->setLabel('Your Product on Twitter')
            ->setRequired(false)
            ->setFilters(array('StringTrim'));


        $google_code = $this->createElement('text', 'google_code', array())
            ->setLabel('Your Product on Google+')
            ->setRequired(false)
            ->setFilters(array('StringTrim'));


        $hiddenProductPicture = $this->createElement('hidden', 'image_small')
            ->setAttrib('data-target', '#product-picture-preview');

        $previewProductPicture = new Local_Form_Element_Note('note', array('name' => 'image_small_preview', 'value' => '<img id="product-picture-preview" src="" alt="product picture" width="110">'));

        $productPicture = $this->createElement('file', 'image_small_upload')
            ->setDisableLoadDefaultDecorators(true)
            ->setLabel('Product Logo (min. 20x20, max. 1000x1000, max. 2MB)')
            //->setDescription('(min. 50x50, max. 1000x1000, 2MB)')
            ->setRequired(true)
            ->setAttrib('class', 'product-picture')
            ->setAttrib('onchange', 'ImagePreview.previewImage(this, \'product-picture-preview\');')
            ->addValidator('Count', false, 1)
            //->addValidator('Size', false, 2097152)
            ->addValidator('FilesSize', false, 2000000)
            ->addValidator('Extension', false, $imageTable->getAllowedFileExtension())
            ->addValidator('Size', false, array('min' => '5B', 'max' => '2MB'))
            ->addValidator('ImageSize', false,
                array('minwidth'  => 20,
                      'minheight' => 20,
                	  'maxwidth'  => 1000,
                      'maxheight' => 1000
                ))
            ->addValidator('MimeType', false, $imageTable->getAllowedMimeTypes());

        $hiddenTitlePicture = $this->createElement('hidden', 'image_big')
            ->setAttrib('data-target', '#image_big-element');

        $previewTitlePicture = new Local_Form_Element_Note('note', array('name' => 'image_big_preview', 'value' => '<img id="title-picture-preview" src="" alt="title picture" width="110">'));

        $titlePicture = $this->createElement('file', 'image_big_upload')
            ->setDisableLoadDefaultDecorators(true)
            ->setLabel('Banner (min. 100x100, max. 2000x1200, max. 2MB)')
            ->setRequired(false)
            ->setAttrib('class', 'title-picture')
            ->setAttrib('onchange', 'ImagePreview.previewImage(this, \'title-picture-preview\');')
            ->addValidator('Count', false, 1)
            ->addValidator('Size', false, 2097152)
            ->addValidator('FilesSize', false, 2000000)
            ->addValidator('Extension', false, $imageTable->getAllowedFileExtension())
            ->addValidator('ImageSize', false,
                array('minwidth' => 100,
                    'maxwidth' => 2000,
                    'minheight' => 100,
                    'maxheight' => 1200
                ))
            ->addValidator('MimeType', false, $imageTable->getAllowedMimeTypes());


        $this->addElement($activeProject)
            ->addElement($title)
            ->addElement($category)
            ->addElement($member)
            ->addElement($short_description)
            ->addElement($description)
            ->addElement($hiddenProductPicture)
            ->addElement($previewProductPicture)
            ->addElement($productPicture)
            ->addElement($hiddenTitlePicture)
            ->addElement($previewTitlePicture)
            ->addElement($titlePicture)
            ->addElement($video)
            ->addElement($facebook)
            ->addElement($twitter_code)
            ->addElement($google_code)
            ->addElement($link_1)
            ->addElement('submit', 'send', array('label' => 'Speichern'))
            ->addElement('submit', 'sendclose', array('label' => 'Speichern & Schließen'));
    }

}
