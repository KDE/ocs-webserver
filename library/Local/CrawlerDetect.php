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

/** crawler detection
 * @param $USER_AGENT
 * @return bool
 */
function crawlerDetect($USER_AGENT)
{
    $crawlers = array(
        array('Googlebot', 'Googlebot'),
        array('MSN', 'MSN'),
        array('msnbot-media', 'MSN'),
        array('bingbot', 'MSN'),
        array('MegaIndex.ru' , 'MegaIndex.ru'),
        array('Baiduspider', 'Baiduspider'),
        array('YandexBot', 'YandexBot'),
        array('AhrefsBot', 'ahrefs.com/robot'),
        array('ltx71', 'ltx71'),
        array('msnbot', 'MSN'),
        array('Rambler', 'Rambler'),
        array('Yahoo', 'Yahoo'),
        array('AbachoBOT', 'AbachoBOT'),
        array('accoona', 'Accoona'),
        array('AcoiRobot', 'AcoiRobot'),
        array('ASPSeek', 'ASPSeek'),
        array('CrocCrawler', 'CrocCrawler'),
        array('Dumbot', 'Dumbot'),
        array('FAST-WebCrawler', 'FAST-WebCrawler'),
        array('GeonaBot', 'GeonaBot'),
        array('Gigabot', 'Gigabot'),
        array('Lycos', 'Lycos spider'),
        array('MSRBOT', 'MSRBOT'),
        array('Scooter', 'Altavista robot'),
        array('AltaVista', 'Altavista robot'),
        array('IDBot', 'ID-Search Bot'),
        array('eStyle', 'eStyle Bot'),
        array('Scrubby', 'Scrubby robot'),
        array('MJ12bot','http://mj12bot.com/'),
        array('SemrushBot', 'SemrushBot'),
        array('bingbot','bingbot'),
        array('DotBot','http://www.opensiteexplorer.org/dotbot'),
        array('SEOkicks','https://www.seokicks.de/robot.html'),
        array('CCBot','CCBot/2.0 (https://commoncrawl.org/faq/)'),
        array('Sogou','Sogou web spider/4.0(+http://www.sogou.com/docs/help/webmasters.htm#07)'),
        array('Bytespider','Bytespider;https://zhanzhang.toutiao.com/'),
        array('BLEXBot','BLEXBot/1.0; +http://webmeup-crawler.com/'),
        array('Applebot','Applebot/0.1; +http://www.apple.com/go/applebot'),
        array('serpstatbot','serpstatbot/1.0 (advanced backlink tracking bot; curl/7.58.0; http://serpstatbot.com/; abuse@serpstatbot.com)'),
        array('Linespider','Linespider/1.1;+https://lin.ee/4dwXkTH'),
        array('Yeti','Yeti/1.1; +http://naver.me/spd'),
        array('Feedspot','Feedspot/1.0 (+https://www.feedspot.com/fs/fetcher; like FeedFetcher-Google)'),
        array('fantastic_search_engine_crawler','fantastic_search_engine_crawler/2.0 (Linux) fantastic-crawler@umich.edu'),
        array('Qwantify','Qwantify/Bleriot/1.1; +https://help.qwant.com/bot'),
        array('coccocbot','coccocbot-web/1.0; +http://help.coccoc.com/searchengine'),
        array('nagios-plugins','check_http/v2.2.1 (nagios-plugins 2.2.1)'),
        array('urlwatch','urlwatch/2.17 (+https://thp.io/2008/urlwatch/info.html)'),
        array('Buck','Buck/2.2; (+https://app.hypefactors.com/media-monitoring/about.html)'),
        array('Anitya','Anitya 0.17.2 at release-monitoring.org'),
        array('MauiBot','MauiBot (crawler.feedback+dc@gmail.com)')
    );

    foreach ($crawlers as $c)
    {
        if (stristr($USER_AGENT, $c[0]))
        {
            return($c[1]);
        }
    }

    return false;
}
