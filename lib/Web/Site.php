<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Core\Site as BaseSite;

class Site extends BaseSite {
    /**
     * @var null|string
     */
    protected $hostName;

    public function __construct(string $moduleName, \ArrayObject $config, string $hostName) {
        parent::__construct($moduleName, $config);
        $this->hostName = $hostName;
    }

    public function hostName(): string {
        return $this->hostName;
    }
}