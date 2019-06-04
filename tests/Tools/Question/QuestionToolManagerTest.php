<?php

namespace SitPHP\Commands\Tests\Tools;

use Doublit\Doublit;
use Doublit\Lib\DoubleStub;
use Doublit\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Question\QuestionManager;
use SitPHP\Commands\Tools\Question\QuestionStyle;
use SitPHP\Commands\Tools\Question\QuestionTool;


class QuestionToolManagerTest extends TestCase
{

    public function testMake()
    {

        /** @var DoubleStub & Command $command */
        $command = Doublit::mock(Command::class)->getInstance();
        $command::_method('getRequest')->stub(new Request('my_command'));
        $command::_method('getManager')->stub(new CommandManager());

        $question_manager = new QuestionManager();
        $question = $question_manager->make($command);
        $this->assertInstanceOf(QuestionTool::class, $question);
    }

    public function testBuildStyle()
    {
        $question_manager = new QuestionManager();
        $style = $question_manager->buildStyle('my_style');

        $this->assertInstanceOf(QuestionStyle::class, $style);
    }

    public function testGetStyle()
    {
        $question_manager = new QuestionManager();
        $style = $question_manager->buildStyle('my_style');

        $this->assertSame($style, $question_manager->getStyle('my_style'));
    }

    public function testHasStyle()
    {
        $question_manager = new QuestionManager();
        $question_manager->buildStyle('my_style');

        $this->assertTrue($question_manager->hasStyle('my_style'));
    }

    public function testRemoveStyle()
    {
        $question_manager = new QuestionManager();
        $question_manager->buildStyle('my_style');
        $question_manager->removeStyle('my_style');

        $this->assertNull($question_manager->getStyle('my_style'));
    }
}
