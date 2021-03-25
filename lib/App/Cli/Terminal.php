<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

// See symfony/console
class Terminal {
    /**
     * @var resource
     */
    protected $stdout;
    /**
     * @var resource
     */
    private $stderr;

    public function __construct() {
        $this->stdout = \STDOUT ?? fopen('php://output', 'w');
        $this->stderr = \STDERR ?? fopen('php://stderr', 'w');
    }

    public function write(string $text, bool $newLn = true): void {
        fwrite($this->stdout, $text . ($newLn ? "\n" : ''));
        fflush($this->stdout);
    }

    public function writeErr(string $text, bool $newLn = true): void {
        fwrite($this->stderr, $text . ($newLn ? "\n" : ''));
        fflush($this->stderr);
    }
}

