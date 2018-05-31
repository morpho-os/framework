<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\Testing\TestCase;
use Morpho\App\Web\ContentNegotiator;
use Morpho\App\Web\Request;

class ContentNegotiatorTest extends TestCase {
    public function dataForInvoke() {
        $mediaRanges = $this->mediaRanges();
        yield [
            $this->mkAcceptHeaderVal([
                $mediaRanges[ContentNegotiator::JSON_FORMAT],
                $mediaRanges[ContentNegotiator::HTML_FORMAT],
                $mediaRanges[ContentNegotiator::XML_FORMAT],
                $mediaRanges[ContentNegotiator::ANY_FORMAT],
            ]),
            ContentNegotiator::HTML_FORMAT,// Default priority is choosing HTML
        ];
        yield [
            $this->mkAcceptHeaderVal([
                $mediaRanges[ContentNegotiator::ANY_FORMAT],
                $mediaRanges[ContentNegotiator::XML_FORMAT],
                $mediaRanges[ContentNegotiator::JSON_FORMAT],
            ]),
            ContentNegotiator::JSON_FORMAT,
        ];
        yield [
            $this->mkAcceptHeaderVal([
                $mediaRanges[ContentNegotiator::JSON_FORMAT],
            ]),
            ContentNegotiator::JSON_FORMAT,
        ];
        yield [
            '   ',
            ContentNegotiator::HTML_FORMAT,
        ];
        yield [
            '',
            ContentNegotiator::HTML_FORMAT,
        ];
    }

    /**
     * @dataProvider dataForInvoke
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

        $this->assertSame(ContentNegotiator::HTML_FORMAT, $format);
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
            ContentNegotiator::JSON_FORMAT => ['application/json'],
            ContentNegotiator::HTML_FORMAT => ['text/html', 'application/xhtml+xml'],
            ContentNegotiator::XML_FORMAT => ['application/xml;q=0.9'],
            ContentNegotiator::ANY_FORMAT => ['*/*;q=0.8'],
        ];
    }
}
