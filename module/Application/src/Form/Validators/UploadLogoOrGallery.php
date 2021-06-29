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


use Laminas\Validator\Exception;

class UploadLogoOrGallery extends \Laminas\Validator\AbstractValidator
{
    const INVALID = 'invalid_upload';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID => "Please upload min. one gallery picture or a product logo.",
    ];

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $context = func_get_arg(1);

        if (empty($value) and empty($context['upload_picture'])) {
            $this->error(self::INVALID);

            return false;
        }

        return true;
    }
}