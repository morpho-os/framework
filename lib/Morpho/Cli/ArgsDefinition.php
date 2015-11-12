<?php
namespace Morpho\Cli;

class ArgsDefinition implements \IteratorAggregate {
    protected $definition = [];

    public function shortArg(string $name, bool $isRequired = true): ShortArgDefinition {
        $this->definition[] = $definition = new ShortArgDefinition($name, $isRequired);
        return $definition;
    }

    public function shortBoolArg(string $name): ShortArgDefinition {
        $this->definition[] = $definition = new ShortBoolArgDefinition($name);
        return $definition;
    }

    public function longArg($name, $isRequired = true): LongArgDefinition {
        $this->definition[] = $definition = new LongArgDefinition($name, $isRequired);
        return $definition;
    }

    public function longBoolArg($name): LongArgDefinition {
        $this->definition[] = $definition = new LongBoolArgDefinition($name);
        return $definition;
    }

    public function getIterator() {
        return new \ArrayIterator($this->definition);
    }
}

class ArgDefinition {
    protected $name;

    protected $isRequired = true;

    protected $hasValue = true;

    public function __construct(string $name, bool $isRequired, $hasValue = true) {
        $this->name = $name;
        $this->isRequired = $isRequired;
        $this->hasValue($hasValue);
    }

    public function isRequired($flag = null) {
        if (null !== $flag) {
            $this->isRequired = $flag;
        }
        return $this->isRequired;
    }

    public function getName() {
        return $this->name;
    }

    public function hasValue($flag = null) {
        if (null !== $flag) {
            if ($this->isRequired) {
                if (false === $flag) {
                    throw new \LogicException();
                }
                $this->hasValue = true;
            } else {
                $this->hasValue = $flag;
            }
        }
        return $this->hasValue;
    }
}

class ShortArgDefinition extends ArgDefinition {
}

class LongArgDefinition extends ArgDefinition {
}

trait TBoolArgDefinition {
    public function __construct(string $name) {
        parent::__construct($name, false, false);
    }

    abstract public function hasValue($flag = null);
}

class ShortBoolArgDefinition extends ShortArgDefinition {
    use TBoolArgDefinition;
}

class LongBoolArgDefinition extends LongArgDefinition {
    use TBoolArgDefinition;
}