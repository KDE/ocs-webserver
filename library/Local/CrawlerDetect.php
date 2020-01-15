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
    // If the user agent is empty, we assume that it is not a bot.
    if (empty($USER_AGENT)) {
        return false;
    }

    $crawlers = array(
        array('Googlebot', 'Googlebot'),
        array('MSN', 'MSN'),
        array('msnbot-media', 'MSN'),
        array('bingbot', 'MSN'),
        array('MegaIndex.ru' , 'MegaIndex.ru'),
        array('Baiduspider', 'Baiduspider'),
        array('YandexBot', 'YandexBot'),
        array('AhrefsBot', 'Mozilla/5.0 (compatible; AhrefsBot/6.1; +http://ahrefs.com/robot/)'),
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
        array('MJ12bot','Mozilla/5.0 (compatible; MJ12bot/v1.4.8; http://mj12bot.com/)'),
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
        array('MauiBot','MauiBot (crawler.feedback+dc@gmail.com)'),
        array('istellabot','istellabot/t.1.13'),
        array('SeznamBot','Mozilla/5.0 (compatible; SeznamBot/3.2-test1; +http://napoveda.seznam.cz/en/seznambot-intro/)'),
        array('TelegramBot','TelegramBot (like TwitterBot)'),
        array('Synapse','Synapse/1.0.0'),
        array('VelenPublicWebCrawler','Mozilla/5.0 (compatible; VelenPublicWebCrawler/1.0; +https://velen.io)'),
        array('MagiBot','Mozilla/5.0 (compatible; MagiBot/1.0.0; Matarael; +https://magi.com/bots)'),
        array('linkfluence','Mozilla/5.0 (compatible; YaK/1.0; http://linkfluence.com/; bot@linkfluence.com)'),
        array('repology','repology-linkchecker/1 (+https://repology.org/bots)'),
        array('yacybot','Mozilla/5.0 (compatible; yacybot/1.921/custom +https://searx.everdot.org/about)'),
        array('facebookexternalhit','facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)'),
        array('ZoominfoBot','ZoominfoBot (zoominfobot at zoominfo dot com)'),
        array('curl','curl/7.66.0'),
        array('ZoomBot','ZoomBot (Linkbot 1.0 http://suite.seozoom.it/bot.html)'),
        array('PaperLiBot','Mozilla/5.0 (compatible; PaperLiBot/2.1; https://support.paper.li/entries/20023257-what-is-paper-li)'),
        array('python-requests','python-requests/2.22.0'),
        array('Cliqzbot','Mozilla/5.0 (compatible; Cliqzbot/3.0; +http://cliqz.com/company/cliqzbot)'),
        array('YisouSpider','YisouSpider'),
        array('trendictionbot','Mozilla/5.0 (Windows NT 10.0; Win64; x64; trendictionbot0.5.0; trendiction search; http://www.trendiction.de/bot; please let us know of any problems; web at trendiction.com) Gecko/20170101 Firefox/67.0'),
        array('Seekport','Mozilla/5.0 (compatible; Seekport Crawler; http://seekport.com/)'),
        array('GarlikCrawler','GarlikCrawler/1.2 (http://garlik.com/, crawler@garlik.com)')
    );

    foreach ($crawlers as $c)
    {
        if (stristr($USER_AGENT, $c[0]))
        {
            return true;
        }
    }

    return false;
}
