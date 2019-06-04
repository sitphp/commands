<?php

namespace App\Lib\Commandit\Tests;

use Doublit\Doublit;
use Doublit\TestCase;
use SitPHP\Commands\Input;

class InputTest extends TestCase
{


    /*
     * Test verbosity
     */
    function testVerbosity(){
        $input = new Input('php://memory');
        $input->setVerbosity(2);
        $this->assertEquals(2, $input->getVerbosity());
    }


    /*
     * Test get path
     */
    function testGetPath(){
        $input = new Input('php://memory');
        $this->assertEquals('php://memory', $input->getPath());
    }

    /*
     * Test get mode
     */
    function testGetMode(){
        $input = new Input('php://memory');
        $this->assertEquals('r+', $input->getMode());
    }

    /*
     * Test get handle
     */
    function testGetHandle(){
        $input = new Input('php://memory');
        $this->assertIsResource($input->getHandle());
    }

    /*
     * Test end of file
     */
    function testIsEndOfFile(){
        $input = new Input('php://memory');
        fwrite($input->getHandle(),'message');
        $this->assertFalse($input->isEndOfFile());
        fgetc($input->getHandle());
        $this->assertTrue($input->isEndOfFile());
    }

    /*
         * Test read char
         */
    function testReadChar(){
        $input = Doublit::mock(Input::class)->getInstance('php://memory');
        $input::_method('isatty')->stub(true);


        fwrite($input->getHandle(), 'write more than 6 chars');
        rewind($input->getHandle());

        $this->assertEquals('write ',$input->readChar());

    }
    function testReadCharWithInvalidInputShouldFail(){
        $this->expectException(\RuntimeException::class);
        $input = new Input('php://memory');
        $input->readChar();
    }

    function testReadByte(){
        $input = new Input('php://memory');
        fwrite($input->getHandle(), 'message');
        rewind($input->getHandle());
        $this->assertEquals('m', $input->readByte());
    }

    function testRead(){
        $input = new Input('php://memory');
        fwrite($input->getHandle(), 'message');
        rewind($input->getHandle());
        $this->assertEquals('me', $input->read(2));
    }

    function testReadLine(){
        $input = new Input('php://memory');
        fwrite($input->getHandle(), 'message');
        rewind($input->getHandle());

        $this->assertEquals('message', $input->readLine());
    }

    function testIsCharDevice(){
        $input = new Input('php://memory');
        $this->assertIsBool($input->isChar());
    }

    function testIsFile(){
        $input = new Input('php://memory');
        $this->assertIsBool($input->isFile());
    }

    function testIsPipe(){
        $input = new Input('php://memory');
        $this->assertIsBool($input->isPipe());
    }

    function testIsatty(){
        $input = new Input('php://memory');
        $this->assertIsBool($input->isatty());
    }

    function testGetType(){
        $input = new Input('php://memory');
        $this->assertEquals('file', $input->getType());
    }

    function testClose(){
        $input = new Input('php://memory');
        $this->assertTrue($input->close());
    }
}
