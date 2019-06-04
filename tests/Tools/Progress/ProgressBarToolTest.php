<?php

namespace SitPHP\Commands\Tests\Tools;

use Doublit\Doublit;
use Doublit\Lib\DoubleStub;
use Doublit\TestCase;
use InvalidArgumentException;
use LogicException;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarManager;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarStyle;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarTool;


class ProgressBarToolTest extends TestCase
{

    public function makeProgressBar()
    {
        /** @var DoubleStub & Command $command */
        $command = Doublit::mock(Command::class)->getInstance();
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');
        $command::_method('getRequest')->stub($request);
        $command->setManager(new CommandManager());

        $progress_bar = new ProgressBarTool($command, new ProgressBarManager(), 10);

        return $progress_bar;
    }

    public function makeProgressBarWithStyle(){
        $request = new Request('my_command');
        /** @var DoubleStub & Command $command */
        $command = Doublit::mock(Command::class)->getInstance();
        $command::_method('getRequest')->stub($request);
        $command->setManager(new CommandManager());

        /** @var ProgressBarStyle & DoubleStub $style */
        $style = Doublit::mock(ProgressBarStyle::class)->getInstance();

        /** @var ProgressBarTool & DoubleStub $progress_bar */
        $progress_bar = Doublit::mock(ProgressBarTool::class)->getInstance($command, new ProgressBarManager(), 10);
        $progress_bar::_method('getStyle')->stub($style);

        return [$progress_bar, $style];
    }

    public function testGetSteps()
    {
        $progress_bar = $this->makeProgressBar();

        $this->assertEquals(10, $progress_bar->getSteps());
    }

