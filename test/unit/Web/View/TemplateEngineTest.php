<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\TemplateEngine;

class TemplateEngineTest extends TestCase {
    public function testRender_WithoutElements() {
        $engine = new TemplateEngine();
        $code = '<?php echo "Hello $world";';
        $res = $engine->render($code, ['world' => 'World!']);
        $this->assertSame('Hello World!', $res);
    }

    public function testRender_WithElements() {
        $engine = new TemplateEngine();
        $code = '<?php echo ??;';
        $engine->append(function ($context) {
            $context['code'] = str_replace('??', '"<span>$smile</span>"', $context['code']);
            $context['vars']['smile'] = ':)';
            return $context;
        });
        $res = $engine->render($code, ['smile' => ':(']);
        $this->assertSame('<span>:)</span>', $res);
    }
}