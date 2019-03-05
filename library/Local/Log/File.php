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
 *
 * Created: 09.10.2018
 */
class Local_Log_File
{
    const extension = ".log";

    protected $logfile;

    /**
     * @inheritDoc
     */
    public function __construct($domain, $filename)
    {
        $this->initLog($domain, $filename);
    }

    /**
     * @return mixed
     */
    private function initLog($domain, $filename)
    {
        $fileDomainId = str_replace('.', '_', $domain);
        $date = date("Y-m-d_H-i-s");
        $date = date("Y-m-d");
        $this->logfile = realpath(APPLICATION_DATA . "/logs") . DIRECTORY_SEPARATOR . $date . '_' . $fileDomainId . '_' . $filename . self::extension;
        //$this->initFiles($this->logfile);
    }

    /**
     * @param $file
     * @param $errorFile
     */
    private function initFiles($file)
    {
        if (file_exists($file)) {
            file_put_contents($file, "1");
            unlink($file);
        }
    }

    /**
     * @param $message
     */
    public function info($message)
    {
        $timestamp = date("c");
        file_put_contents($this->logfile, $timestamp . " [INFO] : " . $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * @param $message
     */
    public function err($message)
    {
        $timestamp = date("c");
        file_put_contents($this->logfile, $timestamp . " [ERROR] : " . $message . PHP_EOL, FILE_APPEND);
    }

}