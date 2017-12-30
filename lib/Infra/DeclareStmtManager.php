<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

class DeclareStmtManager {
    private $offset;
    private $code;
    private const OPEN_TAG_RE = '~^<\?php(?:[\040\t]|\n)~si';
    private const MULTI_COMMENT_RE = '~/\*.*?\*/~siA';
    private const SINGLE_COMMEN_RE = '~//[^\n\r]*~siA';
    private const DECLARE_RE = '~declare\s*\(strict_types\s*=\s*[01]\s*\)\s*;~siA';

    public function removeCommentedOutDeclareStmt(string $code): string {
        if (!preg_match(self::OPEN_TAG_RE, $code, $match)) {
            return $code;
        }
        $this->code = $code;
        $this->offset = strlen($match[0]);
        $n = strlen($code);

        $declareFound = false;
        $locs = [];
        while ($this->offset < $n) {
            $this->skipSpaces();
            if ($this->match(self::MULTI_COMMENT_RE)) {
                $this->skip(self::MULTI_COMMENT_RE);
                $this->skipSpaces();
                continue;
            }
            if ($this->match(self::SINGLE_COMMEN_RE)) {
                $res = $this->match('~//\s*declare\s*\(strict[^\n\r]*~siA');
                if ($res) {
                    $locs[] = [$this->offset, strlen($res[0])];
                }
                $this->skip(self::SINGLE_COMMEN_RE);
                $this->skipSpaces();
                continue;
            }
            if (!$declareFound && $this->match(self::DECLARE_RE)) {
                $this->skip(self::DECLARE_RE);
                $this->skipSpaces();
                $declareFound = true;
                continue;
            }
            if ($this->match('~\S~siA')) {
                break;
            }
        }
        if ($locs) {
            return $this->removeLocs($code, $locs);
        }
        return $code;
    }

    public function removeDeclareStmt(string $code): string {
        if (!preg_match(self::OPEN_TAG_RE, $code, $match)) {
            return $code;
        }
        $this->code = $code;
        $this->offset = strlen($match[0]);
        $n = strlen($code);

        $locs = [];
        while ($this->offset < $n) {
            $this->skipSpaces();
            if ($this->match(self::MULTI_COMMENT_RE)) {
                $this->skip(self::MULTI_COMMENT_RE);
                $this->skipSpaces();
                continue;
            }
            if ($this->match(self::SINGLE_COMMEN_RE)) {
                $this->skip(self::SINGLE_COMMEN_RE);
                $this->skipSpaces();
                continue;
            }
            if ($res = $this->match(self::DECLARE_RE)) {
                $locs[] = [$this->offset, strlen($res[0])];
                break;
            }
            if ($this->match('~\S~siA')) {
                break;
            }
        }
        if ($locs) {
            return $this->removeLocs($code, $locs);
        }
        return $code;
    }

    private function skipSpaces(): void {
        $this->skip('~\s+~siA');
    }

    private function skip(string $re): void {
        $res = $this->match($re);
        if ($res) {
            $this->offset += strlen($res[0]);
        }
    }

    private function match(string $re): array {
        preg_match($re, $this->code, $match, 0, $this->offset);
        return $match;
    }

    private function removeLocs(string $code, array $locs): string {
        $newCode = '';
        $p = 0;
        foreach ($locs as $loc) {
            [$offset, $length] = $loc;
            $newCode .= substr($code, $p, $offset);
            if (preg_match('~^<\?php\s+$~si', $newCode)) {
                $newCode = rtrim($newCode) .  substr($code, $offset + $length);
            } else {
                $newCode .= ltrim(substr($code, $offset + $length));
            }
            $p += $length;
        }
        return rtrim($newCode);
    }
}