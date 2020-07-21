<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App;

use const Morpho\App\CONTROLLER_SUFFIX;
use Morpho\App\ModuleIndex;
use Morpho\App\ServerModule;
use Morpho\Ioc\IServiceManager;
use Morpho\Testing\TestCase;
use Morpho\App\HandlerInstanceProvider;
use Morpho\App\Web\Request;

class HandlerInstanceProviderTest extends TestCase {
    public function testInvoke() {
        $serviceManager = $this->createMock(IServiceManager::class);

        $moduleName = 'foo/bar';
        $module = $this->createConfiguredMock(ServerModule::class, ['name' => $moduleName, 'autoloadFilePath' => __FILE__]);

        $moduleIndex = $this->createMock(ModuleIndex::class);
        $moduleIndex->expects($this->any())
            ->method('module')
            ->with($moduleName)
            ->willReturn($module);
        $services = [
            'serverModuleIndex' => $moduleIndex,
        ];
        $serviceManager->expects($this->any())
            ->method('offsetGet')
            ->willReturnCallback(function ($id) use ($services) {
                return $services[$id];
            });

        $handlerInstanceProvider = new HandlerInstanceProvider($serviceManager);

        $controllerClass = __NAMESPACE__ . '\\HandlerInstanceProviderTest_TestController';

        $handler = [
            'module' => $moduleName,
            'class' => $controllerClass,
        ];

        $request = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('handler')
            ->willReturn($handler);

        $instance = $handlerInstanceProvider($request);

        $this->assertInstanceOf($controllerClass, $instance);
    }
}

class HandlerInstanceProviderTest_TestController {
}
