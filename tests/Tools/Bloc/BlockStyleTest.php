<?php

namespace SitPHP\Commands\Tests\Tools\Bloc;

use Doublit\TestCase;
use InvalidArgumentException;
use SitPHP\Commands\Tools\Bloc\BlocStyle;

class BlocStyleTest extends TestCase
{

    public function testGetName()
    {
        $style = new BlocStyle('name');
        $this->assertEquals('name', $style->getName());
    }

    public function testGetSetColor()
    {
        $style = new BlocStyle();
        $style->setColor('blue');
        $this->assertEquals('blue', $style->getColor());
    }

    public function testGetSetBackgroundColor()
    {
        $style = new BlocStyle();
        $style->setBackgroundColor('blue');
        $this->assertEquals('blue', $style->getBackgroundColor());
    }

    public function testGetSetWidth()
    {
        $style = new BlocStyle();
        $style->setWidth(30);
        $this->assertEquals(30, $style->getWidth());
    }

    public function testSetBorderTop()
    {
        $style = new BlocStyle();
        $style->setBorderTop(2, 'blue');
        $this->assertEquals(['width' => 2, 'color' => 'blue'], $style->getBorderTop());
    }

    public function testGetSetBorderBottom()
    {
        $style = new BlocStyle();
        $style->setBorderBottom(2, 'blue');
        $this->assertEquals(['width' => 2, 'color' => 'blue'], $style->getBorderBottom());
    }

    public function testGetSetBorderLeft()
    {
        $style = new BlocStyle();
        $style->setBorderLeft(2, 'blue');
        $this->assertEquals(['width' => 2, 'color' => 'blue'], $style->getBorderLeft());
    }

    public function testGetSetBorderRight()
    {
        $style = new BlocStyle();
        $style->setBorderRight(2, 'blue');
        $this->assertEquals(['width' => 2, 'color' => 'blue'], $style->getBorderRight());
    }

    public function testGetSetPaddingTop()
    {
        $style = new BlocStyle();
        $style->setPaddingTop(2);
        $this->assertEquals(2, $style->getPaddingTop());
    }

    public function testGetSetPaddingBottom()
    {
        $style = new BlocStyle();
        $style->setPaddingBottom(2);
        $this->assertEquals(2, $style->getPaddingBottom());
    }

    public function testGetSetPaddingLeft()
    {
        $style = new BlocStyle();
        $style->setPaddingLeft(2);
        $this->assertEquals(2, $style->getPaddingLeft());
    }

    public function testGetSetPaddingRight()
    {
        $style = new BlocStyle();
        $style->setPaddingRight(2);
        $this->assertEquals(2, $style->getPaddingRight());
    }


    public function testSetPadding()
    {
        $style = new BlocStyle();
        $style->setPadding(2,3,4,5);

        $this->assertEquals(2, $style->getPaddingTop());
        $this->assertEquals(3, $style->getPaddingRight());
        $this->assertEquals(4, $style->getPaddingBottom());
        $this->assertEquals(5, $style->getPaddingLeft());

        $style->setPadding(2,3);

        $this->assertEquals(2, $style->getPaddingTop());
        $this->assertEquals(3, $style->getPaddingRight());
        $this->assertEquals(2, $style->getPaddingBottom());
        $this->assertEquals(3, $style->getPaddingLeft());

        $style->setPadding(2);

        $this->assertEquals(2, $style->getPaddingTop());
        $this->assertEquals(2, $style->getPaddingRight());
        $this->assertEquals(2, $style->getPaddingBottom());
        $this->assertEquals(2, $style->getPaddingLeft());
    }

    public function testPaddingWithInvalidNumberOfArgumentsShouldFail(){
        $this->expectException(InvalidArgumentException::class);

        $style = new BlocStyle();
        $style->setPadding(2,2,3);
    }

    public function testSetBorder()
    {
        $style = new BlocStyle();

        $style->setBorder(2,3);
        $this->assertEquals(['width' => 2, 'color' => 'black'], $style->getBorderTop());
        $this->assertEquals(['width' => 2, 'color' => 'black'], $style->getBorderBottom());
        $this->assertEquals(['width' => 3, 'color' => 'black'], $style->getBorderLeft());
        $this->assertEquals(['width' => 3, 'color' => 'black'], $style->getBorderRight());

        $style->setBorder(2,3,'red');
        $this->assertEquals(['width' => 2, 'color' => 'red'], $style->getBorderTop());
        $this->assertEquals(['width' => 2, 'color' => 'red'], $style->getBorderBottom());
        $this->assertEquals(['width' => 3, 'color' => 'red'], $style->getBorderLeft());
        $this->assertEquals(['width' => 3, 'color' => 'red'], $style->getBorderRight());
    }

    public function testSetBorderWithInvalidArgumentCountShouldFail(){
        $this->expectException(InvalidArgumentException::class);

        $style = new BlocStyle();
        $style->setBorder(2,3,4,5);
    }
}
