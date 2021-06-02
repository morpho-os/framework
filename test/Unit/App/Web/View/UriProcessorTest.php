<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use ArrayObject;
use Morpho\App\IResponse;
use Morpho\App\Web\IRequest;
use Morpho\App\Web\View\UriProcessor;
use Morpho\Testing\TestCase;

class UriProcessorTest extends TestCase {
    public function dataProcessUrisInTags() {
        foreach (['/base/path', '/'] as $basePath) {
            // `form` tag
            yield [
                $basePath,
                '<form action="http://host/news/test1"></form>',
                '<form action="http://host/news/test1"></form>',
            ];
            yield [
                $basePath,
                '<form action="news/test1"></form>',
                '<form action="news/test1"></form>',
            ];
            yield [
                $basePath,
                '<form action="//host/news/test1"></form>',
                '<form action="//host/news/test1"></form>',
            ];
            yield [
                $basePath,
                "<form action=\"$basePath/news/test2\"></form>",
                '<form action="/news/test2"></form>',
            ];
            yield [
                $basePath,
                '<form action="<?= \'test\' ?>/news/test1"></form>',
                '<form action="<?= \'test\' ?>/news/test1"></form>',
            ];
            yield [
                $basePath,
                '<form action="' . $basePath . '/news/<?= \'test\' ?>/test1<?php echo \'ok\'; ?>"></form>',
                '<form action="/news/<?= \'test\' ?>/test1<?php echo \'ok\'; ?>"></form>',
            ];
            yield [
                $basePath,
                '<form action="' . $basePath . '/news/<?= \'test\' ?>/test1"></form>',
                '<form action="/news/<?= \'test\' ?>/test1"></form>',
            ];
            // `link` tag
            yield [
                $basePath,
                '<link href="http://host/css/test1.css">',
                '<link href="http://host/css/test1.css">',
            ];
            yield [
                $basePath,
                '<link href="css/test1.css">',
                '<link href="css/test1.css">',
            ];
            yield [
                $basePath,
                '<link href="//host/css/test1.css">',
                '<link href="//host/css/test1.css">',
            ];
            yield [
                $basePath,
                '<link href="' . $basePath . '/css/test1.css">',
                '<link href="/css/test1.css">',
            ];
            yield [
                $basePath,
                '<link href="<?= \'test\' ?>/css/test1.css">',
                '<link href="<?= \'test\' ?>/css/test1.css">',
            ];
            yield [
                $basePath,
                '<link href="' . $basePath . '/css/<?= \'test\' ?>/test1.css">',
                '<link href="/css/<?= \'test\' ?>/test1.css">',
            ];
            // `a` tag
            yield [
                $basePath,
                '<a href="http://host/css/test1"></a>',
                '<a href="http://host/css/test1"></a>',
            ];
            yield [
                $basePath,
                '<a href="css/test1"></a>',
                '<a href="css/test1"></a>',
            ];
            yield [
                $basePath,
                '<a href="//host/css/test1"></a>',
                '<a href="//host/css/test1"></a>',
            ];
            yield [
                $basePath,
                '<a href="' . $basePath . '/css/test1"></a>',
                '<a href="/css/test1"></a>',
            ];
            yield [
                $basePath,
                '<a href="<?= \'test\' ?>/css/test1"></a>',
                '<a href="<?= \'test\' ?>/css/test1"></a>',
            ];
            yield [
                $basePath,
                '<a href="' . $basePath . '/css/<?= \'test\' ?>/test1"></a>',
                '<a href="/css/<?= \'test\' ?>/test1"></a>',
            ];
            // `script` tag
            yield [
                $basePath,
                '<script src="http://host/js/test1.js"></script>',
                '<script src="http://host/js/test1.js"></script>',
            ];
            yield [
                $basePath,
                '<script src="js/test1.js"></script>',
                '<script src="js/test1.js"></script>',
            ];
            yield [
                $basePath,
                '<script src="//host/js/test1.js"></script>',
                '<script src="//host/js/test1.js"></script>',
            ];
            yield [
                $basePath,
                '<script src="' . $basePath . '/js/test1.js"></script>',
                '<script src="/js/test1.js"></script>',
            ];
            yield [
                $basePath,
                '<script src="<?= \'test\' ?>/js/test1.js"></script>',
                '<script src="<?= \'test\' ?>/js/test1.js"></script>',
            ];
            yield [
                $basePath,
                '<script src="' . $basePath . '/js/<?= \'test\' ?>/test1.js"></script>',
                '<script src="/js/<?= \'test\' ?>/test1.js"></script>',
            ];
        }
    }

    /**
     * @dataProvider dataProcessUrisInTags
     */
    public function testProcessUrisInTags(string $basePath, $expected, $tag) {
        $request = new class ($basePath) extends ArrayObject implements IRequest {
            private $baseUriPath;

            public function __construct($baseUriPath) {
                parent::__construct();
                $this->baseUriPath = $baseUriPath;
            }

            public function prependUriWithBasePath($uri) {
                $mkUri = function ($uri) {
                    return new class ($uri) {
                        private $uri;

                        public function __construct($uri) {
                            $this->uri = $uri;
                        }

                        public function toStr() {
                            return $this->uri;
                        }
                    };
                };
                if (strlen($uri) > 0) {
                    if ($uri[0] === '/' && (isset($uri[1]) && $uri[1] !== '/')) {
                        return $mkUri(rtrim($this->baseUriPath . $uri, '/'));
                    }
                }
                return $mkUri($uri);
            }

            public function isHandled(bool $flag = null): bool {
                // TODO: Implement isHandled() method.
            }

            public function setHandler(array $handler): void {
                // TODO: Implement setHandler() method.
            }

            public function handler(): array {
                // TODO: Implement handler() method.
            }

            public function setResponse(IResponse $response): void {
                // TODO: Implement setResponse() method.
            }

            public function response(): IResponse {
                // TODO: Implement response() method.
            }

            public function args(array|string|null $namesOrIndexes = null): mixed {
                // TODO: Implement args() method.
            }
        };

        $processor = new UriProcessor($request);

        $processedHtml = $processor->__invoke($tag);

        $this->assertHtmlEquals($expected, $processedHtml);
    }
}
