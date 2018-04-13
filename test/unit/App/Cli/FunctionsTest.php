<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Cli;

use Morpho\Base\Environment;
use Morpho\Base\InvalidConfigException;
use function Morpho\App\Cli\{
    argsToStr, envVarsToStr, shell, escapeArgs, proc, showOk, stylize
};
use Morpho\App\Cli\ProcCommandResult;
use Morpho\Testing\TestCase;

class FunctionsTest extends TestCase {
    public function testShowOk() {
        ob_start();
        showOk();
        $this->assertEquals("OK\n", ob_get_clean());
    }

    public function dataForWriteErrorAndWriteErrorLn() {
        return [
            ['showError', 'Something went wrong', 'Something went wrong'],
            ['showErrorLn', "Space cow has arrived!\n", 'Space cow has arrived!'],
        ];
    }

    /**
     * @dataProvider dataForWriteErrorAndWriteErrorLn
     */
    public function testWriteErrorAndWriteErrorLn($fn, $expectedMessage, $error) {
        if (Environment::isWindows()) {
            $this->markTestSkipped();
        }

        $tmpFilePath = $this->createTmpFile();
        $autoloadFilePath = $this->sut()->baseDirPath() . '/vendor/autoload.php';
        file_put_contents($tmpFilePath, <<<OUT
<?php
require "$autoloadFilePath";
echo \\Morpho\\App\\Cli\\$fn("$error");
OUT
        );

        $fdSpec = [
            2 => ["pipe", "w"],  // stdout is a pipe that the child will write to
        ];
        $process = proc_open('php ' . escapeshellarg($tmpFilePath), $fdSpec, $pipes);

        $out = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);

        $this->assertEquals($expectedMessage, $out);
    }

    public function testStylize() {
        $magenta = 35;
        $text = "Hello";
        $this->assertEquals("\033[" . $magenta . "m$text\033[0m", stylize($text, $magenta));
    }

    public function testEscapeArgs() {
        $this->assertEquals(
            ["'foo'\\''bar'", "'test/'"],
            escapeArgs(["foo'bar", 'test/'])
        );
    }

    public function testArgsToStr() {
        $this->assertEquals(" 'foo'", argsToStr('foo'));
        $this->assertEquals(" 'foo' 'bar'", argsToStr(['foo', 'bar']));
        $gen = function () {
            yield 'foo';
            yield 'bar';
        };
        $this->assertEquals(" 'foo' 'bar'", argsToStr($gen()));
    }

    public function testShell_ThrowsExceptionOnInvalidConfigParam() {
        $this->expectException(InvalidConfigException::class);
        shell('ls', ['some invalid option' => 'value of invalid option']);
    }

    public function testShell_CommandAsString() {
        $result = shell('ls '  . escapeshellarg(__DIR__), ['capture' => true]);
        $this->assertEquals(0, $result->exitCode());
        $this->assertFalse($result->isError());
        $this->assertContains(basename(__FILE__), (string)$result);
    }

    public function testShell_CheckExitConfigParam() {
        $exitCode = 134;
        $this->expectException(\RuntimeException::class, "Command returned non-zero exit code: $exitCode");
        shell('php -r "exit(' . $exitCode . ');"');
    }

    public function testShellSu() {
        if ($this->isWindows()) {
            $this->markTestSkipped();
        }
        $this->markTestIncomplete();
    }

    public function testShell_EnvVarsConfigParam() {
        $var = 'v' . md5(__METHOD__);
        $val = 'hello';
        $this->assertSame($val . "\n", shell('echo $' . $var, ['envVars' => [$var => $val], 'capture' => true])->stdOut());
    }

    public function testEnvVarsToStr() {
        $this->assertSame("PATH='foo' TEST='foo'\''bar'", envVarsToStr(['PATH' => 'foo', 'TEST' => "foo'bar"]));
        $this->assertSame('', envVarsToStr([]));
    }

    public function testEnvVarsToStr_ThrowsExceptionForInvalidVarName() {
        $this->expectException(\RuntimeException::class, 'Invalid variable name');
        envVarsToStr(['&']);
    }

    public function testPipe() {
        $this->markTestIncomplete();
    }

    public function testAskYesNo() {
        if ($this->isWindows()) {
            $this->markTestSkipped();
        }

        $tmpFilePath = $this->createTmpFile();
        $autoloadFilePath = $this->sut()->baseDirPath() . '/vendor/autoload.php';
        $question = "Do you want to play";
        file_put_contents($tmpFilePath, <<<OUT
<?php
require "$autoloadFilePath";
echo json_encode(\\Morpho\\App\\Cli\\askYesNo("$question"));
OUT
        );

        $fdSpec = [
            0 => ["pipe", "r"],  // stdin is a pipe that the child will read from
            1 => ["pipe", "w"],  // stdout is a pipe that the child will write to
        ];
        $process = proc_open('php ' . escapeshellarg($tmpFilePath), $fdSpec, $pipes);

        fwrite($pipes[0], "what\ny\n");

        $out = stream_get_contents($pipes[1]);

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        proc_close($process);

        $this->assertEquals("$question? (y/n): Invalid choice, please type y or n\ntrue", $out);
    }

    public function testProc() {
        $cmd = 'ls -al ' . escapeshellarg(__DIR__);
        $result = proc($cmd);
        $this->assertInstanceOf(ProcCommandResult::class, $result);
        $this->assertSame($cmd, $result->command());
        $checkStdOut = function ($stdOut) {
            $this->assertContains(".\n", $stdOut);
            $this->assertContains("..\n", $stdOut);
            $this->assertContains(basename(__FILE__), $stdOut);
        };
        $checkStdOut($result->stdOut());
        $this->assertSame(0, $result->exitCode());
        $this->assertFalse($result->isError());
        $lines = iterator_to_array($result->lines());
        $this->assertTrue(count($lines) > 0);
        $checkStdOut(implode("\n", $lines));
    }

    public function testProc_CheckExit() {
        $this->expectException(\RuntimeException::class, 'Command returned non-zero exit code: ');
        proc('invalidcmd123_asnani2i2');
    }
}
