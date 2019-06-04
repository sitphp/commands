<?php

namespace SitPHP\Commands\Tests\Tools\Choice;

use Doubles\TestCase;
use SitPHP\Commands\Tools\Choice\ChoiceStyle;

class ChoiceStyleTest extends TestCase
{

    public function testGetName()
    {
        $style = new ChoiceStyle('name');
        $this->assertEquals('name', $style->getName());
    }

    public function testGetSetTitleFormat()
    {
        $style = new ChoiceStyle();
        $style->setTitleFormat('format');
        $this->assertEquals('format', $style->getTitleFormat());
    }

    public function testGetSetErrorFormat()
    {
        $style = new ChoiceStyle();
        $style->setErrorFormat('format');
        $this->assertEquals('format', $style->getErrorFormat());
    }

    public function testGetSetInputFormat()
    {
        $style = new ChoiceStyle();
        $style->setInputFormat('format');
        $this->assertEquals('format', $style->getInputFormat());
    }


    public function testGetSetPromptFormat()
    {
        $style = new ChoiceStyle();
        $style->setPromptFormat('format');
        $this->assertEquals('format', $style->getPromptFormat());
    }

    public function testSetAutoCompleteFormat()
    {
        $style = new ChoiceStyle();
        $style->setAutoCompleteFormat('format');
        $this->assertEquals('format', $style->getAutoCompleteFormat());
    }

    public function testGetSetChoiceFormat()
    {
        $style = new ChoiceStyle();
        $style->setChoiceFormat('key', 'value');
        $this->assertEquals(['key' => 'key', 'value' => 'value'], $style->getChoiceFormat());
    }

    public function testGetSetQuitFormat()
    {
        $style = new ChoiceStyle();
        $style->setQuitFormat('key', 'value');
        $this->assertEquals(['key' => 'key', 'value' => 'value'], $style->getQuitFormat());
    }

    public function testGetSetQuitMessage()
    {
        $style = new ChoiceStyle();
        $style->setQuitMessage('key','value');
        $this->assertEquals(['key' => 'key', 'value' => 'value'], $style->getQuitMessage());
    }


    public function testGetErrorMessage()
    {
        $style = new ChoiceStyle();
        $style->setErrorMessage('message');
        $this->assertEquals('message', $style->getErrorMessage());
    }

}
