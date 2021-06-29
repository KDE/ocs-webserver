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
 *
 */

namespace Application\Form\Validators;


class IpAddressRange extends \Laminas\Validator\AbstractValidator
{
    const INTERNAL_URL = 'internalUrl';

    protected $messageTemplates = array(
        self::INTERNAL_URL => "'%value%' is not valid.",
    );

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        if (false == $this->validIpAddress($value)) {
            $this->error(self::INTERNAL_URL);

            return false;
        }

        return true;
    }

    private function validIpAddress($value)
    {
        $url_fragments = parse_url($value);
        $isIP = (bool)ip2long($url_fragments['host']);
        if (false === $isIP) {
            return true;
        }

        if (filter_var($url_fragments['host'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        return true;
    }

}