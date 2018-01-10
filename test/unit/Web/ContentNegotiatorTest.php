<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\ContentNegotiator;
use Morpho\Web\Request;

class ContentNegotiatorTest extends TestCase {
    public function testAjaxMapsToJson() {
        $request = new Request();
        $request->isAjax(true);

        $negotiator = new ContentNegotiator();

        $format = $negotiator->__invoke($request);

        $this->assertSame(ContentNegotiator::JSON_FORMAT, $format);
    }

    public function dataForAcceptHeader() {
        $any = 'any';
        $mediaRanges = [
            ContentNegotiator::JSON_FORMAT => ['application/json'],
            ContentNegotiator::HTML_FORMAT => ['text/html', 'application/xhtml+xml'],
            ContentNegotiator::XML_FORMAT => ['application/xml;q=0.9'],
            $any => ['*/*;q=0.8'],
        ];
        $mkStr = function (array $mediaRanges): string {
            foreach ($mediaRanges as &$mediaRange) {
                $mediaRange = implode(', ', $mediaRange);
            }
            unset($mediaRange);
            return implode(', ', $mediaRanges);
        };
        yield [
            $mkStr([
                $mediaRanges[ContentNegotiator::JSON_FORMAT],
                $mediaRanges[ContentNegotiator::HTML_FORMAT],
                $mediaRanges[ContentNegotiator::XML_FORMAT],
                $mediaRanges[$any],
            ]),
            ContentNegotiator::HTML_FORMAT,// Default priority is choosing HTML
        ];
        yield [
            $mkStr([
                $mediaRanges[$any],
                $mediaRanges[ContentNegotiator::XML_FORMAT],
                $mediaRanges[ContentNegotiator::JSON_FORMAT],
            ]),
            ContentNegotiator::JSON_FORMAT,
        ];
        yield [
            $mkStr([
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
     * @dataProvider dataForAcceptHeader
     */
    public function testAcceptHeader(string $acceptHeaderValue, string $expectedFormat) {
        $request = new Request();
        $request->headers()['Accept'] = $acceptHeaderValue;

        $negotiator = new ContentNegotiator();

        $format = $negotiator->__invoke($request);

        $this->assertSame($expectedFormat, $format);
    }

    // @TODO: Handle XML and ANY return from ContentNegotiator
    public function testEmptyAcceptHeader_ReturnsFalse() {
        $negotiator = new ContentNegotiator();
        $request = new Request();

        $format = $negotiator->__invoke($request);

        $this->assertSame(ContentNegotiator::HTML_FORMAT, $format);
    }
}