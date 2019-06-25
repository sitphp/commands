<?php


namespace SitPHP\Commands\Commands;


use Doubles\Double;
use mysql_xdevapi\Exception;
use SitPHP\Commands\Command;
use SitPHP\Commands\Tools\Table\Line;
use SitPHP\Formatters\Formatters\CliFormatter;

class ExampleCommand extends Command
{
    // Define arguments and options
    function prepare(){
        $this->setOptionInfos('size', ['s', 'm', 'l']);
    }

    function handle(){

        throw new Exception('fdsfds');

    }
}