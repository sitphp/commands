<?php

namespace SitPHP\Commands\Tests\Tools;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use LogicException;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Section\SectionManager;
use SitPHP\Commands\Tools\Section\SectionTool;

class SectionToolTest extends TestCase
{

    public function makeSection(){

        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        $section = new SectionTool($command, new SectionManager());

        return $section;
    }

    public function testWrite()
    {
        $section = $this->makeSection();
        $section->placeHere();
        $section->write('message1');
        $section->write('message2');

        $this->assertEquals('message1message2', implode('',$section->getOutput()->getBuffer()));
    }

    public function testWriteShouldFailWhenSectionIsNotPlaced(){
        $this->expectException(LogicException::class);
        $section = $this->makeSection();
        $section->write('');
    }

    public function testWriteLn()
    {
        $section = $this->makeSection();
        $section->placeHere();
        $section->writeLn('message');

        $this->assertEquals('message'."\n", implode('',$section->getOutput()->getBuffer()));
    }

    public function testOverwrite()
    {
        $section = $this->makeSection();
        $section->placeHere();
        $section->write('write');
        $section->overwrite('overwrite');

        $this->assertEquals('overwrite', implode('',$section->getOutput()->getBuffer()));
    }

    public function testOverwriteShouldFailWhenSectionIsNotPlaced(){
        $this->expectException(LogicException::class);
        $section = $this->makeSection();
        $section->overwrite('');
    }

    public function testOverwriteLn()
    {
        $section = $this->makeSection();
        $section->placeHere();
        $section->write('write');
        $section->overwriteLn('overwrite');

        $this->assertEquals('overwrite'."\n", implode('',$section->getOutput()->getBuffer()));
    }

    public function testPrepend()
    {
        $section = $this->makeSection();
        $section->placeHere();
        $section->write('write');
        $section->prepend('prepend');

        $this->assertEquals('prependwrite', implode('',$section->getOutput()->getBuffer()));
    }

    public function testPrependShouldFailWhenSectionIsNotPlaced(){
        $this->expectException(LogicException::class);
        $section = $this->makeSection();
        $section->prepend('');
    }

    public function testPrependLn()
    {
        $section = $this->makeSection();
        $section->placeHere();
        $section->write('write');
        $section->prependLn('prepend');

        $this->assertEquals('prepend'."\n".'write', implode('',$section->getOutput()->getBuffer()));
    }

    public function testClear()
    {
        $section = $this->makeSection();
        $section->placeHere();
        $section->write('write');
        $section->clear();

        $this->assertEquals('', implode('',$section->getOutput()->getBuffer()));
    }

    public function testClearShouldFailWhenSectionIsNotPlaced(){
        $this->expectException(LogicException::class);
        $section = $this->makeSection();
        $section->clear();
    }

    public function testIsPlaced()
    {
        $section = $this->makeSection();
        $this->assertFalse($section->isPlaced());
        $section->placeHere();
        $this->assertTrue($section->isPlaced());
    }

    public function testGetBufferSplit()
    {
        $section = $this->makeSection();
        $section->getOutput()->write('before');
        $section->placeHere()->write('section');
        $section->getOutput()->write('after');

        $this->assertEquals(['before' => ['before'], 'content' => 'section', 'after' => ['after']], $section->getBufferSplit());
    }

    public function testMoveCursorToStartPosition()
    {
        $section = $this->makeSection();
        $section->getOutput()->write('output');
        $section->placeHere();
        $section->write('section');
        $section->moveCursorToStartPosition();

        $this->assertEquals(['line' => 1, 'column' => 6], $section->getOutput()->getCursorPosition());
    }


    public function testMoveCursorToTipPosition()
    {
        $section = $this->makeSection();
        $section->getOutput()->write('output');
        $section->placeHere();
        $section->write('section');
        $section->getOutput()->write('output');
        $section->moveCursorToTipPosition();
        $this->assertEquals(['line' => 1, 'column' => 13], $section->getOutput()->getCursorPosition());
    }

}
