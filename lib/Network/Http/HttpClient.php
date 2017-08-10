<?php
namespace Morpho\Network\Http;

use function Morpho\Cli\cmd;
use Zend\Http\Client;
use Zend\Http\Response;
use Zend\Stdlib\Parameters;

class HttpClient extends Client {
    public function sendGet($uri, array $data = null, $headers = null): Response {
        if ($uri !== null) {
            $this->setUri($uri);
        }
        $request = $this->getRequest();
        if (null !== $headers) {
            $request->getHeaders()->addHeaders($headers);
        }
        if (null !== $data) {
            $request->setQuery(new Parameters((array) $data));
        }
        return $this->send($request);
    }

    public function sendPost($uri, array $data = null, $headers = null): Response {
        if ($uri !== null) {
            $this->setUri($uri);
        }
        $request = $this->getRequest();
        if (null !== $headers) {
            $request->getHeaders()->addHeaders($headers);
        }
        if (null !== $data) {
            $request->setPost(new Parameters((array) $data));
        }
        return $this->send($request);
    }

    public function setMaxNumberOfRedirects(int $n): self {
        if ($n < 0) {
            throw new \InvalidArgumentException("The value must be >= 0");
        }
        $this->config['maxredirects'] = $n;
        return $this;
    }

    public function maxNumberOfRedirects(): int {
        return $this->config['maxredirects'];
    }

    public static function downloadFile(string $uri, string $destFilePath = null): string {
        if (null === $destFilePath) {
            $destFilePath = getcwd() . '/' . basename($uri);
        }
        // @TODO: Implement without call of the external tool.
        // @TODO: use curl, wget or fetch, see the `man parallel`
        cmd('curl -L -o ' . escapeshellarg($destFilePath) . ' ' . escapeshellarg($uri));
        return $destFilePath;
    }
}
