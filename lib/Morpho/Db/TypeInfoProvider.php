<?php
namespace Morpho\Db;

use function Morpho\Base\any;

class TypeInfoProvider {
    protected static $typeInfo = [
        // Numeric types:
        'TINYINT'    => [
            'storageInBytes'  => 1,
            'signedMin'       => -128,
            'signedMax'       => 127,
            'unsignedMin'     => 0,
            'unsignedMax'     => 255,
            'maxDisplayWidth' => 255,
            'allowedOptions'  => ['ZEROFILL', 'UNSIGNED', 'SIGNED'],
        ],
        'SMALLINT'   => [
            'storageInBytes'  => 2,
            'signedMin'       => -32768,
            'signedMax'       => 32767,
            'unsignedMin'     => 0,
            'unsignedMax'     => 65535,
            'maxDisplayWidth' => 255,
            'allowedOptions'  => ['ZEROFILL', 'UNSIGNED', 'SIGNED'],
        ],
        'MEDIUMINT'  => [
            'storageInBytes'  => 3,
            'signedMin'       => -8388608,
            'signedMax'       => 8388607,
            'unsignedMin'     => 0,
            'unsignedMax'     => 16777215,
            'maxDisplayWidth' => 255,
            'allowedOptions'  => ['ZEROFILL', 'UNSIGNED', 'SIGNED'],
        ],
        'INT'        => [
            'storageInBytes'  => 4,
            'signedMin'       => -2147483648,
            'signedMax'       => 2147483647,
            'unsignedMin'     => 0,
            'unsignedMax'     => 4294967295,
            'maxDisplayWidth' => 255,
            'allowedOptions'  => ['ZEROFILL', 'UNSIGNED', 'SIGNED'],
        ],
        'BIGINT'     => [
            'storageInBytes'  => 8,
            'signedMin'       => -9223372036854775808,
            'signedMax'       => 9223372036854775807,
            'unsignedMin'     => 0,
            'unsignedMax'     => 18446744073709551615,
            'maxDisplayWidth' => 255,
            'allowedOptions'  => ['ZEROFILL', 'UNSIGNED', 'SIGNED'],
        ],
        'FLOAT'      => [
            'storageInBytes' => 4,
            // 'if ($M <= 24) {return 4;} else {return 8;}'
            'allowedOptions' => ['ZEROFILL', 'UNSIGNED', 'SIGNED'],
            //-3.402823466E+38 to -1.175494351E-38, 0, and 1.175494351E-38 to 3.402823466E+38.
            'min'            => '?',
            'max'            => '?',
        ],
        'DOUBLE'     => [
            //  -1.7976931348623157E+308 to -2.2250738585072014E-308, 0, and 2.2250738585072014E-308 to 1.7976931348623157E+308.
            'storageInBytes' => 8,
            'allowedOptions' => ['ZEROFILL', 'UNSIGNED', 'SIGNED'],
        ],
        'DECIMAL'    => [
            // numberOfDigitsInIntegerPart = numberOfDigits - numberOfDecimals
            'storageInBytes'          => 'if ($M < $D) {return $D + 2;} elsif ($D > 0) {return $M + 2;} else {return $M + 1;}',
            // 'if ($M < $D) {return $D + 2;} elsif ($D > 0) {return $M + 2;} else {return $M + 1;}'
            'maxNumberOfDigits'       => 65,
            'minNumberOfDigits'       => 1,
            'defaultNumberOfDigits'   => 10,
            'minNumberOfDecimals'     => 0,
            'maxNumberOfDecimals'     => 30,
            'defaultNumberOfDecimals' => 0,
            'allowedOptions'          => ['ZEROFILL', 'UNSIGNED', 'SIGNED'],
        ],
        'BIT'        => [
            'storageInBytes'      => '(M+7)/8 bytes',
            'defaultNumberOfBits' => 1,
            'minNumberOfBits'     => 1,
            'maxNumberOfBits'     => 64,
        ],
        // Date and time types:
        'DATE'       => [
            'storageInBytes' => 3,
            'min'            => '1000-01-01',
            'max'            => '9999-12-31',
        ],
        'TIME'       => [
            'storageInBytes' => 3,
        ],
        'DATETIME'   => [
            'storageInBytes' => 8,
        ],
        'TIMESTAMP'  => [
            'storageInBytes' => 4,
        ],
        'YEAR'       => [
            'storageInBytes' => 1,
        ],
        // String types:
        'ENUM'       => [
            'storageInBytes' => '?',
        ],
        'CHAR'       => [
            'storageInBytes' => 'M × w bytes, where w is the number of bytes required for the maximum-length character in the character set',
        ],
        'BINARY'     => [
            'storageInBytes' => 'M bytes',
        ],
        'VARCHAR'    => [
            'storageInBytes' => '$M+1'
            // len + 1 bytes if column is 0 – 255 bytes, len + 2 bytes if column may require more than 255 bytes
        ],
        'VARBINARY'  => [
            'storageInBytes' => 'len + 1 bytes if column is 0 – 255 bytes, len + 2 bytes if column may require more than 255 bytes',
        ],
        'TINYBLOB'   => [
            'storageInBytes' => '$M+1',
            // len + 1 bytes
        ],
        'TINYTEXT'   => [
            'storageInBytes' => '$M+1',
            // len + 1 bytes
        ],
        'BLOB'       => [
            'storageInBytes' => '$M+2',
            // len + 2 bytes
        ],
        'TEXT'       => [
            'storageInBytes' => '$M+2',
            // len + 2 bytes
        ],
        'MEDIUMBLOB' => [
            'storageInBytes' => '$M+3',
            // len + 3 bytes
        ],
        'MEDIUMTEXT' => [
            'storageInBytes' => '$M+3'
            // len + 3 bytes
        ],
        'LONGBLOB'   => [
            'storageInBytes' => '$M+4'
            // len + 4 bytes
        ],
        'LONGTEXT'   => [
            'storageInBytes' => '$M+4'
            // len + 4 bytes
        ],
        'SET'        => [
            'storageInBytes' => '?',
        ],
        'JSON'       => [

        ],
    ];

