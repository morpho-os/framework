<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use const Morpho\App\CONTROLLER_SUFFIX;
use Morpho\App\ModuleIndex;
use Morpho\App\Module;
use Morpho\Ioc\IServiceManager;
use Morpho\Testing\TestCase;
use Morpho\App\Web\InstanceProvider;
use Morpho\App\Web\Request;

class InstanceProviderTest extends TestCase {
    public function testInvoke_ThrowsNotFoundExceptionIfAnyRequestHandlerComponentIsEmpty() {
        $serviceManager = $this->createMock(IServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('offsetGet')
            ->willReturn($this->createMock(ModuleIndex::class));
        $instanceProvider = new InstanceProvider($serviceManager);

        $request = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('handler')
            ->willReturn([null, null, null]);

        $this->assertFalse($instanceProvider->__invoke($request));
    }

    public function testInvoke_SetsHandlerFnAsRequestItem() {
        $serviceManager = $this->createMock(IServiceManager::class);
        $module = $this->createMock(Module::class);
        $moduleName = 'foo/bar';
        $module->expects($this->any())
            ->method('name')
            ->willReturn($moduleName);
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

        $controllerName = 'News';

        $classSuffix = 'App\\Web\\' . $controllerName . CONTROLLER_SUFFIX;

        $instanceProvider = new class ($serviceManager, $module, $moduleName, $classSuffix) extends InstanceProvider {
            private $expectedModule, $expectedModuleName, $expectedClassSuffix;

            public $returnedInstance;

            public function __construct(IServiceManager $serviceManager, Module $expectedModule, string $expectedModuleName, string $expectedClassSuffix) {
                parent::__construct($serviceManager);
                $this->expectedModule = $expectedModule;
                $this->expectedModuleName = $expectedModuleName;
                $this->expectedClassSuffix = $expectedClassSuffix;

            }

            public function mkInstance(Module $module, string $classSuffix) {
                if ($module !== $this->expectedModule) {
                    throw new \UnexpectedValueException();
                }
                if ($classSuffix !== $this->expectedClassSuffix) {
                    throw new \UnexpectedValueException();
                }
                $instance = function () {};
                $this->returnedInstance = $instance;
                return $instance;
            }

            protected function registerModuleClassLoader(Module $module): void {
                if ($module !== $this->expectedModule) {
                    throw new \UnexpectedValueException();
                }
                if ($module->name() !== $this->expectedModuleName) {
                    throw new \UnexpectedValueException();
                }
            }
        };

        $request = new Request();
        $request->setHandler([$moduleName, $controllerName, 'show']);

        $instanceProvider->__invoke($request);

        $this->assertSame($instanceProvider->returnedInstance, $request['handlerFn']);
    }
}
