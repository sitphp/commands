<?php

namespace SitPHP\Commands\Tests\Tools;

use Doubles\TestCase;
use SitPHP\Commands\Tools\Table\Line;

class TableLineTest extends TestCase
{

    public function testGetSetTitle()
    {
        $line_break = new Line();
        $line_break->setTitle('title');
        $this->assertEquals('title', $line_break->getTitle());
    }

    public function testGetSetLineChar()
    {
        $line_break = new Line();
        $line_break->setLineChar('>');
        $this->assertEquals('>', $line_break->getLineChar());
    }

    public function testGetSetSeparationChar()
    {
        $line_break = new Line();
        $line_break->setSeparationChar('>');
        $this->assertEquals('>', $line_break->getSeparationChar());
    }

    public function testGetSetRightBorderChar()
    {
        $line_break = new Line();
        $this->assertNull($line_break->getRightBorderChar());
        $line_break->setRightBorderChar('>');
        $this->assertEquals('>', $line_break->getRightBorderChar());
    }

    public function testGetSetLeftBorderChar()
    {
        $line_break = new Line();
        $this->assertNull($line_break->getLeftBorderChar());
        $line_break->setLeftBorderChar('>');
        $this->assertEquals('>', $line_break->getLeftBorderChar());
    }
}
