<?php

namespace SitPHP\Commands\Tests\Tools;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarManager;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarStyle;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarTool;

class ProgressBarManagerTest extends TestCase
{

    public function testMake()
    {
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return(new Request('my_command'));
        $command::_method('getManager')->return(new CommandManager());

        $progress_bar_manager = new ProgressBarManager();
        $progress_bar = $progress_bar_manager->make($command, 5);
        $this->assertInstanceOf(ProgressBarTool::class, $progress_bar);
    }

    public function testBuildStyle()
    {
        $progress_manager = new ProgressBarManager();
        $style = $progress_manager->buildStyle('my_style');

        $this->assertInstanceOf(ProgressBarStyle::class, $style);
    }

    public function testGetStyle()
    {
        $progress_manager = new ProgressBarManager();
        $style = $progress_manager->buildStyle('my_style');

        $this->assertSame($style, $progress_manager->getStyle('my_style'));
    }

    public function testHasStyle()
    {
        $progress_manager = new ProgressBarManager();
        $progress_manager->buildStyle('my_style');

        $this->assertTrue($progress_manager->hasStyle('my_style'));
    }

    public function testRemoveStyle()
    {
        $progress_manager = new ProgressBarManager();
        $progress_manager->buildStyle('my_style');
        $progress_manager->removeStyle('my_style');

        $this->assertNull($progress_manager->getStyle('my_style'));
    }

    public function testGetSetPlaceholderCallback()
    {
        $progress_manager = new ProgressBarManager();
        $callback = function(){};
        $progress_manager->setPlaceholderCallback('placeholder', $callback);

        $this->assertSame($callback, $progress_manager->getPlaceholderCallback('placeholder'));
    }


    public function testHasPlaceholderCallback()
    {
        $progress_manager = new ProgressBarManager();
        $callback = function(){};

        $this->assertFalse($progress_manager->hasPlaceholderCallback('placeholder'));
        $progress_manager->setPlaceholderCallback('placeholder', $callback);
        $this->assertTrue($progress_manager->hasPlaceholderCallback('placeholder'));
    }

    public function testGetAllPlaceholdersCallbacks()
    {
        $progress_manager = new ProgressBarManager();
        $callback = function(){};
        $progress_manager->setPlaceholderCallback('placeholder', $callback);
        $this->assertEquals(['bar', 'steps', 'message', 'percents', 'elapsed', 'memory', 'placeholder'],array_keys($progress_manager->getAllPlaceholderCallbacks('placeholder')));
    }
}
