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

use Laminas\View\Helper\AbstractHelper;

class CalcDonation extends AbstractHelper
{

    const paypal_fix = 0.3;
    const paypal_var = 0.029;
    const tax = 0.19;

    public function __invoke($tier)
    {
        return $this->calcDonation($tier);
    }

    public function calcDonation($tier)
    {
        //$v = ($tier + self::paypal_fix)/( 1- ( ( self::paypal_var/(1+self::paypal_var)) + (self::tax/(1+self::tax)) ));
        $v = ($tier + self::paypal_fix) / (1 - ((self::paypal_var) + (self::tax / (1 + self::tax))));

        return number_format($v, 2);
    }

} 