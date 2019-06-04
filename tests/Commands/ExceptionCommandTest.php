<?php


namespace SitPHP\Commands\Tests\Commands;


use Doubles\TestCase;
use Exception;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Commands\ExceptionCommand;

class ExceptionCommandTest extends TestCase
{

    function testExceptionCommand(){

        $exception = new Exception('my message');

        $command = new ExceptionCommand();
        $command->setException($exception);

        $command_manager = new CommandManager();
        $command_manager->setCommand('exception', $command);
        $exception_test = $command_manager->test();
        $output = $exception_test->callCommandAs($command, 'exception');

        $this->assertContains('
 Error  : my message
in file "'.dirname(__DIR__).'/Commands/ExceptionCommandTest.php" at line 17
', $output);
    }

    function testVerboseExceptionCommand(){
        $exception = new Exception('my message');

        $command = new ExceptionCommand();
        $command->setException($exception);

        $command_manager = new CommandManager();
        $exception_test = $command_manager->test();
        $output = $exception_test->callCommandAs($command, 'exception', ['--verbose']);

        $this->assertNotContains('Run command in verbose mode (--verbose) or debug mode (--debug) to see more stacktrace', $output);
    }
}