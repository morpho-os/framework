<?php
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

    public function toArray() {
        return $this->getProperties();
    }

    public function fromArray($data) {
        $this->setProperties($data);
    }

    public function getNamespace($useFqn = false) {
        $class = get_class($this);
        return ($useFqn ? '\\' : '') . substr($class, 0, strrpos($class, '\\'));
    }

    public function getClassDirPath() {
        if (null === $this->classDirPath) {
            $this->classDirPath = dirname($this->getClassFilePath());
        }

        return $this->classDirPath;
    }

    public function getClassFilePath() {
        return str_replace('\\', '/', $this->reflect()->getFileName());
    }

    protected function setProperties($data) {
        foreach (array_intersect_key($data, $this->getNamesOfProperties()) as $name => $value) {
            $this->setProperty($name, $value);
        }

        return $this;
    }

    protected function getProperties() {
        $result = array();
        foreach ($this->getNamesOfProperties() as $name) {
            $result[$name] = $this->$name;
        }
        return $result;
    }

    protected function setProperty($name, $value) {
        $this->$name = $value;
        $this->properties[$name] = $name;
    }

    /**
     * @return \ReflectionObject
     */
    protected function reflect() {
        // @TODO: Do we need to cache here?
        if (null === $this->reflectedObject) {
            $this->reflectedObject = new \ReflectionObject($this);
        }

        return $this->reflectedObject;
    }

    protected function getNamesOfProperties() {
        if (null === $this->properties) {
            $properties = $this->reflect()->getProperties(
                \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED
            );
            $this->properties = array();
            foreach ($properties as $property) {
                $name = $property->getName();
                $this->properties[$name] = $name;
            }
        }
        return $this->properties;
    }
}
