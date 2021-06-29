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

namespace Application\Model\Service;

use Application\Model\Service\Interfaces\BbcodeServiceInterface;
use Decoda\Decoda;
use Decoda\Filter\BlockFilter;
use Decoda\Filter\CodeFilter;
use Decoda\Filter\DefaultFilter;
use Decoda\Filter\ImageFilter;
use Decoda\Filter\ListFilter;
use Decoda\Filter\TableFilter;
use Decoda\Filter\TextFilter;
use Decoda\Filter\UrlFilter;

class BbcodeService extends BaseService implements BbcodeServiceInterface
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

        $code = new Decoda('ocs', array('strictMode' => false));
        $code->addHook(new \Decoda\Hook\CensorHook());
        $code->addFilter(new DefaultFilter());
        $code->addFilter(new UrlFilter());
        $code->addFilter(new ListFilter());
        $code->addFilter(new TextFilter());
        $code->addFilter(new BlockFilter());
        $code->addFilter(new CodeFilter());
        $code->addFilter(new ImageFilter());
        $code->addHook(new \Decoda\Hook\EmoticonHook(array('path' => '/emoticons/')));
        $code->addFilter(new TableFilter());
        $code->reset($bbcode);

        return $code->parse();
    }
}