<?php
//declare(strict_types=1);
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