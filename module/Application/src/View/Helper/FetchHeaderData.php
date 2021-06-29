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

use Application\Model\Service\InfoService;
use Application\Model\Service\Interfaces\InfoServiceInterface;
use Application\Model\Service\SectionService;
use Laminas\View\Helper\AbstractHelper;

class FetchHeaderData extends AbstractHelper
{

    /**
     * @var SectionService
     */
    private $section;

    private $info;

    public function __construct(SectionService $section, InfoServiceInterface $infoService)
    {
        $this->section = $section;
        $this->info = $infoService;
    }

    /**
     * @param int $cat_id
     *
     * @return array
     */
    public function __invoke($cat_id)
    {
        $j_section = array();
        if ($cat_id) {
            $s = $this->section->fetchSectionForCategory($cat_id);


            if ($s && $s['section_id']) {
                $sectionStats = $this->section->fetchSectionStatsLastMonth($s['section_id']);

                $j_section['section_id'] = $s['section_id'];
                $j_section['name'] = $s['name'];
                $j_section['amount'] = $this->section->fetchProbablyPayoutLastMonth($s['section_id']);
                $j_section['amount_factor'] = $j_section['amount'] * $sectionStats['factor'];

                $goal = ceil($j_section['amount_factor'] / 500) * 500;
                $j_section['amount'] = number_format($j_section['amount'], 2, '.', '');
                $j_section['amount_factor'] = number_format($j_section['amount_factor'], 2, '.', '');
                $j_section['goal'] = ($goal == 0 ? 500 : $goal);

                $supporters = $this->info->getNewActiveSupportersForSectionUnique($s['section_id']);
                $supporterMonths = $this->info->getSectionSupportersActiveMonths($s['section_id']);

                $image = new Image();

                $sp = array();
                if (!empty($supporters) && is_array($supporters) && count($supporters) > 0) {
                    foreach ($supporters as $s) {
                        $t = array();
                        $t['username'] = $s['username'];
                        $t['profile_image_url'] = $image->Image(
                            $s['profile_image_url'], array(
                                                       'width'  => 100,
                                                       'height' => 100,
                                                   )
                        );
                        $t['sum_support'] = $s['sum_support'];
                        foreach ($supporterMonths as $m) {
                            if ($m['member_id'] == $s['member_id']) {
                                $t['active_months'] = $m['active_months'];

                                break;
                            }
                        }
                        $sp[] = $t;
                    }
                    foreach ($sp as &$s) {
                        $year = floor($s['active_months'] / 12);
                        if ($year > 0) {
                            $syear = $year . ' ' . ($year > 1 ? 'years' : 'year');
                        } else {
                            $syear = '';
                        }
                        $month = $s['active_months'] % 12;
                        $smonth = '';
                        if ($month > 0) {
                            $smonth = $month > 1 ? ($month . ' months ') : ($month . ' month');
                        }
                        $s['period'] = $syear . (($year > 0 && $month > 0) ? ' and ' : '') . $smonth;
                    }
                }
                $j_section['supporters'] = $sp;
            }
        }

        $config = $GLOBALS['ocs_config'];
        $baseurl = $config->settings->client->default->baseurl;
        $baseurlStore = $config->settings->client->default->baseurl_store;
        $data = [];
        $data['baseurl'] = $baseurl;
        $data['baseurlStore'] = $baseurlStore;
        $data['sectiondata'] = $j_section;
        $data['ocsStoreTemplate'] = $this->getCleanStoreTemplate();
        $data['ocsStoreConfig'] = $this->getCleanStoreConfig();
        $data['ocsConfig'] = $this->getCleanConfig();

        return $data;
    }

    private function getCleanStoreTemplate()
    {
        $temp = $GLOBALS['ocs_store']->template;
        $wantedKeys = array(
            'header-logo'     => 0,
            'header-nav-tabs' => 0,
            'header'          => 0,
            'homepage'          => 0,
            'footer_heading'          => 0,
            'domain'          => 0,

        );

        return array_intersect_key($temp, $wantedKeys);
    }

    private function getCleanStoreConfig()
    {
        $temp = (array)$GLOBALS['ocs_store']->config;
        $wantedKeys = array(
            'host'                       => 0,
            'name'                       => 0,
            'is_show_title'              => 0,
            'is_show_real_domain_as_url' => 0,
        );

        return array_intersect_key($temp, $wantedKeys);
    }

    private function getCleanConfig()
    {
        $temp = $GLOBALS['ocs_config']->settings->client->default;

        $wantedKeys = array(
            'url_forum'     => 0,
            'url_gitlab'    => 0,
            'url_blog'      => 0,
            'baseurl'       => 0,
            'baseurl_store' => 0,
        );

        $client = array_intersect_key($temp->toArray(), $wantedKeys);

        return $client;
        // $temp = $GLOBALS['ocs_config']->settings->server;
        // $wantedKeys = array(
        //     'images' => 0,
        //     'videos' => 0,
        //     'comics' => 0,
        // );
        // $server = array_intersect_key($temp->toArray(), $wantedKeys);

        // return array_merge($client, $server);;
    }
}