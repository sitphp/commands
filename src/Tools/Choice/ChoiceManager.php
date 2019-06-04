<?php


namespace SitPHP\Commands\Tools\Choice;

use SitPHP\Commands\Command;
use SitPHP\Commands\ToolManager;

class ChoiceManager extends ToolManager
{
    protected $styles;

    function __construct()
    {
        $this->buildStyle('default');
    }

    function make(Command $command, ...$params){
        $tool = new ChoiceTool($command , $this);
        $tool->setStyle('default');

        return $tool;
    }


    function buildStyle(string $name){
        return $this->styles[$name] = new ChoiceStyle();
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