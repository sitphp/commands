<?php


namespace SitPHP\Commands;


abstract class ToolManager
{

    protected $command_manager;


    abstract function make(Command $command, ...$params);
}