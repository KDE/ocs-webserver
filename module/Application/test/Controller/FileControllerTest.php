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

namespace ApplicationTest\Controller;

use Application\Controller\FileController;
use Application\Model\Service\AclService;
use Application\Model\Service\PploadService;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * @property PploadService|\PHPUnit\Framework\MockObject\MockObject stubPploadService
 * @property AclService|\PHPUnit\Framework\MockObject\MockObject    stubAclService
 */
class FileControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {

        // The module configuration should still be applicable for tests.
        // You can override configuration here with test case specific values,
        // such as sample view templates, path stacks, module_listener_options,
        // etc.
        $configOverrides = [
            // Additional modules to include when in development mode
            'modules'                 => [
                'Laminas\DeveloperTools',
                'BjyProfiler',
            ],
            // Configuration overrides during development mode
            'module_listener_options' => [
                'config_glob_paths'        => [realpath(__DIR__) . '/autoload/{,*.}{global,local}-development.php'],
                'config_cache_enabled'     => false,
                'module_map_cache_enabled' => false,
            ],
        ];

        $this->setApplicationConfig(
            ArrayUtils::merge(
                include __DIR__ . '/../../../../config/application.config.php', $configOverrides
            )
        );

        // mock for ppload server because we don't want to really upload a file.
        $this->stubPploadService = $this->createMock(PploadService::class);
        $this->stubPploadService->method('uploadEmptyFileWithLink')->willReturn(false);

        // mock for acl service class which will return always true. Because we don't test the access rights here.
        $this->stubAclService = $this->createMock(AclService::class);
        $this->stubAclService->method('isGranted')->willReturn(AclService::ACCESS_GRANTED);

        parent::setUp();

        //remark: do any changes for servicelocator after "parent::setUp()" otherwise it will be overridden
        $services = $this->getApplicationServiceLocator();
        $services->setAllowOverride(true);
        $services->setService(AclService::class, $this->stubAclService);
        $services->setService(PploadService::class, $this->stubPploadService);
        $services->setAllowOverride(false);

    }

    public function testFileLinkCanBeAccessed()
    {
        /** @var \Laminas\Http\PhpEnvironment\Request $request */
        //$request = $this->getRequest();

        $data = [
            'u'          => 'https://github.com/mongodb/mongo/archive/r3.5.2.tar.gz',
            'project_id' => '1511361',
            'fn'         => '',
            'fd'         => '',
        ];

        $this->dispatch('/file/link', 'POST', $data);

        /** @var \Laminas\Http\PhpEnvironment\Response $response */
        //$response = $this->getResponse();
        //echo $response->toString();

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName(FileController::class); // as specified in router's controller name alias
        $this->assertControllerClass('FileController');
    }

    public function testGlobalIDNUrls()
    {
        $data = [
            'u'          => 'https://github.com/mongodb/mongo/archive/r3.5.2.tar.gz',
            'project_id' => '1511361',
            'fn'         => '',
            'fd'         => '',
        ];

        $this->dispatch('/file/link', 'POST', $data);

        /** @var \Laminas\Http\PhpEnvironment\Response $response */
        $response = $this->getResponse();

        $this->assertJsonStringEqualsJsonString('{"status":"success","message":"","data":false}', $response->getBody());
    }

    public function testGlobalIpUrls()
    {
        $data = [
            'u'          => 'https://46.101.205.126/p/1511361',
            'project_id' => '1511361',
            'fn'         => '',
            'fd'         => '',
        ];

        $this->dispatch('/file/link', 'POST', $data);

        /** @var \Laminas\Http\PhpEnvironment\Response $response */
        $response = $this->getResponse();

        $this->assertJsonStringEqualsJsonString('{"status":"error","message":"Link is not valid","data":false}', $response->getBody());
    }

    public function testLocalhostUrls()
    {
        $data = [
            'u'          => 'http://localhost/.wellknown/metadata/v1',
            'project_id' => '1511361',
            'fn'         => '',
            'fd'         => '',
        ];

        $this->dispatch('/file/link', 'POST', $data);

        /** @var \Laminas\Http\PhpEnvironment\Response $response */
        $response = $this->getResponse();

        $this->assertJsonStringEqualsJsonString('{"status":"error","message":"Link is not valid","data":false}', $response->getBody());
    }

    public function testReservedIpUrls()
    {
        $data = [
            'u'          => 'http://169.254.169.254/metadata/v1',
            'project_id' => '1511361',
            'fn'         => '',
            'fd'         => '',
        ];

        $this->dispatch('/file/link', 'POST', $data);

        /** @var \Laminas\Http\PhpEnvironment\Response $response */
        $response = $this->getResponse();

        $this->assertJsonStringEqualsJsonString('{"status":"error","message":"Link is not valid","data":false}', $response->getBody());
    }

    public function testLocalIpV1Urls()
    {
        $data = [
            'u'          => 'http://192.168.8.1/.wellknown/metadata/v1',
            'project_id' => '1511361',
            'fn'         => '',
            'fd'         => '',
        ];

        $this->dispatch('/file/link', 'POST', $data);

        /** @var \Laminas\Http\PhpEnvironment\Response $response */
        $response = $this->getResponse();

        $this->assertJsonStringEqualsJsonString('{"status":"error","message":"Link is not valid","data":false}', $response->getBody());
    }

    public function testLocalIpV2Urls()
    {
        $data = [
            'u'          => 'http://10.135.87.42/.wellknown/metadata/v1',
            'project_id' => '1511361',
            'fn'         => '',
            'fd'         => '',
        ];

        $this->dispatch('/file/link', 'POST', $data);

        /** @var \Laminas\Http\PhpEnvironment\Response $response */
        $response = $this->getResponse();

        $this->assertJsonStringEqualsJsonString('{"status":"error","message":"Link is not valid","data":false}', $response->getBody());
    }

}
