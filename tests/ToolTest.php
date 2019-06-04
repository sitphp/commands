<?php

namespace SitPHP\Commands\Tests;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Input;
use SitPHP\Commands\Output;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tool;

class ToolTest extends TestCase
{

    public function makeTool(){
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');

        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setManager(new CommandManager());
        $command::_method('getRequest')->return($request);

        /** @var DoubleStub & Tool $tool */
        $tool = Double::mock(Tool::class)->getInstance($command);
        return $tool;
    }

    public function testGetCommand()
    {
        $tool = $this->makeTool();
        $this->assertInstanceOf(Command::class, $tool->getCommand());
    }

    public function testGetInput()
    {
        $tool = $this->makeTool();
        $this->assertInstanceOf(Input::class, $tool->getInput());
    }

    public function testGetOutput()
    {
        $tool = $this->makeTool();
        $this->assertInstanceOf(Output::class, $tool->getOutput());
    }

    public function testGetErrorOutput()
    {
        $tool = $this->makeTool();
        $this->assertInstanceOf(Output::class, $tool->getErrorOutput());
    }

    public function testUseErrorOutput()
    {
        $tool = $this->makeTool();
        $tool->useErrorOutput();

        $this->assertSame($tool->getOutput(), $tool->getErrorOutput());
    }

    public function testIsUsingErrorOutput()
    {
        $tool = $this->makeTool();
        $this->assertFalse($tool->isUsingErrorOutput());
        $tool->useErrorOutput();
        $this->assertTrue($tool->isUsingErrorOutput());
    }


    public function testUseStandardOutput()
    {
        $tool = $this->makeTool();
        $tool->useErrorOutput();
        $this->assertTrue($tool->isUsingErrorOutput());
        $tool->useStandardOutput();
        $this->assertFalse($tool->isUsingErrorOutput());
        $this->assertNotSame($tool->getOutput(), $tool->getErrorOutput());
    }

    public function testTool()
    {
        $tool = $this->makeTool();
        /** @var Tool $section */
        $section = $tool->tool('section');

        $this->assertInstanceOf(Tool::class, $section);
        $this->assertSame($tool->getInput(), $section->getInput());
        $this->assertSame($tool->getOutput(), $section->getOutput());
        $this->assertSame($tool->getErrorOutput(), $section->getErrorOutput());
    }
}
