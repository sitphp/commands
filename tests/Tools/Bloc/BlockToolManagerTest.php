<?php

namespace SitPHP\Commands\Tests\Tools\Bloc;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Bloc\BlocStyle;
use SitPHP\Commands\Tools\Bloc\BlocTool;
use SitPHP\Commands\Tools\Bloc\BlocManager;

class BlocToolManagerTest extends TestCase
{

    public function testMake()
    {
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return(new Request('my_command'));

        $bloc_manager = new BlocManager();
        $bloc = $bloc_manager->make($command);
        $this->assertInstanceOf(BlocTool::class, $bloc);
    }

    public function testBuildStyle()
    {
        $bloc_manager = new BlocManager();
        $style = $bloc_manager->buildStyle('my_style');

        $this->assertInstanceOf(BlocStyle::class, $style);
    }

    public function testGetStyle()
    {
        $bloc_manager = new BlocManager();
        $style = $bloc_manager->buildStyle('my_style');

        $this->assertSame($style, $bloc_manager->getStyle('my_style'));
    }

    public function testHasStyle()
    {
        $bloc_manager = new BlocManager();
        $bloc_manager->buildStyle('my_style');

        $this->assertTrue($bloc_manager->hasStyle('my_style'));
    }

    public function testRemoveStyle()
    {
        $bloc_manager = new BlocManager();
        $bloc_manager->buildStyle('my_style');
        $bloc_manager->removeStyle('my_style');

        $this->assertNull($bloc_manager->getStyle('my_style'));
    }

}
