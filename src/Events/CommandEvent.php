<?php


namespace SitPHP\Commands\Events;


use SitPHP\Commands\Command;
use SitPHP\Events\Event;

class CommandEvent extends Event
{
    /**
     * @return Command
     */
    function getCommand(){
        return $this->getParam('command');
    }
}