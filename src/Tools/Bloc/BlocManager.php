<?php


namespace SitPHP\Commands\Tools\Bloc;

use SitPHP\Commands\Command;
use SitPHP\Commands\ToolManager;

class BlocManager extends ToolManager
{
    protected $styles;

    /**
     * BlocToolManager constructor.
     */
    function __construct()
    {
        $this->buildStyle('default');
    }

    /**
     * @param Command $command
     * @param array $params
     * @return BlocTool
     */
    function make(Command $command, ...$params){
        $tool = new BlocTool($command, $this);
        $tool->setStyle('default');

        return $tool;
    }


    /**
     * @param string $name
     * @return BlocStyle
     */
    function buildStyle(string $name){
        return $this->styles[$name] = new BlocStyle($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    function getStyle(string $name){
        return $this->styles[$name] ?? null;
    }

    /**
     * @param string $name
     */
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