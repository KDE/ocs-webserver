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

namespace Application\Form\Validators;

use Laminas\Validator\AbstractValidator;

/**
 * Class SourceUrl
 *
 * @package Application\Form\Validators
 */
class WallpaperSites extends AbstractValidator
{

    const INVALID_URL = 'invalidUrl';

    protected $messageTemplates = array(
        self::INVALID_URL  => "'%value%' is not valid. You should not post products based on elements of other websites if it violates our terms of use and/or the terms of use of the source website.",
    );

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $this->setValue((string)$value);
        $search = "unsplash\.com|pixabay\.com|wallpapercave\.com|wallpapersafari\.com|pexels\.com|wallpapersden\.com|pixnio\.com|skitterphoto\.com|wallpaper-house\.com|wallpapercave\.com|wallpaperfx\.com|wallpaperscraft\.com|wallpapershome\.com|wallpapersite\.com|wallpaperswide\.com|wallpapertip\.com|rawpixel\.com|wallpaperflare\.com|backiee\.com|images.?\.alphacoders\.com|skitterphoto\.com|stocksnap\.io|pickupimage\.com|pixelstalk\.net";

        $isValidURL = true;
        $matches = null;
        if (preg_match("/({$search})/i", $value, $matches)) {
            $isValidURL = false;
        }

        if (false == $isValidURL) {
            $this->error(self::INVALID_URL);

            return false;
        }

        return true;
    }

}