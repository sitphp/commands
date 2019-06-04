<?php


namespace SitPHP\Commands\Tools\Question;


use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\ToolManager;

class QuestionManager extends ToolManager
{

    private $styles = [];

    function __construct()
    {
        $this->buildStyle('default');
    }

    function make(Command $command, ...$params)
    {
        $tool = new QuestionTool($command , $this);
        $tool->setStyle('default');

        return $tool;
    }

    function buildStyle(string $name){
        return $this->styles[$name] = new QuestionStyle();
    }

    function getStyle(string $name){
        return $this->styles[$name] ?? null;
    }

    function removeStyle(string $name){
        unset($this->styles[$name]);
    }

    /**
     * @param $name
     * @return bool
     */
    function hasStyle(string $name)
    {
        return isset($this->styles[$name]);
    }
}