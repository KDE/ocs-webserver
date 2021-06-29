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

namespace Application\View\Helper;


use Laminas\Config\Config;

/**
 * Helper trait for auto-completion of code in modern IDEs.
 *
 * The trait provides convenience methods for view helpers,
 * defined by the Plugin component.
 * It is designed to be used for type-hinting $this variable
 * inside zend-view templates via doc blocks.
 *
 * The base class is PhpRenderer, followed by the helper trait from
 * the Plugin component. However, multiple helper traits
 * from different Laminas components can be chained afterwards.
 *
 * @example @var \Laminas\View\Renderer\PhpRenderer|CurrentUserHelperTrait $this
 *
 * @method Config configHelp($section = null)
 */
trait ConfigHelperTrait
{
}