<?php

namespace SitPHP\Commands\Tests\Commands;

use SitPHP\Commands\Command;

class TestCommand extends Command
{
    function handle(){
        $this->write('handled');
    }
}