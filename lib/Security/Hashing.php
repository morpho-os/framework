<?php declare(strict_types=1);
namespace Morpho\Security;

class Hashing {
    public static function isMd5Like(string $testString): bool {
        if (!isset($testString[0])) {
            return false;
        }
        return (bool) preg_match('~^[a-fA-F\d]{32}$~s', $testString);
    }
}
