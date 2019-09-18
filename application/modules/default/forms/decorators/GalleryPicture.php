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
class Default_Form_Decorator_GalleryPicture extends Zend_Form_Decorator_Abstract
{

    const TYPE_PICTURE_ONLINE = 0;
    const TYPE_PICTURE_UPLOAD = 1;

    private $type = self::TYPE_PICTURE_ONLINE;

    public function __construct($options = null)
    {
        $this->type = $options["type"];
        parent::__construct($options);
    }

    public function render($content)
    {
        $value = $this->getElement()->getValue();
        if (!isset($value) && $this->type === self::TYPE_PICTURE_ONLINE) {
            return '';
        }
        $check_divs = '';
        if ($this->type === self::TYPE_PICTURE_ONLINE) {
            $check_divs = '<div class="absolute icon-check" style="display: block;"></div>
            <div class="absolute icon-cross" style="display: none;"></div><img src="' . $this->getElement()->getView()->Image($value, array('width' => '110', 'height' => '77')) . '" />';
        }

        return '<div class="product-image relative">'
               . $content . $check_divs .
               '</div>';
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

}