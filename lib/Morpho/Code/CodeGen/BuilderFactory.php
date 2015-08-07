<?php
namespace Morpho\Code\CodeGen;

use PhpParser\BuilderFactory as BaseBuilderFactory;

class BuilderFactory extends BaseBuilderFactory {
    public function file() {
        return new FileBuilder();
    }
}
