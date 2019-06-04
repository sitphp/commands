<?php


namespace SitPHP\Commands\Tests\Commands;

use Doublit\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandTester;
use SitPHP\Commands\CommandManager;

class ListCommandTest extends TestCase
{

    function testListCommand(){

        $command_manager = new CommandManager();
        $list_command_test = $command_manager->test();
        $list = $command_manager->getCommand('list');
        $output = $list_command_test->call('list');

        $this->assertContains('------------------------
Registered commands
------------------------', $output);
        $this->assertContains($list->getDescription(), $output);
    }

    function testListCommandWithGroups(){
        $command_manager = new CommandManager();
        $command_manager->setCommand('group2:command2', ListGroupCommand1::class);
        $command_manager->setCommand('group2:command1', ListGroupCommand1::class);
        $command_manager->setCommand('group2:command3', ListGroupCommand1::class);
        $command_manager->setCommand('group1:command1', ListGroupCommand1::class);
        $command_manager->setCommand('group1:command2', ListGroupCommand2::class);
        $group1_command2 = $command_manager->getCommand('group1:command2');
        $group1_command2->hide();

        $list_command_test = $command_manager->test();
        $this->assertContains('------------------------
Registered commands
------------------------

help            Shows command help                                 
list            This command provides a list of registered commands
group1                                                             
group1:command1                                                    
group2                                                             
group2:command1                                                    
group2:command2                                                    
group2:command3                                                    
',$list_command_test->call('list'));
    }

}

class ListGroupCommand1 extends Command{
    function prepare(){
        $this->setArgumentInfos('arg1', 2);
        $this->setArgumentInfos('arg2', 1);
        $this->setArgumentInfos('arg3', 3);
    }
    function handle(){

    }
}
class ListGroupCommand2 extends Command{

    function handle(){

    }
}