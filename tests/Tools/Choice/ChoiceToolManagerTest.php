<?php

namespace SitPHP\Commands\Tests\Tools\Choice;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Choice\ChoiceStyle;
use SitPHP\Commands\Tools\Choice\ChoiceTool;
use SitPHP\Commands\Tools\Choice\ChoiceManager;

class ChoiceToolManagerTest extends TestCase
{

    public function testMake()
    {
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return(new Request('my_command'));
        $command::_method('getManager')->return(new CommandManager());

        $choice_manager = new ChoiceManager();
        $choice = $choice_manager->make($command);
        $this->assertInstanceOf(ChoiceTool::class, $choice);
    }

    public function testBuildStyle()
    {
        $choice_manager = new ChoiceManager(new CommandManager());
        $style = $choice_manager->buildStyle('my_style');

        $this->assertInstanceOf(ChoiceStyle::class, $style);
    }

    public function testGetStyle()
    {
        $choice_manager = new ChoiceManager(new CommandManager());
        $style = $choice_manager->buildStyle('my_style');

        $this->assertSame($style, $choice_manager->getStyle('my_style'));
    }

    public function testHasStyle()
    {
        $choice_manager = new ChoiceManager(new CommandManager());
        $choice_manager->buildStyle('my_style');

        $this->assertTrue($choice_manager->hasStyle('my_style'));
    }

    public function testRemoveStyle()
    {
        $choice_manager = new ChoiceManager(new CommandManager());
        $choice_manager->buildStyle('my_style');
        $choice_manager->removeStyle('my_style');

        $this->assertNull($choice_manager->getStyle('my_style'));
    }
}
