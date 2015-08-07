<?php
namespace Morpho\Web\View;

use Morpho\Base\PropertyNotFoundException;

class VarPlugin {
    private $pageHeader;

    private $pageTitle;

    public function __invoke() {
        return $this;
    }

    public function __isset($name) {
        return isset($this->$name);
    }

    public function __get($name) {
        if ($name == 'pageTitle' && null === $this->pageTitle) {
            $this->pageTitle = $this->pageHeader;
        }
        if (!property_exists($this, $name)) {
            throw new PropertyNotFoundException($this, $name);
        }
        return $this->$name;
    }

    public function __set($name, $value) {
        if (!property_exists($this, $name)) {
            throw new PropertyNotFoundException($this, $name);
        }
        $this->$name = $value;
    }
}
