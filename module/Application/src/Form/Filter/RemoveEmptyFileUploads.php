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

namespace Application\Form\Filter;


use Laminas\Filter\Exception;

class RemoveEmptyFileUploads extends \Laminas\Filter\AbstractFilter
{

    /**
     * @inheritDoc
     */
    public function filter($files)
    {
        if (!is_array($files)) {
            return $files;
        }
        if (count($files) == 0) {
            return $files;
        }
        $filtered = $files;
        foreach ($files as $input_name => $upload) {
            if (isset($upload['error'])) {
                if ($upload['error'] === UPLOAD_ERR_NO_FILE) {
                    unset($filtered[$input_name]);
                }
                continue;
            }

            $filtered[$input_name] = array_filter(
                $upload, function ($file) {
                return $file['error'] === UPLOAD_ERR_OK;
                }
            );
            if (empty($filtered[$input_name])) {
                unset($filtered[$input_name]);
            }
        }

        return $filtered;
    }
}