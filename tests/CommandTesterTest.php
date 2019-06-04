<?php

namespace SitPHP\Commands\Tests;

use Doubles\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandTester;
use SitPHP\Commands\CommandManager;

class CommandTesterTest extends TestCase
{

    public function testCall()
    {
        $command_manager = new CommandManager();
        $command_test = new CommandTester($command_manager);
        $command_test->setAnswer('go', 'How do you feel ?');
        $command_test->setAnswer('alex', 'What is your name ?');
        $command_test->setAnswer('coding');
        $this->assertEquals('Your name is alex
You feel good
You are coding
', $command_test->call(CommandTesterCommand::class));
    }

    public function testRunRegisteredCommand(){
        $command_manager = new CommandManager();
        $command_manager->setCommand('my_command',CommandTesterCommand::class);

        $command_test = new CommandTester($command_manager);
        $this->assertEquals('Your name is 
You feel 
You are 
', $command_test->call('my_command'));
    }

    public function testRunWithParams()
    {
        $command_manager = new CommandManager();
        $command_test = new CommandTester($command_manager);
        $command_test->setAnswer('go', 'How do you feel ?');
        $command_test->setAnswer('alex', 'What is your name ?');
        $command_test->setAnswer('coding');

        $this->assertEquals('', $command_test->call(CommandTesterCommand::class, ['--no-interaction']));
    }

    public function testRunWithFormat()
    {
        $command_manager = new CommandManager();
        $command_test = new CommandTester($command_manager);
        $command_test->setAnswer('go', 'How do you feel ?');
        $command_test->setAnswer('alex', 'What is your name ?');
        $command_test->setAnswer('coding');

        $this->assertEquals('Your name is [32malex[0m
You feel [33mgood[0m
You are [34mcoding[0m
', $command_test->call(CommandTesterCommand::class, null, true));
    }

    public function testGetStatusCode(){
        $command_manager = new CommandManager();
        $command_manager->setCommand('my_command',CommandTesterCommand::class);

        $command_test = new CommandTester($command_manager);
        $this->assertNull($command_test->getStatusCode());
        $command_test->call('my_command');
        $this->assertEquals(0,$command_test->getStatusCode());
    }

    public function testCallCommandAs(){
        $command_manager = new CommandManager();
        $command = new CommandTesterCommand();
        $command_test = $command_manager->test();

        $this->assertEquals('Your name is 
You feel 
You are 
', $command_test->callCommandAs($command, 'my_command'));
    }
}


class CommandTesterCommand extends Command {

    function handle(){

        $response = $this->question('What is your name ?')
            ->ask();
        if($response !== null){
            $this->writeLn('Your name is <cs color="green">'.$response.'</cs>');
        }

        $response = $this->question('How do you feel ?')
            ->setAutoComplete(['good', 'bad', 'so so'])
            ->ask();
        if($response !== null){
            $this->writeLn('You feel <cs color="yellow">'.$response.'</cs>');
        }

        $response = $this->question('What are you doing ?')
            ->ask();
        if($response !== null){
            $this->writeLn('You are <cs color="blue">'.$response.'</cs>');
        }

    }
}