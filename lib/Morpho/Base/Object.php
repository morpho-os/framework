<?php
declare(strict_types = 1);

namespace Morpho\Base;

abstract class Object {
    private $reflectedObject;

    private $classDirPath;

    private $properties;

    public function __construct(array $options = []) {
        if (count($options) > 0) {
            $this->setProperties($options);
        }
    }

    public function toArray(): array {
        return $this->getProperties();
    }

    public function fromArray(array $data) {
        $this->setProperties($data);
    }

    public function getNamespace(): string {
        $class = get_class($this);
        return substr($class, 0, strrpos($class, '\\'));
    }

    public function getClassDirPath(): string {
        if (null === $this->classDirPath) {
            $this->classDirPath = dirname($this->getClassFilePath());
        }

        return $this->classDirPath;
    }

    public function getClassFilePath(): string {
        return str_replace('\\', '/', $this->reflect()->getFileName());
    }

    protected function setProperties(array $data) {
        $propNames = $this->getNamesOfProperties();
        Assert::hasOnlyKeys($data, $propNames);
        foreach ($data as $name => $value) {
            $this->setProperty($name, $value);
        }

        return $this;
    }

    protected function getProperties(): array {
        $result = [];
        foreach ($this->getNamesOfProperties() as $name) {
            $result[$name] = $this->$name;
        }
        return $result;
    }

    protected function setProperty(string $name, $value) {
        $this->$name = $value;
        $this->properties[$name] = $name;
    }

    protected function reflect(): \ReflectionObject {
        if (null === $this->reflectedObject) {
            $this->reflectedObject = new \ReflectionObject($this);
        }
        return $this->reflectedObject;
    }

    protected function getNamesOfProperties(): array {
        if (null === $this->properties) {
            $properties = $this->reflect()->getProperties(
                \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED
            );
            $this->properties = [];
            foreach ($properties as $property) {
                $name = $property->getName();
                $this->properties[$name] = $name;
            }
        }
        return $this->properties;
    }
}
