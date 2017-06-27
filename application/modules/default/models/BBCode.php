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
 *
 * Created: 22.06.2017
 */
class Default_Model_BBCode
{

    /**
     * @param string $bbcode
     *
     * @return string
     */
    public static function renderHtml($bbcode)
    {
        if (empty($bbcode)) {
            return '';
        }
        
        Zend_Markup::addParserPath('Local_Markup_Parser', APPLICATION_LIB . '/Local/Markup/Parser');
        $parser = Zend_Markup::factory('BbcodeCI');
        return $parser->render($bbcode);
    }

}