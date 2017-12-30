<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Ioc\ServiceManager;

abstract class HtmlProcessor extends HtmlSemiParser {
    protected const SKIP_ATTR = '_skip';

    protected $serviceManager;

    protected $filePath;

    private $request;

    public function __construct(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        parent::__construct();
    }

    public function setFilePath(string $filePath): void {
        $this->filePath = $filePath;
    }

    protected function request() {
        if (null === $this->request) {
            $this->request = $this->serviceManager->get('request');
        }
        return $this->request;
    }
}