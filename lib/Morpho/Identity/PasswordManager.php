<?php
namespace Morpho\Identity;

use Zend\Math\Rand;

class PasswordManager {
    const PASS_LENGTH = 16;
    const MAX_PASS_LENGTH = 72;

    // Removed characters which can confuse: 0, '0', 'I', 1, 'l'.
    const ALLOWED_CHARS = '\\/~;:<>?!@#$%^&*(){}[].,-_=+abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    const COST = 12;

    public static function generatePassword(int $length = self::PASS_LENGTH): string {
        return Rand::getString($length, self::ALLOWED_CHARS, true);
    }

    public static function isOutdatedHash(string $passwordHash): bool {
        return password_needs_rehash($passwordHash, static::algo(), static::options());
    }

    /**
     * @return string Password hash, 60 characters.
     */
    public static function passwordHash(string $plainPassword): string {
        if (strlen($plainPassword) > self::MAX_PASS_LENGTH) {
            throw new \UnexpectedValueException("Password too long");
        }
        $passwordHash = password_hash($plainPassword, static::algo(), self::options());
        if (false === $passwordHash) {
            throw new \RuntimeException("Unable to generate password hash");
        }
        return $passwordHash;
    }

    public static function isValidPassword(string $plainPassword, string $passwordHash): bool {
        return password_verify($plainPassword, $passwordHash);
    }

    private static function options(): array {
        return ['cost' => self::COST];
    }

    private static function algo(): int {
        return PASSWORD_DEFAULT;
    }
}
