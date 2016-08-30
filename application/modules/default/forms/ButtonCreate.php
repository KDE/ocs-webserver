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

class Default_Form_ButtonCreate extends Zend_Form
{

    public function init()
    {
        $filterStripSlashes = new Zend_Filter_Callback('stripslashes');

        $title = $this->createElement('text', 'title')
            ->setRequired(true)
            //->addErrorMessage('ProjectAddFormTitleErr')
            ->setFilters(array('StringTrim', $filterStripSlashes))
            ->addValidator('StringLength', false, array(6, 60));

        $url = $this->createElement('text', 'link_1', array())
            ->addPrefixPath('Local_Validate', 'Local/Validate', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->setFilters(array('StringTrim'))
            ->addValidator('PartialUrl');

        /** @var Zend_Form_Element_Select $category */
        $category = $this->createElement('Select', 'project_category_id')
            ->setRequired(true)
            ->addErrorMessage("ProjectAddFormCatErr");
        $prjCatTable = new Default_Model_DbTable_ProjectCategory();
        $categoryList = $prjCatTable->fetchMainCatForSelect();
        $categoryValidator = new Zend_Validate_InArray(array_keys($categoryList));
        $category->addValidator($categoryValidator);
        $category->addMultiOptions($categoryList);

        $mailExistCheck = new Zend_Validate_Db_NoRecordExists(array('table' => 'member', 'field' => 'mail', 'exclude' => array('field' => 'is_deleted', 'value' => 1)));
        $mailExistCheck->setMessage('RegisterFormEmailErrAllwaysRegistered', Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);

        $mailValidCheck = $this->getMailValidator();

        $siblingNotEmptyValidator = new Local_Validate_NotEmptyXor(array('paypal_mail', 'dwolla_id'));

        $paypal_mail = $this->createElement('text', 'paypal_mail')
            ->setRequired(false)
            ->setAllowEmpty(false)
            ->addValidator($siblingNotEmptyValidator, true)
            ->removeDecorator('HtmlTag');

        $dwolla_id = $this->createElement('text', 'dwolla_id')
            ->setRequired(false)
            ->setAllowEmpty(false)
            ->addValidator($siblingNotEmptyValidator, true)
            ->removeDecorator('HtmlTag');

        $userExistCheck = new Zend_Validate_Db_NoRecordExists(array('table' => 'member', 'field' => 'username', 'exclude' => array('field' => 'is_deleted', 'value' => 1)));
        $userExistCheck->setMessage('This username already exists.', Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);
        $userEmptyCheck = new Zend_Validate_NotEmpty();
        $userEmptyCheck->setMessage('RegisterFormUsernameErr', Zend_Validate_NotEmpty::IS_EMPTY);
        $userNameLength = new Zend_Validate_StringLength(array('min' => 4, 'max' => 35));

        $user_name = $this->createElement('text', 'username')
            ->setRequired(true)
            ->addValidator($userEmptyCheck)
            ->addValidator($userExistCheck)
            ->addValidator($userNameLength);

        $user_mail = $this->createElement('text', 'mail')
            ->setRequired(true)
            ->addValidator($mailValidCheck, true)
            ->addValidator($mailExistCheck)
            ->removeDecorator('HtmlTag');

        $this->addElement($category)
            ->addElement($title)
            ->addElement($url)
            ->addElement($paypal_mail)
            ->addElement($dwolla_id)
            ->addElement($user_name)
            ->addElement($user_mail);
    }

    /**
     * @return Zend_Validate_EmailAddress
     * @throws Zend_Validate_Exception
     */
    protected function getMailValidator()
    {
        $mailValidCheck = new Zend_Validate_EmailAddress();
        $mailValidCheck->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID)
            ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_FORMAT)
            ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_LOCAL_PART)
            ->setMessage("RegisterFormEmailErrWrongHost", Zend_Validate_EmailAddress::INVALID_HOSTNAME)
            ->setMessage("RegisterFormEmailErrWrongHost2", Zend_Validate_Hostname::INVALID_HOSTNAME)
            ->setMessage("RegisterFormEmailErrHostLocal", Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED)
            ->setOptions(array('domain' => TRUE));
        return $mailValidCheck;
    }

    public function preValidation($data)
    {
        if (!empty($data['paypal_mail'])) {
            $this->paypal_mail->addValidator($this->getMailValidator());
        }
        return $this;
    }

} 