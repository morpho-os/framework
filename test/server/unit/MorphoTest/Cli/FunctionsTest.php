<?php
namespace MorphoTest\Cli;

use Morpho\Base\Environment;
use Morpho\Base\InvalidOptionsException;
use function Morpho\Cli\{
    cmd, escapeArgs, showOk, stylize
};
use Morpho\Test\TestCase;
use const Morpho\Core\BASE_DIR_PATH;

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
        $autoloadFilePath = BASE_DIR_PATH . '/vendor/autoload.php';
        file_put_contents($tmpFilePath, <<<OUT
<?php
require "$autoloadFilePath";
echo \\Morpho\\Cli\\$fn("$error");
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

    public function testCmd_ThrowsExceptionOnInvalidOption() {
        $this->expectException(InvalidOptionsException::class);
        cmd('ls', ['some invalid option' => 'value of invalid option']);
    }

    public function testCmd_CommandAsString() {
        $result = cmd('ls '  . escapeshellarg(__DIR__), ['buffer' => true]);
        $this->assertEquals(0, $result->exitCode());
        $this->assertFalse($result->isError());
        $this->assertContains(basename(__FILE__), (string)$result);
    }

    public function testCmd_CheckExitOption() {
        $exitCode = 134;
        $this->expectException(\RuntimeException::class, "Command returned non-zero exit code: $exitCode");
        cmd('php -r "exit(' . $exitCode . ');"');
    }

    public function testCmdSu() {
        if ($this->windowsSys()) {
            $this->markTestSkipped();
        }
        $this->markTestIncomplete();
    }

    public function testPipe() {
        $this->markTestIncomplete();
    }

    public function testAskYesNo() {
        if ($this->windowsSys()) {
            $this->markTestSkipped();
        }

        $tmpFilePath = $this->createTmpFile();
        $autoloadFilePath = BASE_DIR_PATH . '/vendor/autoload.php';
        $question = "Do you want to play";
        file_put_contents($tmpFilePath, <<<OUT
<?php
require "$autoloadFilePath";
echo json_encode(\\Morpho\\Cli\\askYesNo("$question"));
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
}
