<?php
namespace Morpho\Web;

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

    public function maxNumberOfRedirects(): int {
        return $this->config['maxredirects'];
    }

    public function setMaxNumberOfRedirects(int $n): self {
        if ($n < 0) {
            throw new \InvalidArgumentException("The value must be >= 0");
        }
        $this->config['maxredirects'] = $n;
        return $this;
    }
}
