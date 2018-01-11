<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Web\View;

use Morpho\Ioc\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Request;
use Morpho\Web\Uri\Uri;
use Morpho\Web\View\FormPersister;

class FormPersisterTest extends TestCase {
    /**
     * @var FormPersister
     */
    private $formPersister;

    public function setUp() {
        parent::setUp();
        $serviceManager = new ServiceManager();
        $request = $this->createMock(Request::class);
        $uri = $this->createMock(Uri::class);
        $uri->expects($this->any())
            ->method('toStr')
            ->willReturn('/foo/bar<script?one=ok&two=done');
        $request->expects($this->any())
            ->method('uri')
            ->willReturn($uri);
        $serviceManager->set('request', $request);
        $this->formPersister = new FormPersister($serviceManager);
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