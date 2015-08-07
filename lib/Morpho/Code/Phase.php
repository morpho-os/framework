<?php
namespace Morpho\Code;

abstract class CompilerPhase {
    abstract public function run($input);
} 