<?php

namespace SitPHP\Commands\Tests\Commands;

use Doubles\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandTester;
use SitPHP\Commands\CommandManager;

class HelpCommandTest extends TestCase
{
    function testHelpCommand(){

        $command_manager = new CommandManager();
        $help_command = $command_manager->test();
        $list = $command_manager->getCommand('list');
        $output = $help_command->call('help', ['list']);
        $this->assertContains('------------------------
Help for "list" command
------------------------
', $output);
        $this->assertContains($list->getDescription(), $output);
    }

    function testHelpCommandWithoutArgumentShouldFail(){
        $this->expectException(\InvalidArgumentException::class);
        $command_manager = new CommandManager();
        $help_command = $command_manager->test();
        $help_command->call('help');
    }

    function testHelpCommandWithUndefinedArgumentShouldFail(){
        $this->expectException(\InvalidArgumentException::class);
        $command_manager = new CommandManager();
        $help_command = $command_manager->test();
        $help_command->call('help', ['undefined']);
    }

    function testHelpCommandShouldSortArgumentsByOrder(){

        $command_manager = new CommandManager();
        $command_manager->setCommand('my_command', HelpCommand1::class);
        $help_command = $command_manager->test();
        $this->assertContains('Arguments
0                    description 1
1                    description 2
2                    description 3
', $help_command->call('help', ['my_command']));
    }
}

class HelpCommand1 extends Command {
    function prepare(){
        $this->setArgumentInfos('arg1', 2, 'description 2');
        $this->setArgumentInfos('arg2', 1, 'description 1');
        $this->setArgumentInfos('arg3', 3, 'description 3');
    }
    function handle(){

    }
}
