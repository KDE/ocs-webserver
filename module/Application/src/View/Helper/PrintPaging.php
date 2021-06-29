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
 * */

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class PrintPaging extends AbstractHelper
{

    public function __invoke($total_records, $pageLimit, $currentPageNr, $containerClassname)
    {
        return $this->printPaging($total_records, $pageLimit, $currentPageNr, $containerClassname);
    }

    public function printPaging($total_records, $pageLimit, $currentPageNr, $containerClassname)
    {

        $total_pages = ceil($total_records / $pageLimit);
        if ($total_pages <= 1) {
            return '';
        }
        $html = '<div class="opendesktopwidgetpager"><ul class="opendesktopwidgetpager">';

        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $currentPageNr) {
                $html = $html . '<li class="active"><span class="' . $containerClassname . '">' . $i . '</span></li>';
            } else {
                $html = $html . '<li><span class="' . $containerClassname . '">' . $i . '</span></li>';
            }
        }

        $html = $html . '</ul></div>';

        return $html;
    }

}
