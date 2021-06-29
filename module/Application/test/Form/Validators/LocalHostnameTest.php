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

namespace ApplicationTest\Form\Validators;

use Application\Form\Validators\LocalHostname;
use PHPUnit\Framework\TestCase;

/**
 * @property LocalHostname validator
 */
class LocalHostnameTest extends TestCase
{
    public function testHostIpaddress()
    {
        $this->assertNotFalse($this->validator->isValid('http://169.254.169.254/metadata/v1'));
        if (false === $this->hasFailed()) {
            echo("'http://169.254.169.254/metadata/v1' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('http://127.0.0.1/metadata/v1'));
        if (false === $this->hasFailed()) {
            echo("'http://127.0.0.1/metadata/v1' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('http://10.135.205.238/metadata/v1'), "'10.135.205.238' is not rejected");
        if (false === $this->hasFailed()) {
            echo("'http://10.135.205.238/metadata/v1' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('http://192.168.8.1/metadata/v1'), "'192.168.8.1' is not rejected");
        if (false === $this->hasFailed()) {
            echo("'http://192.168.8.1/metadata/v1' => passed\n");
        }

        $this->assertFalse($this->validator->isValid('http://localhost/metadata/v1'), "'localhost' is not rejected");
        if (false === $this->hasFailed()) {
            echo("'http://localhost/metadata/v1' => rejected\n");
        }
    }

    public function testAllowedUrls()
    {
        $this->assertNotFalse($this->validator->isValid('https://www.gutenberg.org/ebooks/35'));
        if (false === $this->hasFailed()) {
            echo("'https://www.gutenberg.org/ebooks/35' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('https://www.opencode.net/dahoc/image2pdf'));
        if (false === $this->hasFailed()) {
            echo("'https://www.opencode.net/dahoc/image2pdf' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('https://www.opendesktop.org/p/998694/'));
        if (false === $this->hasFailed()) {
            echo("'https://www.opendesktop.org/p/998694/' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('https://www.pling.com/u/g-nome/'));
        if (false === $this->hasFailed()) {
            echo("'https://www.pling.com/u/g-nome/' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('https://www.youtube.com/watch?v=9DYn2-qiMmE'));
        if (false === $this->hasFailed()) {
            echo("'https://www.youtube.com/watch?v=9DYn2-qiMmE' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('https://youtu.be/MjeDxhIB0SM'));
        if (false === $this->hasFailed()) {
            echo("'https://youtu.be/MjeDxhIB0SM' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('https://www.facebook.com/KissKool-Theme-108913274359160'));
        if (false === $this->hasFailed()) {
            echo("'https://www.facebook.com/KissKool-Theme-108913274359160' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('https://github.com/thevladsoft/Canaimadonia-KDM'));
        if (false === $this->hasFailed()) {
            echo("'https://github.com/thevladsoft/Canaimadonia-KDM' => passed\n");
        }
    }

    public function testAllowedIpAddresses()
    {
        $this->assertNotFalse($this->validator->isValid('https://140.82.121.4/thevladsoft/Canaimadonia-KDM'));
        if (false === $this->hasFailed()) {
            echo("'https://140.82.121.4/thevladsoft/Canaimadonia-KDM' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('https://142.250.185.78/watch?v=9DYn2-qiMmE'));
        if (false === $this->hasFailed()) {
            echo("'https://142.250.185.78/watch?v=9DYn2-qiMmE' => passed\n");
        }

        $this->assertNotFalse($this->validator->isValid('https://46.101.205.126/u/g-nome/'));
        if (false === $this->hasFailed()) {
            echo("'https://46.101.205.126/u/g-nome/' => passed\n");
        }
    }

    protected function setUp()
    {
        $this->validator = new LocalHostname();
        parent::setUp();
    }

    protected function tearDown()
    {
        $this->validator = null;
        parent::tearDown();
    }
}
