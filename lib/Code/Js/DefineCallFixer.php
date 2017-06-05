<?php
declare(strict_types=1);
namespace Morpho\Code\Js;

use Morpho\Code\StringReader;
use Morpho\Fs\Path;

class DefineCallFixer {
    public static function fixDefineCall(string $line, string $baseDirPath, string $jsFilePath): string {
        $tokens = (new DefineFnCallParser())($line);

        $eval = function (array $tokens) use ($baseDirPath, $jsFilePath): string {
            $values = [];
            $hasModuleId = false;
            foreach ($tokens as $token) {
                switch ($token['name']) {
                    case 'moduleId':
                        $moduleId = self::unquote($token['value']);
                        if ($moduleId === 'require' || $moduleId === 'exports') {
                            throw new \UnexpectedValueException("The 'require' or 'exports' names can't be used as module ID");
                        }
                        $values[] = self::quote(self::resolvePath($moduleId, $baseDirPath, $jsFilePath));
                        $hasModuleId = true;
                        break;
                    case 'deps':
                        if (!$hasModuleId) {
                            $values[] = self::quote(Path::dropExt(Path::toRelative($baseDirPath, $jsFilePath)));
                        }
                        $value = '[';
                        $deps = [];
                        foreach ($token['value'] as $key => $dep) {
                            $unquoted = self::unquote($dep);
                            if ($unquoted === 'require' ||$unquoted === 'exports') {
                                $deps[] = self::quote($unquoted);
                            } else {
                                $deps[] = self::quote(self::resolvePath($unquoted, $baseDirPath, $jsFilePath));
                            }
                        }
                        $value .= implode(', ', $deps);
                        $value .= ']';
                        $values[] = $value;
                        break;
                    case 'moduleInitializer':
                        $values[] = $token['value'];
                        break;
                    default:
                        throw new \UnexpectedValueException();
                }
            }
            return implode(', ', $values);
        };

        return 'define(' . $eval($tokens);
    }

    private static function quote(string $s): string {
        return '"' . $s . '"';
    }

    private static function unquote(string $s): string {
        return trim($s, '"');
    }

    private static function resolvePath(string $moduleId, string $baseDirPath, string $curTsFilePath): string {
        if ($moduleId[0] === '.') {
            return $moduleId;
        }
        return Path::toRelative($baseDirPath, dirname($curTsFilePath)) . '/' . $moduleId;
    }
}

class DefineFnCallParser {
    /**
     * @var StringReader
     */
    private $reader;
    private $tokens;

    function __invoke(string $line): array {
        $this->reader = new StringReader($line);
        $this->tokens = [];

        $this->reader->skip('define(');
        if ($this->reader->peek1() !== '[') {
            $this->moduleId();
            $this->skipArgSep();
        }
        $this->deps();
        $this->skipArgSep();
        $this->moduleInitializer();

        return $this->tokens;
    }

    private function moduleId(): void {
        $str = $this->reader->readDoubleQuotedString();
        $this->emit(__FUNCTION__, $str);
    }

    private function deps(): void {
        $deps = [];
        $this->reader->skip('[');
        while (true) {
            if ($this->reader->peek1() === ']') {
                break;
            }
            $str = $this->reader->readDoubleQuotedString();
            $deps[] = $str;
            if ($this->reader->peek1() === ']') {
                break;
            }
            $this->skipArgSep();
        }
        $this->reader->skip(']');
        $this->emit(__FUNCTION__, $deps);
    }

    private function moduleInitializer() {
        $s = $this->reader->readRe('~function\s*\([^)]*?\)\s*\{~si');
        $this->emit(__FUNCTION__, $s);
    }

    private function skipArgSep() {
        $this->reader->readRe('~,\s+~si');
    }

    private function emit(string $name, $value): void {
/*        if (!constant(self::class . '::' . $name)) {
            throw new \UnexpectedValueException();
        }*/
        $this->tokens[] = [
            'name' => $name,
            'value' => $value,
        ];
    }
}