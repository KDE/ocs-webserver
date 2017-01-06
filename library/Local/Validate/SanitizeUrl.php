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
class Local_Validate_SanitizeUrl extends Zend_Validate_Abstract
{
    const INVALID_URL = 'invalidUrl';
    const INVALID_FILE_TYPE = 'invalidFileType';
    const URL_NOT_ACCESSIBLE = 'urlNotAccessible';

    protected $_allowedMimeTypes = array(
        'text/html' => 'htm|html|php',
    );

    protected $_invalidMimeTypes = array(
        '\.pdf',
        '\.zip',
        '\.exe',
        '\.rar',
        '\.doc',
        '\.7z',
        '\.js',
        '\.css'
    );

    // Image formats
//	'jpg|jpeg|jpe'                 => 'image/jpeg',
//	'gif'                          => 'image/gif',
//	'png'                          => 'image/png',
//	'bmp'                          => 'image/bmp',
//	'tif|tiff'                     => 'image/tiff',
//	'ico'                          => 'image/x-icon',

    // Video formats
//	'asf|asx'                      => 'video/x-ms-asf',
//	'wmv'                          => 'video/x-ms-wmv',
//	'wmx'                          => 'video/x-ms-wmx',
//	'wm'                           => 'video/x-ms-wm',
//	'avi'                          => 'video/avi',
//	'divx'                         => 'video/divx',
//	'flv'                          => 'video/x-flv',
//	'mov|qt'                       => 'video/quicktime',
//	'mpeg|mpg|mpe'                 => 'video/mpeg',
//	'mp4|m4v'                      => 'video/mp4',
//	'ogv'                          => 'video/ogg',
//	'webm'                         => 'video/webm',
//	'mkv'                          => 'video/x-matroska',

    // Text formats
//	'txt|asc|c|cc|h'               => 'text/plain',
//	'csv'                          => 'text/csv',
//	'tsv'                          => 'text/tab-separated-values',
//	'ics'                          => 'text/calendar',
//	'rtx'                          => 'text/richtext',
//	'css'                          => 'text/css',
//	'htm|html'                     => 'text/html',

    // Audio formats
//	'mp3|m4a|m4b'                  => 'audio/mpeg',
//	'ra|ram'                       => 'audio/x-realaudio',
//	'wav'                          => 'audio/wav',
//	'ogg|oga'                      => 'audio/ogg',
//	'mid|midi'                     => 'audio/midi',
//	'wma'                          => 'audio/x-ms-wma',
//	'wax'                          => 'audio/x-ms-wax',
//	'mka'                          => 'audio/x-matroska',

    // Misc application formats
//	'rtf'                          => 'application/rtf',
//	'js'                           => 'application/javascript',
//	'pdf'                          => 'application/pdf',
//	'swf'                          => 'application/x-shockwave-flash',
//	'class'                        => 'application/java',
//	'tar'                          => 'application/x-tar',
//	'zip'                          => 'application/zip',
//	'gz|gzip'                      => 'application/x-gzip',
//	'rar'                          => 'application/rar',
//	'7z'                           => 'application/x-7z-compressed',
//	'exe'                          => 'application/x-msdownload',

    // MS Office formats
//	'doc'                          => 'application/msword',
//	'pot|pps|ppt'                  => 'application/vnd.ms-powerpoint',
//	'wri'                          => 'application/vnd.ms-write',
//	'xla|xls|xlt|xlw'              => 'application/vnd.ms-excel',
//	'mdb'                          => 'application/vnd.ms-access',
//	'mpp'                          => 'application/vnd.ms-project',
//	'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml. document',
//	'docm'                         => 'application/vnd.ms-word.document.macroEnabled.12',
//	'dotx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml. template',
//	'dotm'                         => 'application/vnd.ms-word.template.macroEnabled.12',
//	'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
//	'xlsm'                         => 'application/vnd.ms-excel.sheet.macroEnabled.12',
//	'xlsb'                         => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
//	'xltx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
//	'xltm'                         => 'application/vnd.ms-excel.template.macroEnabled.12',
//	'xlam'                         => 'application/vnd.ms-excel.addin.macroEnabled.12',
//	'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml. presentation',
//	'pptm'                         => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
//	'ppsx'                         => 'application/vnd.openxmlformats-officedocument.presentationml. slideshow',
//	'ppsm'                         => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
//	'potx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
//	'potm'                         => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
//	'ppam'                         => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
//	'sldx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
//	'sldm'                         => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
//	'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',

    // OpenOffice formats
//	'odt'                          => 'application/vnd.oasis.opendocument.text',
//	'odp'                          => 'application/vnd.oasis.opendocument.presentation',
//	'ods'                          => 'application/vnd.oasis.opendocument.spreadsheet',
//	'o dg'                          => 'application/vnd.oasis.opendocument.graphics',
//	'odc'                          => 'application/vnd.oasis.opendocument.chart',
//	'odb'                          => 'application/vnd.oasis.opendocument.database',
//	'odf'                          => 'application/vnd.oasis.opendocument.formula',

    // WordPerfect formats
//	'wp|wpd'                       => 'application/wordperfect',

    // iWork formats
//	'key'                          => 'application/vnd.apple.keynote',
//	'numbers'                      => 'application/vnd.apple.numbers',
//	'pages'                        => 'application/vnd.apple.pages'


    protected $_messageTemplates = array(
        self::INVALID_URL => "Not a valid URL. Please check your URL.",
        self::INVALID_FILE_TYPE => "Not a valid file type. Valid file types are htm or html or php.",
        self::URL_NOT_ACCESSIBLE => "Not a valid URL. Please check your URL."
    );

    public function isValid($value)
    {
        $valueString = ( string )$value;
        $this->_setValue($valueString);

        return $this->isUrlValid($value);
    }

    /**
     * Checks the URL string for allowed structure and mime type
     *
     * @param null $url
     * @return bool
     */
    function isUrlValid($url = null)
    {
        if ($url == null) {
            return false;
        }

        if (false == Zend_Uri_Http::check($url)) {
            $this->_error(self::INVALID_URL);
            return false;
        }

        $uri = Zend_Uri_Http::fromString($url);
        $path = $uri->getPath();
        foreach ($this->_invalidMimeTypes as $invalidMimeType) {
            if (true == preg_match("/.*{$invalidMimeType}\/?$/", $path)) {
                $this->_error(self::INVALID_FILE_TYPE);
                return false;
            }
        }

        $format = 1;
        $infoHeader = get_headers($url, $format);
        if (false === $infoHeader) {
            $this->_error(self::URL_NOT_ACCESSIBLE);
            return false;
        }

        $infoContentType = $infoHeader["Content-Type"];
        $contentType = explode(';', $infoContentType);

        if (false == array_key_exists(trim($contentType[0]), $this->_allowedMimeTypes)) {
            $this->_error(self::INVALID_FILE_TYPE);
            return false;
        }

        return true;
    }

}