<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\Testing\TestCase;
use Morpho\App\Web\ContentNegotiator;
use Morpho\App\Web\ContentFormat;
use Morpho\App\Web\Request;

class ContentNegotiatorTest extends TestCase {
    public function dataInvoke() {
        $mediaRanges = $this->mediaRanges();
        yield [
            $this->mkAcceptHeaderVal([
                $mediaRanges[ContentFormat::JSON],
                $mediaRanges[ContentFormat::HTML],
                $mediaRanges[ContentFormat::XML],
                $mediaRanges[ContentFormat::ANY],
            ]),
            ContentFormat::HTML,// Default priority is choosing HTML
        ];
        yield [
            $this->mkAcceptHeaderVal([
                $mediaRanges[ContentFormat::ANY],
                $mediaRanges[ContentFormat::XML],
                $mediaRanges[ContentFormat::JSON],
            ]),
            ContentFormat::JSON,
        ];
        yield [
            $this->mkAcceptHeaderVal([
                $mediaRanges[ContentFormat::JSON],
            ]),
            ContentFormat::JSON,
        ];
        yield [
            '   ',
            ContentFormat::HTML,
        ];
        yield [
            '',
            ContentFormat::HTML,
        ];
    }

    /**
     * @dataProvider dataInvoke
     */
    public function testInvoke(string $acceptHeaderValue, string $expectedFormat) {
        $request = new Request();
        $request->headers()['Accept'] = $acceptHeaderValue;

        $negotiator = new ContentNegotiator();

        $format = $negotiator->__invoke($request);

        $this->assertSame($expectedFormat, $format);
    }

    public function testInvoke_MissingAcceptHeaderReturnsDefaultFormat() {
        $negotiator = new ContentNegotiator();
        $request = new Request();

        $format = $negotiator->__invoke($request);

        $this->assertSame(ContentFormat::HTML, $format);
    }

    private function mkAcceptHeaderVal(array $mediaRanges): string {
        foreach ($mediaRanges as &$mediaRange) {
            $mediaRange = \implode(', ', $mediaRange);
        }
        unset($mediaRange);
        return \implode(', ', $mediaRanges);
    }

    private function mediaRanges(): array {
        return [
            ContentFormat::JSON => ['application/json'],
            ContentFormat::HTML => ['text/html', 'application/xhtml+xml'],
            ContentFormat::XML => ['application/xml;q=0.9'],
            ContentFormat::ANY => ['*/*;q=0.8'],
        ];
    }
}
