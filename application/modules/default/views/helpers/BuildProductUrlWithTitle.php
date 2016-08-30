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
/**
 * Class Default_View_Helper_BuildProductUrlWithTitle
 * @deprecated
 */
class Default_View_Helper_BuildProductUrlWithTitle
{

    /**
     * @param object $dataProduct
     * @param string $action
     * @param array $params
     * @return string
     * @deprecated
     */
    public function buildProductUrlWithTitle($dataProduct, $action = 'show', $params = null)
    {
        $url_param = '';
        if (is_array($params)) {
            array_walk($params, create_function('&$i,$k', '$i="$k/$i/";'));
            $url_param = implode('/', $params);
        }

        return '/p/' . $dataProduct->project_id . '/' . $action . '/' . stripslashes(urlencode(str_replace(' ', '_', $dataProduct->title))) . '/' . $url_param;
    }

}