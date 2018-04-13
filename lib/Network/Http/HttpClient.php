<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network\Http;

use function Morpho\App\Cli\shell;
use Zend\Http\Client;
use Zend\Stdlib\Parameters;

class HttpClient {
    private $client;
    /**
     * @var int
     */
    private $maxNumberOfRedirects = 5;

    public function __construct() {
        $this->client = new Client();
    }

    public function get($uri, array $data = null, $headers = null): HttpResponse {
        if ($uri !== null) {
            $this->client->setUri($uri);
        }
        $request = $this->client->getRequest();
        if (null !== $headers) {
            $request->getHeaders()->addHeaders($headers);
        }
        if (null !== $data) {
            $request->setQuery(new Parameters((array) $data));
        }
        $this->client->setOptions(['maxredirects' => $this->maxNumberOfRedirects]);
        $res = $this->client->send($request);
        return new HttpResponse($res);
    }

    public function post($uri, array $data = null, $headers = null): HttpResponse {
        if ($uri !== null) {
            $this->client->setUri($uri);
        }
        $request = $this->client->getRequest();
        if (null !== $headers) {
            $request->getHeaders()->addHeaders($headers);
        }
        if (null !== $data) {
            $request->setPost(new Parameters((array) $data));
        }
        $this->client->setOptions(['maxredirects' => $this->maxNumberOfRedirects]);
        return new HttpResponse($this->client->send($request));
    }

    public function setMaxNumberOfRedirects(int $n): self {
        if ($n < 0) {
            throw new \InvalidArgumentException("The value must be >= 0");
        }
        $this->maxNumberOfRedirects = $n;
        return $this;
    }

    public function maxNumberOfRedirects(): int {
        return $this->maxNumberOfRedirects;
    }

    /**
     * @return string Path to the downloaded file.
     */
    public static function download(string $uri, string $destPath = null): string {
        if (null === $destPath) {
            $destPath = getcwd() . '/' . basename($uri);
        } elseif (is_dir($destPath)) {
            $destPath .= '/' . basename($uri);
        }
        // @TODO: Implement without call of the external tool.
        // @TODO: use curl, wget or fetch, see the `man parallel`
        shell('curl -L -o ' . escapeshellarg($destPath) . ' ' . escapeshellarg($uri));
        return $destPath;
    }
}
