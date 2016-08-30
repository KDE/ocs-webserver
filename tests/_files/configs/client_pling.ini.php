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

return array(
    'favicon' => '/favicon.ico',
    'logo' => '/theme/flatui/img/new/pling-logo-large.png',

    'head' => array(
        'browser_title' => 'pling.it',
        'browser_title_prepend' => 'pling.it - ',
        'meta_author' => 'Pling US, LLC',
        'meta_description' => '',
        'meta_keywords' => ''
    ),

    'homepage' => array(
        'logo' => array(
            'background-image' => 'url(\'/theme/flatui/img/new/pling-logo-large.png\')',
            'background-position' => 'inherit',
            'background-repeat' => 'no-repeat',
            'height' => '108px',
            'width' => '200px'
        ),
        'headline' => '<h2>The Donation Platform</h2>
                        <h3>to support free products and services.</h3>

                        <p>
                            <a class="btn btn-primary btn-native btn-large watch-video" href="#homepage-video"
                               data-toggle="modal">Watch Video</a>
                            <a class="btn btn-primary btn-pling-red btn-large" href="/register">Get Started</a>
                        </p>',
  ),
  'footer_heading' => 'The Donation Platform - pling.it',
  'domain' => 'pling.it',
  'header' =>
  array (
      'background-image' => 'none',
      'background-color' => '#e2e2e2',
      'color' => '#ffffff',
      'height' => '90px',
  ),
  'header-logo' =>
  array (
      'background-image' => 'none',
      'height' => '76px',
      'width' => '140px',
      'top' => '10px',
      'left' => '0',
      'image-src' => '/theme/flatui/img/new/pling-logo-large.png',
  ),
  'header-nav-tabs' =>
  array (
      'background-color' => '#AAA6A6',
      'background-color-active' => '#FF8743',
      'background-color-hover' => '#FF8743',
      'border-color' => '#ffffff',
      'border-radius' => '5px 5px 0 0',
      'border-style' => 'solid solid none',
      'border-with' => '2px 2px 0',
      'height' => '24px',
      'margin-right' => '2px',
      'absolute-left' => '310px',
      'absolute-right' => '30px',
      'link' =>
          array (
              'color' => '#ffffff',
              'color-active' => '#ffffff',
              'color-hover' => '#ffffff',
          ),
  ),
);
