<?php
namespace Morpho\Cli;

use function Morpho\Base\{filter, all, any, fold};
use Morpho\Code\Compiler\Lexer;

class ArgsHandler {
    protected $argv;

    protected $parsedArgs;

    protected $definition;

    const SHORT_ARG_TOKEN  = 1;
    const LONG_ARG_TOKEN   = 2;
    const VALUE_ARG_TOKEN  = 3;
    const WHITESPACE_TOKEN = 4;

    public function setArgValues(array $args) {
        $this->argv = $args;
    }

    public function getArgValues(): array {
        if (null === $this->argv) {
            $args = $_SERVER['argv'];
            array_shift($args);
            $this->argv = $args;
        }
        return $this->argv;
    }

    public function define(): ArgsDefinition {
        $definition = $this->definition = new ArgsDefinition();
        return $definition;
    }

    public function hasShortArg(string $name): bool {
        return array_key_exists($name, $this->parseArgs()['short']);
    }

    public function hasLongArg(string $name): bool {
        return array_key_exists($name, $this->parsedArgs()['long']);
    }

    public function getValueArgs(): array {
        return $this->parsedArgs()['values'];
    }

    protected function parseArgs(): array {
        $lexer = new Lexer([
            '\s+' => self::WHITESPACE_TOKEN,
            '-[a-z][a-z_0-9-]*(=\S+)?' => self::SHORT_ARG_TOKEN,
            '--[a-z][a-z_0-9-]*(=\S+)?' => self::LONG_ARG_TOKEN,
            '\S+' => self::VALUE_ARG_TOKEN,
        ]);
        $tokens = $lexer->run(implode(' ', $this->getArgValues()));

        $nameValue = function (array $token, $offset) {
            $nameValue = explode('=', substr($token[2], $offset));
            $name = array_shift($nameValue);
            $value = array_shift($nameValue);
            if (null === $value) {
                $value = true;
            }
            return [$name, $value];
        };
        $args = [];
        foreach ($tokens as $token) {
            switch ($token[0]) {
                case self::LONG_ARG_TOKEN:
                    list($name, $value) = $nameValue($token, 2);
                    $args[] = ['name' => $name, 'value' => $value, 'type' => self::LONG_ARG];
                    break;

                case self::SHORT_ARG_TOKEN:
                    list($name, $value) = $nameValue($token, 1);
                    $args[] = ['name' => $name, 'value' => $value, 'type' => self::SHORT_ARG];
                    break;

                case self::VALUE_ARG_TOKEN:
                    $args[] = ['name' => null, 'value' => $token[2], 'type' => self::VALUE_ARG];
                    break;

                case self::WHITESPACE_TOKEN:
                    break;

                default:
                    throw new \UnexpectedValueException("Unexpected token");
            }
        }

        return $this->checkArgs($this->definition, $args);
    }

    protected function checkArgs(ArgsDefinition $argsDefinition, array $args): array {
        $argWithName = function ($name) {
            return function ($arg) use ($name) {
                return $arg['name'] === $name;
            };
        };
        $hasType = function ($type) {
            return function (array $arg) use ($type) {
                return $arg['type'] === $type;
            };
        };
        $unsetArgsWithName = function (array $args, string $name) {
            return filter(function ($arg) use ($name) {
                return $arg['name'] !== $name;
            }, $args, true);
        };

        $extractValues = function (array $args) {
            return fold(function ($prev, $cur) {
                $prev[] = $cur['value'];
                return $prev;
            }, $args, []);
        };
        $checkedArgs = ['short' => [], 'long' => [], 'values' => []];
        foreach ($argsDefinition as $argDefinition) {
            $argName = $argDefinition->getName();

            $argsWithName = filter($argWithName($argName), $args);
            if (!count($argsWithName)) {
                if ($argDefinition->hasValue() && $argDefinition->isRequired()) {
                    // @TODO: Specify different Exception class.
                    throw new \RuntimeException();
                }
            }
            if ($argDefinition instanceof ShortArgDefinition) {
                if (!all($hasType(self::SHORT_ARG), $argsWithName)) {
                    // @TODO: Specify different Exception class.
                    throw new \RuntimeException();
                }
                $checkedArgs['short'][$argName] = $extractValues($argsWithName);
            } elseif ($argDefinition instanceof LongArgDefinition) {
                if (!all($hasType(self::LONG_ARG), $argsWithName)) {
                    // @TODO: Specify different Exception class.
                    throw new \RuntimeException();
                }
                $checkedArgs['long'][$argName] = $extractValues($argsWithName);
            } else {
                throw new \UnexpectedValueException();
            }

            $args = $unsetArgsWithName($args, $argName);
        }

        $splitArgsByName = function (array $args) {
            $withName = $withoutName = [];
            foreach ($args as $arg) {
                if (null === $arg['name']) {
                    $withoutName[] = $arg['value'];
                } else {
                    $withName[] = $arg;
                }
            }
            return [$withName, $withoutName];
        };
        list($withName, $withoutName) = $splitArgsByName($args);
        if (count($withName)) {
            // @TODO: More verbose Exception, and different Exception class.
            throw new \RuntimeException("Undefined arguments found: " . print_r($withName, true));
        }
        $checkedArgs['values'] = $withoutName;

        return $checkedArgs;
    }

    protected function parsedArgs(): array {
        if (null === $this->parsedArgs) {
            $this->parsedArgs = $this->parseArgs();
        }
        return $this->parsedArgs;
    }
}