<?php


namespace SitPHP\Commands\Tools\Section;


use SitPHP\Commands\Command;
use SitPHP\Commands\ToolManager;

class SectionManager extends ToolManager
{

    function make(Command $command, ...$params){
        return new SectionTool($command, $this);
    }
}