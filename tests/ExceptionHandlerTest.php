<?php

namespace SitPHP\Commands\Tests;

use Doubles\TestCase;
use Exception;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\ExceptionHandler;
use SitPHP\Commands\Request;

class ExceptionHandlerTest extends TestCase
{

    public function testException()
    {
        $command_manager = new CommandManager();
        $command_manager->setCommand('my_command', ExceptionCommandTest::class);
        $request = new Request('my_command' ,null, 'php://temp', 'php://memory', 'php://memory');

        try{
            $command_manager->run($request);
        } catch (Exception $e){
            $exception_handler = new ExceptionHandler();
            $exception_handler->handleForConsole($command_manager, $request, $e);
        }

        $handle = $request->getErrorOutput()->getHandle();
        rewind($handle);
        $output = stream_get_contents($handle);

        $this->assertContains('
 Error  : my message
in file "'.dirname(__FILE__).'/ExceptionHandlerTest.php" at line 65

Stacktrace :
-------------------
',$output);
    }


    function testVerboseException(){
        $command_manager = new CommandManager();
        $command_manager->setCommand('my_command', ExceptionCommandTest::class);
        $request = new Request('my_command' ,['--verbose'], 'php://temp', 'php://memory', 'php://memory');

        try{
            $command_manager->run($request);
        } catch (Exception $e){
            $exception_handler = new ExceptionHandler();
            $exception_handler->handleForConsole($command_manager, $request, $e);
        }

        $handle = $request->getErrorOutput()->getHandle();
        rewind($handle);
        $output = stream_get_contents($handle);

        $this->assertNotContains('Run command in verbose mode (--verbose) or debug mode (--debug) to see more stacktrace', $output);
    }
}


class ExceptionCommandTest extends Command{
    function handle(){
       throw new Exception('my message');
    }
}