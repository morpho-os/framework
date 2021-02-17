<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Php\Reflection;

use Morpho\Base\IFn;
use function array_diff;
use function array_values;

class StdClassTypeFilter implements IFn {
    public static array $stdClasses = [
        'AppendIterator',
        'ArithmeticError',
        'ArrayAccess',
        'ArrayIterator',
        'ArrayObject',
        'AssertionError',
        'BadFunctionCallException',
        'BadMethodCallException',
        'CURLFile',
        'CachingIterator',
        'CallbackFilterIterator',
        'ClosedGeneratorException',
        'Closure',
        'Collator',
        'Countable',
        'DOMAttr',
        'DOMCdataSection',
        'DOMCharacterData',
        'DOMComment',
        'DOMConfiguration',
        'DOMDocument',
        'DOMDocumentFragment',
        'DOMDocumentType',
        'DOMDomError',
        'DOMElement',
        'DOMEntity',
        'DOMEntityReference',
        'DOMErrorHandler',
        'DOMException',
        'DOMImplementation',
        'DOMImplementationList',
        'DOMImplementationSource',
        'DOMLocator',
        'DOMNameList',
        'DOMNameSpaceNode',
        'DOMNamedNodeMap',
        'DOMNode',
        'DOMNodeList',
        'DOMNotation',
        'DOMProcessingInstruction',
        'DOMStringExtend',
        'DOMStringList',
        'DOMText',
        'DOMTypeinfo',
        'DOMUserDataHandler',
        'DOMXPath',
        'DateInterval',
        'DatePeriod',
        'DateTime',
        'DateTimeImmutable',
        'DateTimeInterface',
        'DateTimeZone',
        'Directory',
        'DirectoryIterator',
        'DivisionByZeroError',
        'DomainException',
        'EmptyIterator',
        'Error',
        'ErrorException',
        'Exception',
        'FilesystemIterator',
        'FilterIterator',
        'Generator',
        'GlobIterator',
        'InfiniteIterator',
        'IntlBreakIterator',
        'IntlCalendar',
        'IntlChar',
        'IntlCodePointBreakIterator',
        'IntlDateFormatter',
        'IntlException',
        'IntlGregorianCalendar',
        'IntlIterator',
        'IntlPartsIterator',
        'IntlRuleBasedBreakIterator',
        'IntlTimeZone',
        'InvalidArgumentException',
        'Iterator',
        'IteratorAggregate',
        'IteratorIterator',
        'JsonSerializable',
        'LengthException',
        'LibXMLError',
        'LimitIterator',
        'Locale',
        'LogicException',
        'MessageFormatter',
        'MultipleIterator',
        'NoRewindIterator',
        'Normalizer',
        'NumberFormatter',
        'OutOfBoundsException',
        'OutOfRangeException',
        'OuterIterator',
        'OverflowException',
        'PDO',
        'PDOException',
        'PDORow',
        'PDOStatement',
        'ParentIterator',
        'ParseError',
        'Phar',
        'PharData',
        'PharException',
        'PharFileInfo',
        'RangeException',
        'RecursiveArrayIterator',
        'RecursiveCachingIterator',
        'RecursiveCallbackFilterIterator',
        'RecursiveDirectoryIterator',
        'RecursiveFilterIterator',
        'RecursiveIterator',
        'RecursiveIteratorIterator',
        'RecursiveRegexIterator',
        'RecursiveTreeIterator',
        'Reflection',
        'ReflectionClass',
        'ReflectionClassConstant',
        'ReflectionException',
        'ReflectionExtension',
        'ReflectionFunction',
        'ReflectionFunctionAbstract',
        'ReflectionGenerator',
        'ReflectionMethod',
        'ReflectionObject',
        'ReflectionParameter',
        'ReflectionProperty',
        'ReflectionType',
        'ReflectionZendExtension',
        'Reflector',
        'RegexIterator',
        'ResourceBundle',
        'RuntimeException',
        'SeekableIterator',
        'Serializable',
        'SessionHandler',
        'SessionHandlerInterface',
        'SessionIdInterface',
        'SessionUpdateTimestampHandlerInterface',
        'SplDoublyLinkedList',
        'SplFileInfo',
        'SplFileObject',
        'SplFixedArray',
        'SplHeap',
        'SplMaxHeap',
        'SplMinHeap',
        'SplObjectStorage',
        'SplObserver',
        'SplPriorityQueue',
        'SplQueue',
        'SplStack',
        'SplSubject',
        'SplTempFileObject',
        'Spoofchecker',
        'Throwable',
        'Transliterator',
        'Traversable',
        'TypeError',
        'UConverter',
        'UnderflowException',
        'UnexpectedValueException',
        'ZipArchive',
        '__PHP_Incomplete_Class',
        'finfo',
        'php_user_filter',
        'stdClass',
    ];
    
    public function __invoke(mixed $classTypes): array {
        return array_values(
            array_diff(
                $classTypes,
                self::$stdClasses
            )
        );
    }
}
