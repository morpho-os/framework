<?php declare(strict_types=1);
namespace Morpho\App;

class Module extends \ArrayObject {
    protected string $name;

    public function __construct(string $name, $meta) {
        $this->name = $name;
        parent::__construct($meta);
    }

    public function name(): string {
        return $this->name;
    }

    public function dirPath(): string {
        return $this['path']['dirPath'];
    }
}
