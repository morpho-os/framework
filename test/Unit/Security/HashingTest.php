<?php declare(strict_types=1);
namespace Morpho\Test\Unit\Security;

use Morpho\Security\Hashing;
use Morpho\Testing\TestCase;

class HashingTest extends TestCase {
    public function dataForIsMd5Like() {
        yield [false, ''];
        yield [false, 'foo'];
        yield [true, md5('foo')];
        yield [false, 'testtesttesttesttesttesttesttest'];
        yield [false, 'testtesttesttesttesttesttesttes1'];
        yield [false, 'testtesttesttesttesttestTESTtes1'];
        yield [true, 'abcdabcdabcdabcdabcdabcdabcdabcd'];
        yield [true, 'abcdabcdabcdabcdabcdabcdabcda123'];
        yield [true, 'abcdAbcdabcdabcdAbcdabcdabcda123'];
    }

    /**
     * @dataProvider dataForIsMd5Like
     */
    public function testIsMd5Like(bool $expected, string $testString) {
        $this->assertSame($expected, Hashing::isMd5Like($testString));
    }
}
