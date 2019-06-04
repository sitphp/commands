<?php

namespace SitPHP\Commands\Tests\Helpers;

use Doubles\TestCase;
use SitPHP\Commands\Helpers\PhpHelper;

class PhpHelperTest extends TestCase
{
    /*
         * Test shell exec
         */
    function testIsCli()
    {
        $this->assertTrue(PhpHelper::isCli());
    }

    function testShellExec()
    {
        $response = PhpHelper::shellExec('echo shell', false, $status);
        $this->assertEquals(['shell'], $response);
        $this->assertEquals(0, $status);
    }
    function testShellExecShouldReturnNullWithUndefinedCommand(){
        $response = PhpHelper::shellExec('undefined', false, $status);
        $this->assertNull($response);
        $this->assertEquals(127, $status);
    }
    function testShellExecWithEscape(){

        $response = PhpHelper::shellExec('echo &shell', true);
        $this->assertEquals(['&shell'], $response);
    }
}
