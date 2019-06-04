<?php

namespace SitPHP\Commands\Tests\Helpers;

use Doublit\TestCase;
use SitPHP\Commands\Helpers\CharHelper;

class CharHelperTest extends TestCase
{
    /*
   * Test chars
   */
    function testIsControlChar(){
        $this->assertTrue(CharHelper::isControlKeyChar("\001", 'A'));
        $this->assertTrue(CharHelper::isControlKeyChar("\0037"));
    }
    function testIsControlCharWithInvalidKeyShouldFail(){
        $this->expectException(\InvalidArgumentException::class);
        $this->assertTrue(CharHelper::isControlKeyChar("\001", '+'));
    }

    function testIsBackspaceChar(){
        $this->assertTrue(CharHelper::isBackspaceChar("\177"));
    }
    function testIsSpaceChar(){
        $this->assertTrue(CharHelper::isSpaceChar("\032"));
    }
    function testIsTabChar(){
        $this->assertTrue(CharHelper::isTabChar("\t"));
    }

    function testIsReturnChar(){
        $this->assertTrue(CharHelper::isReturnChar(PHP_EOL));
    }
    function testIsEscapeChar(){
        $this->assertTrue(CharHelper::isEscapeChar("\033"));
        $this->assertFalse(CharHelper::isEscapeChar("\03345"));
    }
    function testIsEscapedChar(){
        $this->assertTrue(CharHelper::isEscapedChar("\03345"));
    }
    function testIsArrowChar(){

        $this->assertTrue(CharHelper::isArrowChar("\033[A"));
        $this->assertTrue(CharHelper::isArrowChar("\033[B"));
        $this->assertFalse(CharHelper::isArrowChar("\03345"));
        $this->assertFalse(CharHelper::isArrowChar("\001"));
    }
    function testIsArrowUpChar(){
        $this->assertTrue(CharHelper::isArrowUpChar("\033[A"));
        $this->assertFalse(CharHelper::isArrowUpChar("\033[B"));
        $this->assertFalse(CharHelper::isArrowUpChar("\03345"));
    }
    function testIsArrowDownChar(){
        $this->assertTrue(CharHelper::isArrowDownChar("\033[B"));
        $this->assertFalse(CharHelper::isArrowDownChar("\033[A"));
        $this->assertFalse(CharHelper::isArrowDownChar("\03345"));
    }
    function testIsArrowLeftChar(){
        $this->assertTrue(CharHelper::isArrowLeftChar("\033[D"));
        $this->assertFalse(CharHelper::isArrowLeftChar("\033[C"));
        $this->assertFalse(CharHelper::isArrowLeftChar("\03345"));
    }
    function testIsArrowRightChar(){
        $this->assertTrue(CharHelper::isArrowRightChar("\033[C"));
        $this->assertFalse(CharHelper::isArrowRightChar("\033[D"));
        $this->assertFalse(CharHelper::isArrowRightChar("\03345"));
    }
    function testIsContentChar(){
        $this->assertFalse(CharHelper::isContentChar("\033[C"));
        $this->assertTrue(CharHelper::isContentChar("a"));
    }
}
