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

class CalcDonationYearlyText extends AbstractHelper
{

    const paypal_fix = 0.3;
    const paypal_var = 0.029;
    const tax = 0.19;

    public function __invoke($tier)
    {
        return $this->calcDonationYearlyText($tier);
    }

    public function calcDonationYearlyText($tier)
    {
        //($0.99 + 35 cents paypal + 26 cents taxes = $1.60 monthly)
        $v = ($tier * 12 + self::paypal_fix) / (1 - ((self::paypal_var / (1 + self::paypal_var)) + (self::tax / (1 + self::tax))));
        $paypal = self::paypal_fix + $v * self::paypal_var;
        $total = $tier * 12 + $paypal;
        $t = $total * self::tax;
        $g = $total + $t;
        $r = '(12*$' . $tier . ' = $' . number_format($tier * 12, 2) . ' + $' . number_format($paypal, 2) . ' paypal + $' . number_format($t, 2) . ' tax = $' . number_format($g, 2) . ' yearly)';

        return $r;
    }

}
