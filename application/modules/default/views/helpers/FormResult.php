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
class Default_View_Helper_FormResult
{

    /**
     * @param Zend_Form $form
     * @param boolean $saved
     * @param boolean $errorGlobal
     * @param string $successMessage
     * @return string
     */
    function formResult($form, $saved, $errorGlobal = false, $successMessage = null)
    {
        $errors = $form->getMessages();


        if (count($errors)) {
            if ($errorGlobal) {
                $htmlErrors = '<ul>';
                foreach ($errors as $element) {
                    if (is_string($element)) {
                        $htmlErrors .= '<li class="text-error">' . $element . '</li>';
                    } else {
                        foreach ($element as $errorid => $errorValue) {
                            $htmlErrors .= '<li class="text-error">' . $errorValue . '</li>';
                        }
                    }
                }
                $htmlErrors .= '</ul>';
                return $htmlErrors;
            } else {
                return '<div class="text-error">An error occured, please checkout the detailed error messages.</div>';
            }

        } else {
            if ($saved) {
                if ($successMessage == null) {
                    return '<div class="form-success">Changes saved.</div>';
                } else {
                    return '<div class="form-success">' . $successMessage . '</div>';
                }
            }
        }
    }
}
