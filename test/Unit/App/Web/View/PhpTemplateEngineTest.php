<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\App\ISite;
use Morpho\Base\IPipe;
use Morpho\Testing\TestCase;
use Morpho\App\Web\View\PhpTemplateEngine;
use function file_put_contents;
use function str_replace;

class PhpTemplateEngineTest extends TestCase {
    private $templateEngine;

    public function setUp(): void {
        parent::setUp();
        $this->templateEngine = new PhpTemplateEngine($this->templateEngineConf());
    }

    public function testInterface() {
        $this->assertInstanceOf(IPipe::class, $this->templateEngine);
    }

    public function dataForEval() {
        yield [
            '',
            '',
            [],
        ];
        yield [
            "It&#039;s",
            '<?= "It$foo";',
            ['foo' => "'s"],
        ];
    }

    /**
     * @dataProvider dataForEval
     */
    public function testEval_DefaultPhases($expected, $source, $vars) {
        $compiled = $this->templateEngine->eval($source, $vars);
        $this->assertSame($expected, $compiled);
    }

    public function testEval_WithoutPhases() {
        $code = '<?php echo "Hello $world";';
        $this->templateEngine->setPhases([]);
        $this->assertSame([], $this->templateEngine->phases());

        $res = $this->templateEngine->eval($code, ['world' => 'World!']);

        $this->assertSame('Hello World!', $res);
    }

    public function testEval_PrependCustomPhase() {
        $code = '<?php echo ??;';
        $this->templateEngine->prependPhase(function ($context) {
            $context['program'] = str_replace('??', '"<span>$smile</span>"', $context['program']);
            return $context;
        });
        $res = $this->templateEngine->eval($code, ['smile' => ':)']);
        $this->assertSame(
            htmlspecialchars('<span>:)</span>', ENT_QUOTES),
            $res
        );
    }

    public function testEvalPhpFile_PreservingThis() {
        $code = '<?php echo "$this->a $b";';
        $filePath = $this->createTmpFile();
        file_put_contents($filePath, $code);

        $templateEngine = new class ($this->templateEngineConf()) extends PhpTemplateEngine {
            protected $a = 'Hello';
        };
        $this->assertSame(
            'Hello World!',
            $templateEngine->evalPhpFile($filePath, ['b' => 'World!'])
        );
    }

    public function testForceCompileAccessor() {
        $this->checkBoolAccessor([$this->templateEngine, 'forceCompile'], false);
    }

    private function templateEngineConf(): array {
        return [
            'request' => null,
            'site' => $this->createStub(ISite::class),
        ];
    }
}
