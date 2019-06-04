<?php

namespace SitPHP\Commands\Tests\Tools;

use Doubles\TestCase;
use SitPHP\Commands\Tools\Question\QuestionStyle;

class QuestionStyleTest extends TestCase
{

    public function testGetName()
    {
        $style = new QuestionStyle('name');
        $this->assertEquals('name', $style->getName());
    }

    public function testGetSetPromptFormat()
    {
        $style = new QuestionStyle('name');
        $style->setPromptFormat('format');
        $this->assertEquals('format', $style->getPromptFormat());
    }

    public function testGetSetAutocompleteFormat()
    {
        $style = new QuestionStyle('name');
        $style->setAutocompleteFormat('format');
        $this->assertEquals('format', $style->getAutocompleteFormat());
    }

    public function testGetSetPlaceholderFormat()
    {
        $style = new QuestionStyle('name');
        $style->setPlaceholderFormat('format');
        $this->assertEquals('format', $style->getPlaceholderFormat());
    }


    public function testGetSetInputFormat()
    {
        $style = new QuestionStyle('name');
        $style->setInputFormat('format');
        $this->assertEquals('format', $style->getInputFormat());
    }


}
