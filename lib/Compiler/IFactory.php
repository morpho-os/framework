<?php declare(strict_types=1);
namespace Morpho\Compiler;

interface IFactory {
    public function mkFrontend(): callable;
    public function mkMidend(): callable;
    public function mkBackend(): callable;
}