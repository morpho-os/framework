<?php
declare(strict_types=1);
namespace Morpho\Code\Js;

use Morpho\Code\StringReader;
use Morpho\Fs\Path;
use function Morpho\Base\contains;

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
                        $values[] = self::quote(self::fixModuleId($moduleId, $baseDirPath, $jsFilePath));
                        $hasModuleId = true;
                        break;
                    case 'deps':
                        if (!$hasModuleId) {

                            $values[] = self::quote(self::moduleIdFromPath($baseDirPath, $jsFilePath));
                        }
                        $value = '[';
                        $deps = [];
                        foreach ($token['value'] as $key => $dep) {
                            $unquoted = self::unquote($dep);
                            if ($unquoted === 'require' ||$unquoted === 'exports') {
                                $deps[] = self::quote($unquoted);
                            } else {
                                $deps[] = self::quote(self::fixModuleId($unquoted, $baseDirPath, $jsFilePath));
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

    private static function fixModuleId(string $moduleId, string $baseDirPath, string $jsFilePath): string {
        if ($moduleId[0] === '.' || contains($moduleId, '/')) {
            return $moduleId;
        }
        $newModuleId = Path::toRelative($baseDirPath, dirname($jsFilePath) . '/' . $moduleId);
        return $newModuleId;
    }

    private static function moduleIdFromPath(string $baseDirPath, string $jsFilePath): string {
        return Path::dropExt(Path::toRelative($baseDirPath, $jsFilePath));
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

        $this->reader->read('define(');
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
        $this->reader->read('[');
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
        $this->reader->read(']');
        $this->emit(__FUNCTION__, $deps);
    }

    private function moduleInitializer() {
        $s = $this->reader->readMatching('~function\s*\([^)]*?\)\s*\{~si');
        $this->emit(__FUNCTION__, $s);
    }

    private function skipArgSep() {
        $this->reader->readMatching('~,\s+~si');
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