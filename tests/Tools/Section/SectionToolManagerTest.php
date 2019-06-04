<?php

namespace SitPHP\Commands\Tests\Tools;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Section\SectionManager;
use SitPHP\Commands\Tools\Section\SectionTool;

class SectionToolManagerTest extends TestCase
{
    public function testMake()
    {
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return(new Request('my_command'));

        $section_manager = new SectionManager();
        $section = $section_manager->make($command);
        $this->assertInstanceOf(SectionTool::class, $section);
    }
}