    public function testGetProgress()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->getProgress();
        $this->assertEquals(0, $progress_bar->getProgress());
        $progress_bar->display();
        $this->assertEquals(0, $progress_bar->getProgress());
        $progress_bar->progress();
        $this->assertEquals(1, $progress_bar->getProgress());
        $progress_bar->regress();
        $this->assertEquals(0, $progress_bar->getProgress());
        $progress_bar->finish();
        $this->assertEquals(10, $progress_bar->getProgress());
    }

    public function testGetElapsedTime()
    {
        $progress_bar = $this->makeProgressBar();
        $this->assertEquals('0ms', $progress_bar->getElapsedTime());
        $progress_bar->display();
        $progress_bar->finish();
        $this->assertTrue($progress_bar->getElapsedTime(true) > 0);
    }

    public function testGetStartTime()
    {
        $progress_bar = $this->makeProgressBar();
        $this->assertNull($progress_bar->getStartTime());
        $this->assertNull($progress_bar->getStopTime());
        $progress_bar->display();
        $progress_bar->finish();

        $this->assertIsFloat($progress_bar->getStartTime());
        $this->assertIsFloat($progress_bar->getStopTime());
    }

    public function testIsFinished()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display();
        $progress_bar->progress();
        $this->assertFalse($progress_bar->isFinished());
        $progress_bar->finish();
        $this->assertTrue($progress_bar->isFinished());
    }

    public function testIsAutoFinishActive()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->disableAutoFinish();
        $this->assertFalse($progress_bar->isAutoFinishActive());
        $progress_bar->enableAutoFinish();
        $this->assertTrue($progress_bar->isAutoFinishActive());
    }

    public function testAutoFinish()
    {
        $progress_bar1 = $this->makeProgressBar();
        $progress_bar1->enableAutoFinish();
        $progress_bar1->display();
        for ($i = 1; $i <= 10; $i++) {
            $progress_bar1->progress();
        }
        $this->assertTrue($progress_bar1->isFinished());

        $progress_bar2 = $this->makeProgressBar();
        $progress_bar2->disableAutoFinish();
        $progress_bar2->display();
        for ($i = 1; $i <= 10; $i++) {
            $progress_bar2->progress();
        }
        $this->assertFalse($progress_bar2->isFinished());
    }

    public function testFinish()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display();
        $progress_bar->finish();

        $this->assertEquals(10, $progress_bar->getProgress());
        $this->assertTrue($progress_bar->isFinished());
    }

    public function testFinishShouldFailWhenProgressBarIsNotDisplayed()
    {
        $this->expectException(LogicException::class);
        $progress_bar = $this->makeProgressBar();
        $progress_bar->finish();
    }

    public function testProgress()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display();
        $progress_bar->progress();
        $this->assertEquals(1, $progress_bar->getProgress());
    }

    public function testProgressShouldNotWorkWhenFinished()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display();
        $progress_bar->finish();
        $progress_bar->progress();

        $this->assertEquals(10, $progress_bar->getProgress());
    }

    public function testProgressShouldFailWhenProgressBarIsNotDisplayed()
    {
        $this->expectException(LogicException::class);
        $progress_bar = $this->makeProgressBar();
        $progress_bar->progress();
    }

    public function testRegress()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display();
        $progress_bar->progress();
        $progress_bar->regress();
        $this->assertEquals(0, $progress_bar->getProgress());
        $progress_bar->regress();
        $this->assertEquals(0, $progress_bar->getProgress());
    }

    public function testRegressShouldNotWorkWhenFinished()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display();
        $progress_bar->finish();
        $progress_bar->regress();

        $this->assertEquals(10, $progress_bar->getProgress());
    }

    public function testRegressShouldFailWhenProgressBarIsNotDisplayed()
    {
        $this->expectException(LogicException::class);
        $progress_bar = $this->makeProgressBar();
        $progress_bar->regress();
    }

    public function testJump()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display();
        $progress_bar->jump(3);

        $this->assertEquals(3, $progress_bar->getProgress());

        $progress_bar->jump(60);
        $this->assertEquals(10, $progress_bar->getProgress());
        $this->assertTrue($progress_bar->isFinished());
    }

    public function testJumpShouldNotFinishWhenAutoFinishIsDisabled()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar
            ->disableAutoFinish()
            ->display();
        $progress_bar->jump(60);

        $this->assertFalse($progress_bar->isFinished());
    }

    public function testJumpShouldFailWhenProgressBarIsNotDisplayed()
    {
        $this->expectException(LogicException::class);
        $progress_bar = $this->makeProgressBar();
        $progress_bar->jump(3);
    }

    public function testJumpShouldNotWorkWhenProgressBarIsFinished()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display();
        $progress_bar->finish();
        $progress_bar->jump(60);
        $this->assertEquals(10, $progress_bar->getProgress());
    }

    public function testDive()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display();
        $progress_bar->jump(5);
        $progress_bar->dive(3);

        $this->assertEquals(2, $progress_bar->getProgress());

        $progress_bar->dive(60);

        $this->assertEquals(0, $progress_bar->getProgress());
    }

    public function testDiveShouldNotWorkWhenProgressBarIsFinished(){
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display()
            ->finish();

        $progress_bar->dive(60);
        $this->assertEquals(10, $progress_bar->getProgress());
    }

    public function testDiveShouldFailWhenProgressBarIsNotDisplayed()
    {
        $this->expectException(LogicException::class);
        $progress_bar = $this->makeProgressBar();
        $progress_bar->dive(3);
    }

    public function testGetMessage()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display('display');
        $this->assertEquals('display', $progress_bar->getMessage());
        $progress_bar->progress('progress');
        $this->assertEquals('progress', $progress_bar->getMessage());
        $progress_bar->regress('regress');
        $this->assertEquals('regress', $progress_bar->getMessage());
        $progress_bar->jump(1, 'jump');
        $this->assertEquals('jump', $progress_bar->getMessage());
        $progress_bar->dive(1, 'dive');
        $this->assertEquals('dive', $progress_bar->getMessage());
    }

    public function testDisplay()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar
            ->setFormat('[%bar%] [%steps%] (%percents%) %message%')
            ->setProgressChar('=')
            ->setIndicatorChar('>')
            ->setSpaceChar(' ')
            ->setWidth(30)
            ->display('display');

        $output = $progress_bar->getOutput();
        rewind($output->getHandle());
        $this->assertEquals('[                              ] [0/10] (0%) display
', stream_get_contents($output->getHandle()));

        $progress_bar->progress('progress');
        rewind($output->getHandle());
        $this->assertEquals('[                              ] [0/10] (0%) display
[===>                          ] [1/10] (10%) progress
', stream_get_contents($output->getHandle()));

        $progress_bar->progress('step');
        rewind($output->getHandle());
        $this->assertEquals('[                              ] [0/10] (0%) display
[===>                          ] [1/10] (10%) progress
[======>                       ] [2/10] (20%) step
', stream_get_contents($output->getHandle()));

    }

    public function testPlacedDisplay()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->placeHere()
            ->display()
            ->progress();

        $this->assertEquals('1/10 [===...........................] (10%) 
', implode('', $progress_bar->getOutput()->getBuffer()));
    }

    public function testDisplayShouldNotWorkTwice(){
        $progress_bar = $this->makeProgressBar();
        $progress_bar->display()
            ->display();
        $output = $progress_bar->getOutput();
        rewind($output->getHandle());
        $this->assertEquals('0/10 [..............................] (0%) 
',stream_get_contents($output->getHandle()));
    }

    public function testIsPlaced()
    {
        $progress_bar = $this->makeProgressBar();
        $progress_bar->placeHere();
        $this->assertTrue($progress_bar->isPlaced());
    }

    public function testSetStyleWithUndefinedStyleShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $progress_bar = $this->makeProgressBar();
        $progress_bar->setStyle('undefined');
    }

    /*
     * Test style
     */
    public function testGetSetWidth()
    {
        /**
         * @var ProgressBarTool & DoubleStub $progress_bar
         * @var ProgressBarStyle & DoubleStub $style
         */
        list($progress_bar, $style) = $this->makeProgressBarWithStyle();
        $style::_method('setWidth')->count(1)->args([50]);
        $style::_method('getWidth')->count(1);
        $progress_bar->setWidth(50);
        $this->assertEquals(50, $progress_bar->getWidth());
    }

    public function testGetSetSpaceChar()
    {
        /**
         * @var ProgressBarTool & DoubleStub $progress_bar
         * @var ProgressBarStyle & DoubleStub $style
         */
        list($progress_bar, $style) = $this->makeProgressBarWithStyle();
        $style::_method('setSpaceChar')->count(1)->args(['>']);
        $style::_method('getSpaceChar')->count(1);
        $progress_bar->setSpaceChar('>');
        $this->assertEquals('>', $progress_bar->getSpaceChar());
    }

    public function testGetSetIndicatorChar()
    {
        /**
         * @var ProgressBarTool & DoubleStub $progress_bar
         * @var ProgressBarStyle & DoubleStub $style
         */
        list($progress_bar, $style) = $this->makeProgressBarWithStyle();
        $style::_method('setIndicatorChar')->count(1)->args(['>']);
        $style::_method('getIndicatorChar')->count(1);
        $progress_bar->setIndicatorChar('>');
        $this->assertEquals('>', $progress_bar->getIndicatorChar());
    }

    public function testGetSetProgressChar()
    {
        /**
         * @var ProgressBarTool & DoubleStub $progress_bar
         * @var ProgressBarStyle & DoubleStub $style
         */
        list($progress_bar, $style) = $this->makeProgressBarWithStyle();
        $style::_method('setProgressChar')->count(1)->args(['>']);
        $style::_method('getProgressChar')->count(1);
        $progress_bar->setProgressChar('>');
        $this->assertEquals('>', $progress_bar->getProgressChar());
    }

    public function testGetSetFormat()
    {
        /**
         * @var ProgressBarTool & DoubleStub $progress_bar
         * @var ProgressBarStyle & DoubleStub $style
         */
        list($progress_bar, $style) = $this->makeProgressBarWithStyle();
        $style::_method('setFormat')->count(1)->args(['format']);
        $style::_method('getFormat')->count(1);
        $progress_bar->setFormat('format');
        $this->assertEquals('format', $progress_bar->getFormat());
    }
}
