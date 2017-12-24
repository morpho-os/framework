<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Base\IFn;

class JsonRenderer implements IFn {
    /**
     * @param \Morpho\Web\Request $request
     * @return string
     */
    public function __invoke($request) {
        d($request);

        /**
         * @Listen beforeDispatch -9999
         * /
        public function beforeDispatch($event) {
        //$this->autoDecodeRequestJson();
        /*
        $request = $this->request;
        $header = $request->header('Content-Type');
        if (false !== $header && false !== stripos($header->getFieldValue(), 'application/json')) {
        $data = Json::decode($request->content());
        $request->replace((array) $data);
        }
        }
         *
         *
         * "application/json; charset=utf-8"
         */
    }
}