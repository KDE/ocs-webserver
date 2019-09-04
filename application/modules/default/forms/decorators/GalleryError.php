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
class Default_Form_Decorator_GalleryError extends Zend_Form_Decorator_Abstract
{


    public function render($content)
    {
        $element = $this->getElement();
        $view = $element->getView();
        if (null === $view) {
            return $content;
        }

        $errors = $element->getMessages();
        if (empty($errors)) {
            return $content;
        }

        //The getFlattenedMessages is a workaround - because Zend FormErrors-Decorator doesn't work properly with SubForms
//        return $content . '<div class="field-missing-container absolute clearfix">
//            <div class="field-missing-left"></div>
//            <div class="field-missing">' . $view->formErrors($this->getFlattenedMessages($errors)) . '</div>
//        </div>';
//        return $content . '<div id="'.$element->getName().'-error" class="clear error">' . $view->formErrors($this->getFlattenedMessages($errors)) . '</div>';
        $errorHtml = '';
        foreach ($this->getFlattenedMessages($errors) as $currentError) {
            $errorHtml .= '<label id="' . $element->getName() . '-error" class="clear error" for="' . $element->getName() . '" style="width:100%">' . $currentError . '</label>';
        }

        return $content . $errorHtml;
    }


    private function getFlattenedMessages($messages)
    {
        $arr = array();
        foreach ($messages as $k => $element) {
            if (!is_array($element)) {
                $arr[$k] = $element;
            } else {
                foreach ($this->getFlattenedMessages($element) as $k2 => $subElement) {
                    $arr[$k . '_' . $k2] = $subElement;
                }
            }
        }

        return $arr;
    }

}