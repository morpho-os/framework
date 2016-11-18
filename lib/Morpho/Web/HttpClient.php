<?php
namespace Morpho\Web;

use Zend\Http\Client;
use Zend\Http\Response;
use Zend\Stdlib\Parameters;

class HttpClient extends Client {
    public static function sendGet($uri, array $data = null, array $options = null): Response {
        $client = new static($uri, $options);
        $request = $client->getRequest();
        if (null !== $data) {
            $request->setQuery(new Parameters((array) $data));
        }
        return $client->send($request);
    }

    public static function sendPost($uri, array $data = null, array $options = null): Response {
        $client = new static($uri, $options);
        $request = $client->getRequest();
        if (null !== $data) {
            $request->setPost(new Parameters((array) $data));
        }
        return $client->send($request);
    }
}
