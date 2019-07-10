<?php

namespace SitPHP\Commands;

use SitPHP\Commands\Commands\ExceptionCommand;
use Throwable;

class ExceptionHandler
{
    /**
     * @param CommandManager $command_manager
     * @param Request $request
     * @param Throwable $exception
     * @throws Throwable
     */
    function handleForConsole(CommandManager $command_manager, Request $request, Throwable $exception)
    {
        $exception_command = new ExceptionCommand($exception);
        $command_manager->callCommandAs($exception_command, 'exception', $request->getParams(), $request->getInput(), $request->getOutput(), $request->getErrorOutput());


    }

}