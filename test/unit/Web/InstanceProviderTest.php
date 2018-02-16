<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Web;

use const Morpho\Core\CONTROLLER_SUFFIX;
use Morpho\Core\ModuleIndex;
use Morpho\Core\ModuleMeta;
use Morpho\Ioc\IServiceManager;
use Morpho\Testing\TestCase;
use Morpho\Web\InstanceProvider;
use Morpho\Web\NotFoundException;
use Morpho\Web\Request;

class InstanceProviderTest extends TestCase {
    public function testInvoke_ThrowsNotFoundExceptionIfAnyRequestHandlerComponentIsEmpty() {
        $serviceManager = $this->createMock(IServiceManager::class);
        /** @noinspection PhpParamsInspection */
        $instanceProvider = new InstanceProvider($serviceManager);

        $this->expectException(NotFoundException::class);

        $request = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('handler')
            ->willReturn([null, null, null]);

        /** @noinspection PhpParamsInspection */
        $instanceProvider->__invoke($request);
    }

    public function testInvoke_SetsHandlerFnAsRequestItem() {
        $serviceManager = $this->createMock(IServiceManager::class);
        $moduleMeta = $this->createMock(ModuleMeta::class);
        $moduleIndex = $this->createMock(ModuleIndex::class);
        $moduleName = 'foo/bar';
        $moduleIndex->expects($this->any())
            ->method('moduleMeta')
            ->with($moduleName)
            ->willReturn($moduleMeta);
        $services = [
            'moduleIndex' => $moduleIndex,
        ];
        $serviceManager->expects($this->any())
            ->method('offsetGet')
            ->willReturnCallback(function ($id) use ($services) {
                return $services[$id];
            });

        $controllerName = 'News';

        $classSuffix = (PHP_SAPI === 'cli' ? 'Cli\\' : 'Web\\') . $controllerName . CONTROLLER_SUFFIX;

        $instanceProvider = new class ($serviceManager, $moduleMeta, $moduleName, $classSuffix) extends InstanceProvider {
            private $expectedModuleMeta, $expectedModuleName, $expectedClassSuffix;

            public $returnedInstance;

            public function __construct(IServiceManager $serviceManager, ModuleMeta $expectedModuleMeta, string $expectedModuleName, string $expectedClassSuffix) {
                parent::__construct($serviceManager);
                $this->expectedModuleMeta = $expectedModuleMeta;
                $this->expectedModuleName = $expectedModuleName;
                $this->expectedClassSuffix = $expectedClassSuffix;

            }

            public function newInstance(ModuleMeta $moduleMeta, string $classSuffix) {
                if ($moduleMeta !== $this->expectedModuleMeta) {
                    throw new \UnexpectedValueException();
                }
                if ($classSuffix !== $this->expectedClassSuffix) {
                    throw new \UnexpectedValueException();
                }
                $instance = function () {};
                $this->returnedInstance = $instance;
                return $instance;
            }

            protected function registerModuleClassLoader($moduleMeta, $moduleName): void {
                if ($moduleMeta !== $this->expectedModuleMeta) {
                    throw new \UnexpectedValueException();
                }
                if ($moduleName !== $this->expectedModuleName) {
                    throw new \UnexpectedValueException();
                }
            }
        };

        $request = new Request();
        $request->setHandler([$moduleName, $controllerName, 'show']);

        /** @noinspection PhpParamsInspection */
        $instanceProvider->__invoke($request);

        $this->assertSame($instanceProvider->returnedInstance, $request['handlerFn']);
    }
}