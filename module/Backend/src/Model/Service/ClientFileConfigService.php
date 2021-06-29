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

namespace Backend\Model\Service;

use Application\Model\Service\StoreTemplateReader;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

class ClientFileConfigService
{

    /** @var string */
    protected $_clientName;
    /** @var  array */
    protected $_clientConfigData;
    protected $defaultConfigLoaded;

    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     *
     * @link http://php.net/manual/en/language.oop5.decon.php
     *
     * @param string $clientName
     */
    // function __construct($clientName)
    // {
    //     $this->_clientName = $clientName;
    // }

    private $storeTemplateReader;

    function __construct(StoreTemplateReader $storeTemplateReader)
    {
        $this->storeTemplateReader = $storeTemplateReader;
    }

    public function loadClientConfig()
    {
        $this->_clientConfigData = $this->storeTemplateReader->getStoreTemplate($this->_clientName);
    }

    /**
     * @return null|array
     */
    public function getConfig()
    {
        return $this->_clientConfigData;
    }

    /**
     * @return null|string
     */
    public function getClientName()
    {
        return $this->_clientName;
    }

    /**
     * @return null|string
     */
    public function setClientName($clientName)
    {
        $this->_clientName = $clientName;
    }

    /**
     */
    public function getForm()
    {
        $form = new Form();
        $form->add(
            [
                'type'       => 'hidden',
                'name'       => 'clientname',
                'attributes' => [
                    'value' => $this->_clientName,
                ],
            ]
        );
        foreach ($this->_clientConfigData as $key => $value) {
            $form = $this->generateSubForm($key, $value, $form);
        }

        return $form;
    }

    // /**
    //  * @return Zend_Form
    //  * @throws Zend_Form_Exception
    //  */
    // public function getForm()
    // {
    //     $form = new Backend_Form_Other_Config();
    //     $form->addElementPrefixPath('Backend_Form_Element', APPLICATION_PATH . '/backend/forms/elements');
    //     $form->addElement('hidden', 'client-name');
    //     $form->getElement('client-name')->setValue($this->_clientName);
    //     foreach ($this->_clientConfigData as $key => $value) {
    //         $form = $this->generateSubForm($key, $value, $form);
    //     }

    //     return $form;
    // }

    /**
     * @param string $key
     * @param mixed  $value
     * @param Form   $form
     *
     * @return mixed
     */
    private function generateSubForm($key, $value, $form)
    {

        // dont know what is this for ...
        // if (false === is_object($form->getSubForm($key))) {
        //     $newSubForm = new Backend_Form_Other_SubFormConfig();
        //     $form->addSubForm($newSubForm, $key);
        //     $form->getSubForm($key)->setLegend($key);
        // }

        // 'favicon' => '/images/system/storeLogo.png', 
        // is Text
        if (false === is_array($value)) {
            $form->add(
                [
                    'type'       => 'text',
                    'name'       => $key,
                    'options'    => [
                        'label' => $key,
                    ],
                    'attributes' => [
                        'value' => $value,
                    ],
                ]
            );

            return $form;
        }

        // when array
        $fieldset = new Fieldset($key, ['label' => $key]);
        $fieldset->setAttribute('id', 'fieldset-' . $key);
        foreach ($value as $itemKey => $itemValue) {
            // is Text            
            if (false === is_array($itemValue)) {
                $fieldset->add(
                    [
                        'type'       => 'text',
                        'name'       => $itemKey,
                        'options'    => [
                            'label' => $itemKey,
                        ],
                        'attributes' => [
                            'value' => $itemValue,
                        ],
                    ]
                );
                continue;
            }
            $subForm = $this->generateSubForm($itemKey, $itemValue, $fieldset);
            $form->add($subForm);
        }
        $form->add($fieldset);

        return $form;
    }


    // /**
    //  * @param string    $key
    //  * @param mixed     $value
    //  * @param Zend_Form $form
    //  * @return mixed
    //  * @throws Zend_Form_Exception
    //  */
    // private function generateSubForm($key, $value, $form)
    // {
    //     if (false === is_object($form->getSubForm($key))) {
    //         $newSubForm = new Backend_Form_Other_SubFormConfig();
    //         $form->addSubForm($newSubForm, $key);
    //         $form->getSubForm($key)->setLegend($key);
    //     }

    //     if (false === is_array($value)) {
    //         $form->addElement('text', $key);
    //         $form->$key->setValue($value);
    //         $form->$key->setLabel($key);

    //         return $form;
    //     }

    //     foreach ($value as $itemKey => $itemValue) {
    //         if (false === is_array($itemValue)) {
    //             $element = new Backend_Form_Element_Config($itemKey);
    //             $form->getSubForm($key)->addElement($element, $itemKey);
    //             $form->getSubForm($key)->$itemKey->setValue($itemValue);
    //             $form->getSubForm($key)->$itemKey->setLabel($itemKey);
    //             continue;
    //         }
    //         $subForm = $this->generateSubForm($itemKey, $itemValue, $form->getSubForm($key));
    //         $form->addSubForm($subForm, $key);
    //     }

    //     return $form;
    // }

    public function saveClientConfig($getAllParams, $clientName = null)
    {
        $clientFileId = '';
        if (false == empty($clientName)) {
            $clientFileId = '_' . $clientName;
        }
        $clientConfigPath = $GLOBALS['ocs_config']->settings->store->template->path;
        $clientConfigFileName = "client{$clientFileId}.ini.php";

        file_put_contents(
            $clientConfigPath . '/' . $clientConfigFileName, '<?php

return ' . var_export($getAllParams, true) . ';'
        );
    }

    /**
     * @return array
     */
    public function getClientConfigData()
    {
        return $this->_clientConfigData;
    }

    /**
     * @return mixed
     */
    public function getDefaultConfigLoaded()
    {
        return $this->defaultConfigLoaded;
    }

}