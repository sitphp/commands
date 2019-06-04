<?php

namespace SitPHP\Commands\Tests\Tools;

use Doublit\TestCase;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarStyle;

class ProgressBarStyleTest extends TestCase
{

    public function testGetName()
    {
        $style = new ProgressBarStyle('name');
        $this->assertEquals('name', $style->getName());
    }
    public function testGetSetFormat()
    {
        $style = new ProgressBarStyle();
        $style->setFormat('format');
        $this->assertEquals('format', $style->getFormat());
    }

    public function testGetSetWidth()
    {
        $style = new ProgressBarStyle();
        $style->setWidth(4);
        $this->assertEquals(4, $style->getWidth());
    }

    public function testGetSetSpaceChar()
    {
        $style = new ProgressBarStyle();
        $style->setSpaceChar('c');
        $this->assertEquals('c', $style->getSpaceChar());
    }

    public function testGetIndicatorChar()
    {
        $style = new ProgressBarStyle();
        $style->setIndicatorChar('c');
        $this->assertEquals('c', $style->getIndicatorChar());
    }

    public function testGetSetProgressChar()
    {
        $style = new ProgressBarStyle();
        $style->setProgressChar('c');
        $this->assertEquals('c', $style->getProgressChar());
    }

}
