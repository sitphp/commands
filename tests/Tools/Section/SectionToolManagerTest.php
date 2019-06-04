<?php

namespace SitPHP\Commands\Tests\Tools;

use Doublit\Doublit;
use Doublit\Lib\DoubleStub;
use Doublit\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Section\SectionManager;
use SitPHP\Commands\Tools\Section\SectionTool;

class SectionToolManagerTest extends TestCase
{
    public function testMake()
    {
        /** @var DoubleStub & Command $command */
        $command = Doublit::mock(Command::class)->getInstance();
        $command::_method('getRequest')->stub(new Request('my_command'));

        $section_manager = new SectionManager();
        $section = $section_manager->make($command);
        $this->assertInstanceOf(SectionTool::class, $section);
    }
}
