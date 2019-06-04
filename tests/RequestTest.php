<?php

namespace SitPHP\Commands\Tests;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use InvalidArgumentException;
use SitPHP\Commands\Input;
use SitPHP\Commands\Output;
use SitPHP\Commands\Request;
use stdClass;

class RequestTest extends TestCase
{

    public function testCreateFromGlobal()
    {
        $this->assertInstanceOf(Request::class, Request::createFromGlobal());
    }

    public function testCreateFromGlobalWithNoCommandName(){
        $_SERVER['argv'] = [];
        $request = Request::createFromGlobal();

        $this->assertEquals('list', $request->getCommandName());
    }

    public function testNewRequestWithInvalidParamsShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        new Request('my_command', [new stdClass()]);
    }

    public function testNewRequestWithInvalidInputShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        new Request('my_command', null, new stdClass());
    }

    public function testNewRequestWithInvalidOutputShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        new Request('my_command', null, null, new stdClass());
    }

    public function testNewRequestWithInvalidErrorOutputShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        new Request('my_command', null, null, null, new stdClass());
    }

    public function testGetCommandName()
    {
        $request = new Request('my_command');
        $this->assertEquals('my_command', $request->getCommandName());
    }

    public function testGetInput()
    {
        $request = new Request('my_command');
        $this->assertInstanceOf(Input::class,$request->getInput());

    }

    public function testGetOutput()
    {
        $request = new Request('my_command');
        $this->assertInstanceOf(Output::class,$request->getOutput());

    }

    public function testGetErrorOutput()
    {
        $request = new Request('my_command');
        $this->assertInstanceOf(Output::class,$request->getErrorOutput());
    }

    public function testGetArg()
    {
        $request = new Request('my_command' , ['arg']);
        $this->assertEquals('arg', $request->getArg(0));
    }

    public function testGetAllArgs()
    {
        $request = new Request('my_command' , ['arg']);
        $this->assertEquals([0 => 'arg'], $request->getAllArgs());
    }

    public function testGetOption()
    {
        $request = new Request('my_command' , ['--option']);
        $this->assertTrue($request->getOption('option'));
    }

    public function testGetOptionWithValue()
    {
        $request = new Request('my_command' , ['--option = value']);
        $this->assertEquals('value', $request->getOption('option'));
    }

    public function testGetAllOptions()
    {
        $request = new Request('my_command' , ['--option1','--option2 = option2']);
        $this->assertEquals(['option1' => true, 'option2' => 'option2'], $request->getAllOptions());
    }

    public function testGetFlag()
    {
        $request = new Request('my_command', ['-flag']);
        $this->assertTrue($request->getFlag('flag'));
    }

    public function testGetFlagWithValue()
    {
        $request = new Request('my_command', ['-flag = flag']);
        $this->assertEquals('flag', $request->getFlag('flag'));
    }

    public function testGetAllFlags()
    {
        $request = new Request('my_command', ['-flag1', '-flag2 = flag2']);
        $this->assertEquals(['flag1' => true, 'flag2' => 'flag2'], $request->getAllFlags('flag'));
    }

    public function testFormatOptionShouldEnableFormatting()
    {
        $request = new Request('my_command', ['--format']);
        $this->assertTrue($request->getOutput()->isFormattingActive());
    }

    public function testNoFormatOptionShouldDisableFormatting()
    {
        $request = new Request('my_command', ['--no-format']);
        $this->assertFalse($request->getOutput()->isFormattingActive());
    }

    public function testIsDebug()
    {
        $request = new Request('my_command', ['--debug']);
        $this->assertTrue($request->isDebug());
    }


    public function testIsVerbose()
    {
        $request = new Request('my_command', ['--verbose']);
        $this->assertTrue($request->isVerbose());
    }

    public function testIsQuiet()
    {
        $request = new Request('my_command', ['--quiet']);
        $this->assertTrue($request->isQuiet());
    }

    public function testIsSilent()
    {
        $request = new Request('my_command', ['--silent']);
        $this->assertTrue($request->isSilent());
    }

    public function testGetVerbosity()
    {
        $request = new Request('my_command', ['--silent']);
        $this->assertEquals(-2,$request->getVerbosity());
    }

    public function testIsInteractive()
    {
        $request = new Request('my_command');
        $this->assertTrue($request->isInteractive());
        $request = new Request('my_command', ['--no-interaction']);
        $this->assertFalse($request->isInteractive());
    }


    public function testChangeStty(){
        $input = Double::mock(Input::class)->getInstance('php://memory');
        $input::_method('isatty')->return(true);

        $request = new Request('my_command', null, $input);
        $this->assertTrue($request->changeStty('-echo'));
    }

    public function testChangeSttyWithoutTtyShouldReturnFalse()
    {
        /** @var DoubleStub & Input $input */
        $input = Double::mock(Input::class)->getInstance('php://memory');
        $input::_method('isatty')->return(false);
        /** @var DoubleStub & Request $request */
        $request = Double::mock(Request::class)->getInstance('my_command');
        $request::_method('getInput')->return($input);

        $this->assertFalse($request->changeStty('change'));
    }


    public function testRestoreStty(){
        $input = Double::mock(Input::class)->getInstance('php://memory');
        $input::_method('isatty')->return(true);

        $request = new Request('my_command', null, $input);
        $request->changeStty('-echo');
        $this->assertTrue($request->restoreStty());
    }
    public function testRestoreSttyShouldReturnTrueWhenSttyWasNotChanged(){
        $input = Double::mock(Input::class)->getInstance('php://memory');
        $input::_method('isatty')->return(true);

        $request = new Request('my_command', null, $input);
        $this->assertTrue($request->restoreStty());
    }

    public function testRestoreSttyWithoutTtyShouldReturnFalse()
    {
        /** @var DoubleStub & Input $input */
        $input = Double::mock(Input::class)->getInstance('php://memory');
        $input::_method('isatty')->return(false);
        /** @var DoubleStub & Request $request */
        $request = Double::mock(Request::class)->getInstance('my_command');
        $request::_method('getInput')->return($input);

        $this->assertFalse($request->restoreStty('change'));
    }

}
