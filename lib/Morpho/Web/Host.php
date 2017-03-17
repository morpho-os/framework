<?php
namespace Morpho\Web;

class Host {
    /**
     * @var ?string
     */
    public $alias;

    /**
     * @var ?string
     */
    public $name;

    public function __construct(?string $alias, ?string $name) {
        $this->alias = $alias;
        $this->name = $name;
    }
}