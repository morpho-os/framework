<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Js;

class TscCompileOptions {
    private $cmdOptions;
    private $options;

    public function __construct(array $options = null, array $cmdOptions = null) {
        $this->options = (array) $options;
        $this->cmdOptions = (array) $cmdOptions;
    }

    public function setOptions(array $options) {
        $this->options = $options;
        return $this;
    }

    public function setCmdOptions(array $options) {
        $this->cmdOptions = $options;
        return $this;
    }

    public function options(): array {
        return $this->options ;
    }

    public function cmdOptions(): array {
        return $this->cmdOptions;
    }
}