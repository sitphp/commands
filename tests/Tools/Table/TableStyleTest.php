<?php

namespace SitPHP\Commands\Tests\Tools;

use Doublit\TestCase;
use InvalidArgumentException;
use SitPHP\Commands\Tools\Table\TableStyle;

class TableStyleTest extends TestCase
{


    public function testSetCornerChars()
    {
        $style = new TableStyle();
        $style->setCornerChars('>', '>', '>', '>');

        $this->assertEquals('>', $style->getTopLeftCornerChar());
        $this->assertEquals('>', $style->getTopRightCornerChar());
        $this->assertEquals('>', $style->getBottomLeftCornerChar());
        $this->assertEquals('>', $style->getBottomRightCornerChar());
    }

    public function testGetSetTopLeftCornerChar()
    {
        $style = new TableStyle();
        $style->setTopLeftCornerChar('>');
        $this->assertEquals('>', $style->getTopLeftCornerChar());
    }

    public function testGetSetTopRightCornerChar()
    {
        $style = new TableStyle();
        $style->setTopRightCornerChar('>');
        $this->assertEquals('>', $style->getTopRightCornerChar());
    }

    public function testGetSetBottomLeftCornerChar()
    {
        $style = new TableStyle();
        $style->setBottomLeftCornerChar('>');
        $this->assertEquals('>', $style->getBottomLeftCornerChar());
    }

    public function testGetSetBottomRightCornerChar()
    {
        $style = new TableStyle();
        $style->setBottomRightCornerChar('>');
        $this->assertEquals('>', $style->getBottomRightCornerChar());
    }

    public function testSetPadding()
    {
        $style = new TableStyle();
        $style->setPadding(3);
        $this->assertEquals(3, $style->getPadding());
    }

    public function testSetNegativePaddingShouldFail(){
        $this->expectException(InvalidArgumentException::class);

        $style = new TableStyle();
        $style->setPadding(-1);
    }

    public function testGetSetPaddingChar()
    {
        $style = new TableStyle();
        $style->setPaddingChar('>');
        $this->assertEquals('>', $style->getPaddingChar());
    }

    public function testSetTopBorderLineChar()
    {
        $style = new TableStyle();
        $style->setTopBorderLineChar('>');
        $this->assertEquals('>', $style->getTopBorderLineChar());
    }

    public function testSetBottomBorderLineChar()
    {
        $style = new TableStyle();
        $style->setBottomBorderLineChar('>');
        $this->assertEquals('>', $style->getBottomBorderLineChar());
    }

    public function testSetRightBorderLineChar()
    {
        $style = new TableStyle();
        $style->setRightBorderLineChar('>');
        $this->assertEquals('>', $style->getRightBorderLineChar());
    }

    public function testSetLeftBorderLineChar()
    {
        $style = new TableStyle();
        $style->setLeftBorderLineChar('>');
        $this->assertEquals('>', $style->getLeftBorderLineChar());
    }

    public function testSetTopBorderSeparationChar()
    {
        $style = new TableStyle();
        $style->setTopBorderSeparationChar('>');
        $this->assertEquals('>', $style->getTopBorderSeparationChar());
    }

    public function testSetBottomBorderSeparationChar()
    {
        $style = new TableStyle();
        $style->setBottomBorderSeparationChar('>');
        $this->assertEquals('>', $style->getBottomBorderSeparationChar());
    }

    public function testSetLeftBorderSeparationChar()
    {
        $style = new TableStyle();
        $style->setLeftBorderSeparationChar('>');
        $this->assertEquals('>', $style->getLeftBorderSeparationChar());
    }

    public function testSetRightBorderSeparationChar()
    {
        $style = new TableStyle();
        $style->setRightBorderSeparationChar('>');
        $this->assertEquals('>', $style->getRightBorderSeparationChar());
    }


    public function testGetSetTopBorderChars()
    {
        $style = new TableStyle();
        $style->setTopBorderChars('>', '<');
        $this->assertEquals('>', $style->getTopBorderLineChar());
        $this->assertEquals('<', $style->getTopBorderSeparationChar());
    }

    public function testGetSetBottomBorderChars()
    {
        $style = new TableStyle();
        $style->setBottomBorderChars('>', '<');
        $this->assertEquals('>', $style->getBottomBorderLineChar());
        $this->assertEquals('<', $style->getBottomBorderSeparationChar());
    }

    public function testGetSetLeftBorderChars()
    {
        $style = new TableStyle();
        $style->setLeftBorderChars('>', '<');
        $this->assertEquals('>', $style->getLeftBorderLineChar());
        $this->assertEquals('<', $style->getLeftBorderSeparationChar());
    }

    public function testGetSetRightBorderChars()
    {
        $style = new TableStyle();
        $style->setRightBorderChars('>', '<');
        $this->assertEquals('>', $style->getRightBorderLineChar());
        $this->assertEquals('<', $style->getRightBorderSeparationChar());
    }


    public function testClearBorderTop()
    {
        $style = new TableStyle();
        $style->clearBorderTop();
        $this->assertEquals('', $style->getTopBorderLineChar());
        $this->assertEquals('', $style->getTopBorderSeparationChar());
        $this->assertEquals('', $style->getTopLeftCornerChar());
        $this->assertEquals('', $style->getTopRightCornerChar());
    }

    public function testClearBorderBottom()
    {
        $style = new TableStyle();
        $style->clearBorderBottom();
        $this->assertEquals('', $style->getBottomBorderLineChar());
        $this->assertEquals('', $style->getBottomBorderSeparationChar());
        $this->assertEquals('', $style->getBottomLeftCornerChar());
        $this->assertEquals('', $style->getBottomRightCornerChar());
    }

    public function testClearBorderLeft()
    {
        $style = new TableStyle();
        $style->clearBorderLeft();
        $this->assertEquals('', $style->getLeftBorderLineChar());
        $this->assertEquals('', $style->getLeftBorderSeparationChar());
        $this->assertEquals('', $style->getTopLeftCornerChar());
        $this->assertEquals('', $style->getBottomLeftCornerChar());
    }

    public function testClearBorderRight()
    {
        $style = new TableStyle();
        $style->clearBorderRight();
        $this->assertEquals('', $style->getRightBorderLineChar());
        $this->assertEquals('', $style->getRightBorderSeparationChar());
        $this->assertEquals('', $style->getTopRightCornerChar());
        $this->assertEquals('', $style->getBottomRightCornerChar());
    }

    public function testSetLineBreakChars()
    {
        $style = new TableStyle();
        $style->setLineChars('>', '<');
        $this->assertEquals('<', $style->getLineBreaksSeparationChar());
        $this->assertEquals('>', $style->getLineBreaksLineChar());
    }

    public function testGetSetLineBreakLineChar()
    {
        $style = new TableStyle();
        $style->setLineBreaksLineChar('>');
        $this->assertEquals('>', $style->getLineBreaksLineChar());
    }

    public function testGetSetLineBreakSeparationChar()
    {
        $style = new TableStyle();
        $style->setLineBreaksSeparationChar('>');
        $this->assertEquals('>', $style->getLineBreaksSeparationChar());
    }

    public function testClearLineBreaks()
    {
        $style = new TableStyle();
        $style->clearLineBreaks();
        $this->assertEquals('', $style->getLineBreaksLineChar());
        $this->assertEquals('', $style->getLineBreaksSeparationChar());
    }

    public function testGetSetCellSeparationChar()
    {
        $style = new TableStyle();
        $style->setCellSeparationChar('>');
        $this->assertEquals('>', $style->getCellSeparationChar());
    }
}
