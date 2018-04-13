<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\Testing\TestCase;
use Morpho\App\Web\View\TemplateEngine;

class TemplateEngineTest extends TestCase {
    public function testTpl_PreservingThis() {
        $code = '<?php echo "$this->a $b";';
        $filePath = $this->createTmpFile();
        file_put_contents($filePath, $code);
        $templateEngine = new class extends TemplateEngine {
            protected $a = 'Hello';
        };
        $this->assertSame(
            'Hello World!',
            $templateEngine->tpl($filePath, ['b' => 'World!'])
        );
    }

    public function testRun_WithoutElements() {
        $engine = new TemplateEngine();
        $code = '<?php echo "Hello $world";';
        $res = $engine->run($code, ['world' => 'World!']);
        $this->assertSame('Hello World!', $res);
    }

    public function testRun_WithElements() {
        $engine = new TemplateEngine();
        $code = '<?php echo ??;';
        $engine->append(function ($context) {
            $context['code'] = str_replace('??', '"<span>$smile</span>"', $context['code']);
            return $context;
        });
        $res = $engine->run($code, ['smile' => ':)']);
        $this->assertSame('<span>:)</span>', $res);
    }
}
