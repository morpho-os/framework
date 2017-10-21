<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

class Site {
    protected $moduleName;
    protected $hostName;

    public function __construct($moduleName, $hostName) {
        $this->moduleName = $moduleName;
        $this->hostName = $hostName;
    }

    public function moduleName(): string {
        return $this->moduleName;
    }

    public function hostName(): ?string {
        return $this->hostName;
    }
}