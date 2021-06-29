<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Form;

use Laminas\Form\Form;

/**
 * Class SettingsForm
 * @package Application\Form
 * @deprecated
 */
class SettingsForm extends Form
{

    /**
     * TODO not compatible anymore
     * private $highlightClass = 'input-error';
     *
     * public function isValid($data)
     * {
     * $valid = parent::isValid($data);
     *
     * $this->highlightErrors();
     *
     * return $valid;
     * }
     *
     * protected function highlightErrors()
     * {
     * foreach ($this->getElements() as $element) {
     *
     * if ($element->hasErrors()) {
     * $oldClass = $element->getAttrib('class');
     * if (!empty($oldClass)) {
     * $element->setAttrib('class', $oldClass . ' ' . $this->getHighlightClass());
     * } else {
     * $element->setAttrib('class', $this->getHighlightClass());
     * }
     * }
     * }
     * }
     *
     * /**
     *
     * @param string $highlightClass
     *
     * public function setHighlightClass($highlightClass)
     * {
     * $this->highlightClass = $highlightClass;
     * }
     *
     * @return string
     *
     * public function getHighlightClass()
     * {
     * return $this->highlightClass;
     * }
     *
     * /**
     */
}