<?php

namespace SitPHP\Commands\Tests;

use Doubles\TestCase;
use Exception;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\ExceptionHandler;
use SitPHP\Commands\Request;

class ExceptionHandlerTest extends TestCase
{

    public function testHandleForConsole()
    {
        $exception_handler = new ExceptionHandler();
        $command_manager = new CommandManager();
        $request = new Request('my_command' ,null, 'php://temp', 'php://memory', 'php://memory');
        $exception = new Exception('my message');

        $exception_handler->handleForConsole($command_manager, $request, $exception);
        $handle = $request->getErrorOutput()->getHandle();
        rewind($handle);
        $output = stream_get_contents($handle);

        $this->assertContains('
 Error  : my message
in file "'.dirname(__FILE__).'/ExceptionHandlerTest.php" at line 19

Stacktrace :
-------------------
',$output);
    }
}
