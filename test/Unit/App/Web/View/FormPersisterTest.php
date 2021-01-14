<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\Testing\TestCase;
use Morpho\App\Web\Request;
use Morpho\App\Web\Uri\Uri;
use Morpho\App\Web\View\FormPersister;

class FormPersisterTest extends TestCase {
    /**
     * @var FormPersister
     */
    private $formPersister;

    public function setUp(): void {
        parent::setUp();
        $request = $this->createMock(Request::class);
        $uri = $this->createMock(Uri::class);
        $uri->expects($this->any())
            ->method('toStr')
            ->willReturn('/foo/bar<script?one=ok&two=done');
        $request->expects($this->any())
            ->method('uri')
            ->willReturn($uri);
        $this->formPersister = new FormPersister($request);
    }

    public function testInvoke_FormWithoutAction_DefaultMethod_AddsRequestUri() {
        $this->assertSame('post', FormPersister::DEFAULT_METHOD);
        $html = '<form></form>';
        $this->assertEquals('<form method="' . FormPersister::DEFAULT_METHOD . '" action="/foo/bar&lt;script?one=ok&amp;two=done"></form>', $this->formPersister->__invoke($html));
    }

    public function testInvoke_FormWithoutAction_GetMethod_AddsRequestUri() {
        $html = '<form method="get"></form>';
        $this->assertEquals('<form method="get" action="/foo/bar&lt;script?one=ok&amp;two=done"></form>', $this->formPersister->__invoke($html));
    }
}
