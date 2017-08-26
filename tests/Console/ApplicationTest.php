<?php
namespace ngyuki\DbImport\Test\Console;

use ngyuki\DbImport\Console\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @author ngyuki
 */
class ApplicationTest extends TestCase
{
    public function dispatch($argv)
    {
        array_unshift($argv, __FILE__);

        $input = new ArgvInput($argv);
        $output = new BufferedOutput();

        $application = new Application();
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        return $application->run($input, $output);
    }

    /**
     * @test
     */
    public function list_()
    {
        $code = $this->dispatch(['list']);
        assertThat($code, equalTo(0));
    }

    /**
     * @test
     */
    public function import_ok()
    {
        $example = __DIR__ . '/../../example';
        $code = $this->dispatch(array_merge(['import', '-c', $example], glob("$example/files/*")));
        assertThat($code, equalTo(0));
    }

    /**
     * @test
     */
    public function import_v()
    {
        $example = __DIR__ . '/../../example';
        $code = $this->dispatch(array_merge(['import', '-c', $example, '-v'], glob("$example/files/*")));
        assertThat($code, equalTo(0));
    }

    /**
     * @test
     */
    public function import_vvv()
    {
        $example = __DIR__ . '/../../example';
        $code = $this->dispatch(array_merge(['import', '-c', $example, '-vvv'], glob("$example/files/*")));
        assertThat($code, equalTo(0));
    }
}
