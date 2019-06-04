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

        $this->assertEquals('
 Error  : my message
in file "'.dirname(__FILE__).'/ExceptionHandlerTest.php" at line 19

Stacktrace :
-------------------
#1 SitPHP\Commands\Tests\ExceptionHandlerTest->testHandleForConsole                                 
   /Users/alexandre/Sites/sitphp/commands/vendor/phpunit/phpunit/src/Framework/TestCase.php (1154)  
#2 PHPUnit\Framework\TestCase->runTest                                                              
   /Users/alexandre/Sites/sitphp/commands/vendor/phpunit/phpunit/src/Framework/TestCase.php (842)   
#3 PHPUnit\Framework\TestCase->runBare                                                              
   /Users/alexandre/Sites/sitphp/commands/vendor/phpunit/phpunit/src/Framework/TestResult.php (693) 

Run command in verbose mode (--verbose) or debug mode (--debug) to see more stacktrace.

',$output);
    }
}
