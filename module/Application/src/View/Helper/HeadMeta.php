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

namespace Application\View\Helper;

use Laminas\View\Helper\HeadMeta as ZendHeadMeta;
use Laminas\View\Helper\Placeholder\Container\AbstractContainer;

class HeadMeta extends ZendHeadMeta
{

    const DELIMITER = ' - ';

    /**
     * @param null   $content
     * @param null   $keyValue
     * @param string $keyType
     * @param array  $modifiers
     * @param string $placement
     *
     * @return HeadMeta
     * @see \Laminas\View\Helper\HeadMeta::__invoke()
     */
    public function __invoke(
        $content = null,
        $keyValue = null,
        $keyType = 'name',
        $modifiers = array(),
        $placement = AbstractContainer::APPEND
    ) {
        parent::__invoke($content, $keyValue, $keyType, $modifiers, $placement);

        return $this;
    }

    /**
     * @see \Laminas\View\Helper\HeadMeta::append()
     */
    public function append($value)
    {
        //if ($value->name == 'description') {
        //    $this->updateDescription($value, AbstractContainer::APPEND);
        //} else if ($value->name == 'keywords') {
        //    $this->updateKeywords($value, AbstractContainer::APPEND);
        //} else {
        parent::append($value);
        //}
    }
}