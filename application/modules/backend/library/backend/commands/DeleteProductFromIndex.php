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
class Backend_Commands_DeleteProductFromIndex implements Local_Queue_CommandInterface
{

    protected $productId;
    protected $catId;

    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     * @param int $productId
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct($productId, $catId)
    {
        $this->productId = $productId;
        $this->catId = $catId;
    }


    public function doCommand()
    {
        return $this->deleteProductFromIndex();
    }

    protected function deleteProductFromIndex()
    {
        if (empty($this->productId) OR empty($this->catId)) {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - no productId or catId was set.');
            return;
        }

        $product = array();
        $product['project_id'] = $this->productId;
        $product['project_category_id'] = $this->catId;
        
        $modelSearch = new Default_Model_Search_Lucene();
        $modelSearch->deleteDocument($product);
    }
    
}