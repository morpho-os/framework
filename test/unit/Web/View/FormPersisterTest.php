<?php declare(strict_types=1);
namespace MorphoTest\Unit\Web\View;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\View\FormPersister;

class FormPersisterTest extends TestCase {
    public function testInvoke_FormWithAction_DefaultMethod_PrependsWithBasePath() {
        $serviceManager = new ServiceManager();
        $serviceManager->set('request', new class {
            public function uri() {
                return new class {
                    public function prependWithBasePath($uri) {
                        return '/base/path' . $uri;
                    }
                };
            }
        });
        $formPersister = new FormPersister($serviceManager);
        $html = <<<'HTML'
<form action="/foo/<?= $id ?>/edit?one=ok">
</form>
HTML;
        $this->assertHtmlEquals('<form action="/base/path/foo/<?= $id ?>/edit?one=ok" method="' . FormPersister::DEFAULT_METHOD . '"></form>', $formPersister->__invoke($html));
    }

    public function testInvoke_FormWithoutAction_DefaultMethod_AddsRequestUri() {
        $serviceManager = new ServiceManager();
        $serviceManager->set('request', new class {
            public function path() {
                return '/foo/bar<script?';
            }

            public function uri() {
                return new class {
                    public function __toString() {
                        return '/foo/bar&lt;script?one=ok&two=done';
                    }
                };
            }
        });
        $formPersister = new FormPersister($serviceManager);
        $html = '<form></form>';
        $this->assertEquals('<form method="' . FormPersister::DEFAULT_METHOD . '" action="/foo/bar&lt;script?one=ok&two=done"></form>', $formPersister->__invoke($html));
    }
}