    public static function getTypeInfo(string $type): array {
        $type = self::resolveSynonymOfType($type);
        if (!isset(self::$typeInfo[$type])) {
            throw new \UnexpectedValueException($type);
        }
        return self::$typeInfo[$type];
    }

    public static function resolveSynonymOfType(string $type): string {
        $type = strtoupper($type);
        if ($type === 'INTEGER') {
            return 'INT';
        }
        if ($type === 'DOUBLE PRECISION' || $type === 'REAL') {
            return 'DOUBLE';
        }
        if ($type === 'DEC' || $type === 'FIXED' || $type === 'NUMERIC') {
            return 'DECIMAL';
        }
        return $type;
    }

    public static function isSynonym(string $type): bool {
        return in_array(strtoupper($type), ['INTEGER', 'DOUBLE PRECISION', 'REAL', 'DEC', 'FIXED', 'NUMERIC']);
    }

    public static function isMacroType(string $type): bool {
        return in_array(strtoupper($type), ['SERIAL', 'BOOL', 'BOOLEAN', 'SERIAL DEFAULT VALUE'], true);
    }

    public static function expandMacroType(string $type): string {
        $type = strtoupper($type);
        if ($type === 'SERIAL') {
            return 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE';
        }
        if ($type === 'BOOL' || $type === 'BOOLEAN') {
            return 'TINYINT(1)';
        }
        if ($type === 'SERIAL DEFAULT VALUE') {
            return 'NOT NULL AUTO_INCREMENT UNIQUE';
        }
        throw new \UnexpectedValueException($type);
    }

    public static function isBitFieldType(string $type): bool {
        return strtoupper($type) === 'BIT';
    }

    public static function isNumericType(string $type): bool {
        return self::isBitFieldType($type) || self::isIntegerType($type) || self::isFloatingPointType($type) || self::isFixedPointType($type);
    }

    public static function isIntegerType(string $type): bool {
        return self::isOneOfTypes(self::resolveSynonymOfType($type), ['TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT']);
    }

    public static function isFloatingPointType(string $type): bool {
        return self::isOneOfTypes(self::resolveSynonymOfType($type), ['FLOAT', 'DOUBLE']);
    }

    public static function isFixedPointType(string $type): bool {
        return self::isOneOfTypes(self::resolveSynonymOfType($type), ['DECIMAL']);
    }

    public static function isDateOrTimeType(string $type): bool {
        return self::isOneOfTypes($type, ['DATE', 'TIME', 'DATETIME', 'TIMESTAMP', 'YEAR']);
    }

    public static function isStringType(string $type): bool {
        return self::isOneOfTypes($type, ['ENUM', 'CHAR', 'BINARY', 'VARBINARY', 'VARCHAR', 'TINYBLOB', 'TINYTEXT', 'BLOB', 'TEXT', 'MEDIUMBLOB', 'MEDIUMTEXT', 'LONGBLOB', 'LONGTEXT', 'SET']);
    }

    public static function isOneOfTypes(string $type, array $types) {
        return any(
            function ($expectedType) use ($type) {
                return self::typesEqual($type, $expectedType);
            },
            $types
        );
    }

    public static function typesEqual(string $type1, string $type2) {
        // @TODO: split by '(', and check type with ===
        return 0 === stripos($type1, $type2);
    }
}
