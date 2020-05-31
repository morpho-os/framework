<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network\Http;

use function Morpho\App\Cli\sh;
use Zend\Http\Client as HttpClientInternal;
use Zend\Stdlib\Parameters;
use Zend\Http\Request as RequestInternal;

class HttpClient {
    private $client;
    /**
     * @var int
     */
    private $maxNumberOfRedirects = 5;

    public function __construct() {
        $this->client = new HttpClientInternal();
    }

    public function send(string $httpMethod, string $uri, array $data = null, $headers = null): HttpResponse {
        $request = $this->client->getRequest();
        if (null !== $data) {
            switch ($httpMethod) {
                case RequestInternal::METHOD_GET:
                    $request->setQuery(new Parameters((array) $data));
                    break;
                case RequestInternal::METHOD_POST:
                    $request->setPost(new Parameters((array) $data));
                    break;
                default:
                    throw new \UnexpectedValueException();
            }
        }
        $this->client->setUri($uri);
        $request->setMethod($httpMethod);
        if (null !== $headers) {
            $request->getHeaders()->addHeaders($headers);
        }
        $this->client->setOptions(['maxredirects' => $this->maxNumberOfRedirects]);
        return $this->doSend($request);
    }

    public function get(string $uri, array $data = null, $headers = null): HttpResponse {
        return $this->send(RequestInternal::METHOD_GET, $uri, $data, $headers);
    }

    public function post(string $uri, array $data = null, $headers = null): HttpResponse {
        return $this->send(RequestInternal::METHOD_POST, $uri, $data, $headers);
    }

    public function setMaxNumberOfRedirects(int $n): void {
        if ($n < 0) {
            throw new \InvalidArgumentException("The value must be >= 0");
        }
        $this->maxNumberOfRedirects = $n;
    }

    public function maxNumberOfRedirects(): int {
        return $this->maxNumberOfRedirects;
    }

    /**
     * @return string Path to the downloaded file.
     */
    public static function download(string $uri, string $destPath = null): string {
        if (null === $destPath) {
            $destPath = \getcwd() . '/' . \basename($uri);
        } elseif (\is_dir($destPath)) {
            $destPath .= '/' . \basename($uri);
        }
        // @TODO: Implement without call of the external tool.
        // @TODO: use curl, wget or fetch, see the `man parallel`
        sh('curl -L -o ' . \escapeshellarg($destPath) . ' ' . \escapeshellarg($uri), ['show' => false]);
        return $destPath;
    }
    
    protected function doSend(RequestInternal $request): HttpResponse {
        return new HttpResponse($this->client->send($request));
    }
}
