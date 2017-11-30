<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\View;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\View\FormPersister;

class FormPersisterTest extends TestCase {
    public function testInvoke_FormWithoutAction_DefaultMethod_AddsRequestUri() {
        $serviceManager = new ServiceManager();
        $serviceManager->set('request', new class {
            public function path() {
                return '/foo/bar<script?';
            }

            public function uri() {
                return new class {
                    public function __toString() {
                        return '/foo/bar<script?one=ok&two=done';
                    }
                };
            }
        });
        $formPersister = new FormPersister($serviceManager);
        $html = '<form></form>';
        $this->assertEquals('<form method="' . FormPersister::DEFAULT_METHOD . '" action="/foo/bar&lt;script?one=ok&amp;two=done"></form>', $formPersister->__invoke($html));
    }
}