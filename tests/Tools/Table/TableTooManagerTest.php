<?php

namespace SitPHP\Commands\Tests\Tools;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Table\Line;
use SitPHP\Commands\Tools\Table\TableManager;
use SitPHP\Commands\Tools\Table\TableStyle;
use SitPHP\Commands\Tools\Table\TableTool;

class TableTooManagerTest extends TestCase
{

    public function testMake()
    {
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return(new Request('my_command'));

        $table_manager = new TableManager();
        $table = $table_manager->make($command);
        $this->assertInstanceOf(TableTool::class, $table);
    }

    public function testBuildStyle()
    {
        $table_manager = new TableManager();
        $style = $table_manager->buildStyle('my_style');

        $this->assertInstanceOf(TableStyle::class, $style);
    }

    public function testGetStyle()
    {
        $table_manager = new TableManager();
        $style = $table_manager->buildStyle('my_style');

        $this->assertSame($style, $table_manager->getStyle('my_style'));
    }

    public function testHasStyle()
    {
        $table_manager = new TableManager();
        $table_manager->buildStyle('my_style');

        $this->assertTrue($table_manager->hasStyle('my_style'));
    }

    public function testRemoveStyle()
    {
        $table_manager = new TableManager();
        $table_manager->buildStyle('my_style');
        $table_manager->removeStyle('my_style');

        $this->assertNull($table_manager->getStyle('my_style'));
    }


    public function testBuildLine()
    {
        $table_manager = new TableManager();
        $line_break = $table_manager->buildLine('my_linebreak');

        $this->assertInstanceOf(Line::class, $line_break);
    }

    public function testGetLineBreak()
    {
        $table_manager = new TableManager();
        $table_manager->buildLine('my_linebreak');

        $this->assertInstanceOf(Line::class, $table_manager->getLine('my_linebreak'));
    }

    public function testHasLineBreak()
    {
        $table_manager = new TableManager();
        $table_manager->buildLine('my_linebreak');

        $this->assertTrue($table_manager->hasLine('my_linebreak'));
    }

    public function testRemoveLineBreak()
    {
        $table_manager = new TableManager();
        $table_manager->buildLine('my_linebreak');
        $table_manager->removeLine('my_linebreak');

        $this->assertNull($table_manager->getLine('my_linebreak'));
    }
}
