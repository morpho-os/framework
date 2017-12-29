<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

class DeclareStmtManager {
    public function removeCommentedOutDeclareStmt(string $code): string {
        if (!preg_match('~^<\?php(?:[\040\t]|\n)~si', $code, $match)) {
            return $code;
        }
        $offset = strlen($match[0]);
        $n = strlen($code);

        $match = function ($re) use ($code, &$offset) {
            preg_match($re, $code, $match, 0, $offset);
            return $match;
        };
        $skip = function ($re) use ($match, &$offset) {
            $res = $match($re);
            if ($res) {
                $offset += strlen($res[0]);
            }
        };
        $skipSpaces = function () use ($skip) {
            $skip('~\s+~siA');
        };

        $declareRe = '~declare\s*\(strict_types\s*=\s*[01]\s*\)\s*;~siA';
        $multiCommentRe = '~/\*.*?\*/~siA';
        $singleCommentRe = '~//[^\n\r]*~siA';
        $declareFound = false;
        $locs = [];
        while ($offset < $n) {
            $skipSpaces();
            if ($match($multiCommentRe)) {
                $skip($multiCommentRe);
                $skipSpaces();
                continue;
            }
            if ($match($singleCommentRe)) {
                $res = $match('~//\s*declare\s*\(strict[^\n\r]*~siA');
                if ($res) {
                    $locs[] = [$offset, strlen($res[0])];
                }
                $skip($singleCommentRe);
                $skipSpaces();
                continue;
            }
            if (!$declareFound && $match($declareRe)) {
                $skip($declareRe);
                $skipSpaces();
                $declareFound = true;
                continue;
            }
            if ($match('~\S~siA')) {
                break;
            }
        }
        if ($locs) {
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
            return $newCode;
        }
        return $code;
    }
}