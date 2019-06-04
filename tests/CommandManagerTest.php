<?php

namespace SitPHP\Commands\Tests;

use Doubles\Double;
use Doubles\TestCase;
use InvalidArgumentException;
use SitPHP\Benchmarks\BenchManager;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandTester;
use SitPHP\Commands\Events\CommandEvent;
use SitPHP\Commands\Events\ExceptionEvent;
use SitPHP\Commands\Request;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Output;
use SitPHP\Commands\ToolManager;
use SitPHP\Commands\Tools\Bloc\BlocManager;
use SitPHP\Commands\Tools\Choice\ChoiceManager;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarManager;
use SitPHP\Commands\Tools\Question\QuestionManager;
use SitPHP\Commands\Tools\Section\SectionManager;
use SitPHP\Commands\Tools\Table\TableManager;
use SitPHP\Events\Event;
use SitPHP\Events\EventManager;
use SitPHP\Formatters\FormatterManager;

class CommandManagerTest extends TestCase
{
    /**
     * @var CommandManager
     */
    private $command_manager;

    function setUp()
    {
        parent::setUp();
        $this->command_manager = new CommandManager();
    }

    /* Test command set/get */
    function testGetSetCommand()
    {
        $this->command_manager->setCommand('my_command', MyTestCommand::class);
        $command = $this->command_manager->getCommand('my_command');
        $this->assertInstanceOf(Command::class, $command);
    }

    function testGetSetCommandObject(){
        $command = new MyTestCommand();
        $this->command_manager->setCommand('my_command', $command);
        $this->assertSame($command, $this->command_manager->getCommand('my_command'));
    }

    function testSetCommandWithConfig()
    {
        $this->command_manager->setCommand('my_command', MyTestCommand::class, ['hide' => true]);

        $this->command_manager->setCommand('my_other_command', MyOtherTestCommand::class, ['hide' => true]);

        $command_1 = $this->command_manager->getCommand('my_command');
        $command_2 = $this->command_manager->getCommand('my_other_command');
        $this->assertTrue($command_1->isHidden());
        $this->assertTrue($command_2->isHidden());
    }

    function testGetCommandTwice()
    {
        $this->command_manager->setCommand('my_command', MyTestCommand::class);
        $command1 = $this->command_manager->getCommand('my_command');
        $command2 = $this->command_manager->getCommand('my_command');
        $this->assertSame($command1, $command2);
    }

    function testGetUndefinedCommandShouldReturnNull()
    {
        $command_definition = $this->command_manager->getCommand('undefined');
        $this->assertNull($command_definition);
    }


    function testRemoveCommand()
    {
        $this->command_manager->setCommand('my_command', MyTestCommand::class);
        $this->command_manager->call('my_command');
        $this->command_manager->removeCommand('my_command');
        $this->command_manager->removeCommand('undefined');
        $this->assertNull($this->command_manager->getCommand('my_command'));
    }

    function testGetAllCommands()
    {
        $this->command_manager->setCommand('my_command', MyTestCommand::class);
        $commands = $this->command_manager->getAllCommands();

        $this->assertInstanceOf(Command::class, $commands['list']);
        $this->assertInstanceOf(Command::class, $commands['help']);
        $this->assertInstanceOf(Command::class, $commands['my_command']);

        $this->assertEquals(['help', 'list', 'my_command'], array_keys($commands));
    }

    function testHasCommand(){
        $this->command_manager->setCommand('my_command', MyTestCommand::class);
        $this->assertTrue($this->command_manager->hasCommand('my_command'));
        $this->assertFalse($this->command_manager->hasCommand('undefined'));
    }

    function testSetInvalidCommandShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->command_manager->setCommand('my_command', MyTestCommand::class);
        $this->command_manager->setCommand('my_command', new \stdClass());
    }

    /*
     * Test global options
     */
    function testGlobalOptions()
    {
        $this->command_manager->setGlobalOption('my_option', ['flag1', 'flag2'], 'global help');
        $this->assertEquals([
            'flags' => ['flag1', 'flag2'],
            'description' => 'global help'
        ], $this->command_manager->getGlobalOption('my_option'));
    }

    function testRemoveGlobalOption()
    {
        $this->command_manager->setGlobalOption('myoption', ['flag1', 'flag2'], 'global help');
        $this->command_manager->removeGlobalOption('myoption');
        $this->assertNull($this->command_manager->getGlobalOption('myoption'));
    }

    function testGetAllGlobalOptions()
    {
        $this->assertEquals(['help' => Array(
            'flags' => 'h',
            'description' => 'Show this help message'
        ),
            'silent' => Array(
                'flags' => null,
                'description' => 'Silent mode : hide all messages'
            ),
            'quiet' => Array(
                'flags' => null,
                'description' => 'Quiet mode : show only quiet messages'
            ),
            'verbose' => Array(
                'flags' => null,
                'description' => 'Verbose mode : show verbose messages'
            ),
            'debug' => Array(
                'flags' => null,
                'description' => 'Debug mode : show debug messages'
            ),
            'format' => Array(
                'flags' => null,
                'description' => 'Force output to format'
            ),
            'no-format' => Array(
                'flags' => null,
                'description' => 'Force output not to format'
            ),
            'no-interaction' => Array(
                'flags' => null,
                'description' => 'Hide interactive messages'
            )], $this->command_manager->getAllGlobalOptions());
    }

    /* Test tool managers */

    function testGetSetToolManager()
    {
        $this->command_manager->setToolManager('my_tool', ConsoleTestTool::class);
        $this->assertInstanceOf(ConsoleTestTool::class, $this->command_manager->getToolManager('my_tool'));
    }

    function testGetToolManagerShouldFailWithInvalidClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->command_manager->setToolManager('my_tool', \stdClass::class);
        $this->assertEquals(\stdClass::class, $this->command_manager->getToolManager('my_tool'));
    }

    function testGetUndefinedToolShouldReturnNull()
    {
        $this->assertNull($this->command_manager->getToolManager('undefined'));
    }

    function testRemoveToolManager()
    {
        $this->command_manager->setToolManager('my_tool', \stdClass::class);
        $this->command_manager->removeToolManager('my_tool');
        $this->assertNull($this->command_manager->getToolManager('my_tool'));
    }

    function testGetTableManager()
    {
        $this->assertInstanceOf(TableManager::class, $this->command_manager->getTableManager());
    }

    function testGetBlocManager()
    {
        $this->assertInstanceOf(BlocManager::class, $this->command_manager->getBlocManager());
    }

    function testGetQuestionManager()
    {
        $this->assertInstanceOf(QuestionManager::class, $this->command_manager->getQuestionManager());
    }

    function testGetChoiceManager()
    {
        $this->assertInstanceOf(ChoiceManager::class, $this->command_manager->getChoiceManager());
    }

    function testGetSectionManager()
    {
        $this->assertInstanceOf(SectionManager::class, $this->command_manager->getSectionManager());
    }

    function testGetProgressBarManager()
    {
        $this->assertInstanceOf(ProgressBarManager::class, $this->command_manager->getProgressBarManager());
    }

    /*
     * Test run/call
     */

    function testRun()
    {
        $this->command_manager->setCommand('my_command', MyTestCommand::class);
        $request = new Request('my_command', null, 'php://temp', 'php://memory');
        $this->command_manager->run($request);
        $handle = $request->getOutput()->getHandle();
        rewind($handle);
        $this->assertEquals('handled' . "\n", stream_get_contents($handle));
    }

    function testRunTwice(){
        $this->command_manager->setCommand('my_command', MyTestCommand::class);
        $request = new Request('my_command', null, 'php://temp', 'php://memory');
        $this->command_manager->run($request);
        $this->command_manager->run($request);
        $handle = $request->getOutput()->getHandle();
        rewind($handle);
        $this->assertEquals('handled' . "\n".'handled' . "\n", stream_get_contents($handle));
    }

    function testRunWithNonRegisteredCommandNamePath()
    {
        $request = new Request(MyTestCommand::class, null, 'php://temp', 'php://memory');
        $this->command_manager->run($request);
        $handle = $request->getOutput()->getHandle();
        rewind($handle);
        $this->assertEquals('handled' . "\n", stream_get_contents($handle));
    }

    function testRunWithNonRegisteredCommandNameShorthand()
    {
        $request = new Request('SitPHP/Commands/Tests:test-command', null, 'php://temp', 'php://memory');
        $this->command_manager->run($request);
        $handle = $request->getOutput()->getHandle();
        rewind($handle);
        $this->assertEquals('handled' . "\n", stream_get_contents($handle));
    }

    function testCall()
    {
        $this->command_manager->setCommand('my_command', MyTestCommand::class);
        $output = new Output('php://memory');
        $this->command_manager->call('my_command', null, 'php://temp', $output);
        rewind($output->getHandle());
        $this->assertEquals('handled' . "\n", stream_get_contents($output->getHandle()));
    }

    function testRunCommandWithHelpOption()
    {
        $help_command = Double::mock(HelpTestCommand::class)->getClass();
        $this->command_manager->setCommand('help', $help_command);

        $test_command = Double::mock(MyTestCommand::class)->getClass();
        $this->command_manager->setCommand('my_command', $test_command);

        $help_command::_method('execute')->count(1);
        $test_command::_method('execute')->count(0);

        $request = new Request('my_command', ['--help'], 'php://temp', 'php://memory');
        $this->command_manager->run($request);
    }

    function testCommandWithHFlag()
    {
        $help_command = Double::mock(HelpTestCommand::class)->getClass();
        $this->command_manager->setCommand('help', $help_command);

        $test_command = Double::mock(MyTestCommand::class)->getClass();
        $this->command_manager->setCommand('my_command', $test_command);

        $help_command::_method('execute')->count(1);
        $test_command::_method('execute')->count(0);

        $request = new Request('my_command', ['-h'], 'php://temp', 'php://memory');
        $this->command_manager->run($request);
    }

    /*
     * Test dependencies
     */
    function testGetSetFormatterManager()
    {
        $this->assertInstanceOf(FormatterManager::class, $this->command_manager->getFormatterManager());
        $formatter_manager = new FormatterManager();
        $this->command_manager->setFormatterManager($formatter_manager);
        $this->assertSame($formatter_manager, $this->command_manager->getFormatterManager());
    }

    function testGetSetEventManager()
    {
        $this->assertInstanceOf(EventManager::class, $this->command_manager->getEventManager());
        $event_manager = new EventManager();
        $this->command_manager->setEventManager($event_manager);
        $this->assertSame($event_manager, $this->command_manager->getEventManager());
    }

    function testGetSetBenchManager()
    {
        $this->assertInstanceOf(BenchManager::class, $this->command_manager->getBenchManager());
        $bench_manager = new BenchManager();
        $this->command_manager->setBenchManager($bench_manager);
        $this->assertSame($bench_manager, $this->command_manager->getBenchManager());
    }


    /*
     * Test events
     */
    function testAddRequestListener()
    {
        $this->command_manager->setCommand('my_command', MyTestCommand::class);
        $request = new Request('my_command', null, 'php://temp', 'php://memory');
        $this->command_manager->addOnRequestListener(function (Event $event) {
            $request = $event->getParam('request');
            $request->before = true;
        });

        $this->command_manager->run($request);
        $this->assertTrue($request->before);
    }

    function testRunWithInvalidCommandNameShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $request = new Request('undefined', null, 'php://temp', 'php://memory');
        $this->command_manager->run($request);
    }

    function testAddBeforeCommandListener()
    {
        $command = new MyTestCommand();
        $this->command_manager->addBeforeCommandListener(function (CommandEvent $event) {
            $command = $event->getCommand();
            $command->before = true;
        }, null, 'my_command');

        $this->command_manager->callCommandAs($command, 'my_command', null,'php://temp', 'php://memory');
        $this->assertTrue($command->before);
    }

    function testAddBeforeCommandListenerToMany()
    {

        $command_1 = new MyTestCommand();
        $command_2 = new MyOtherTestCommand();

        $this->command_manager->addBeforeCommandListener(function (CommandEvent $event) {
            $command = $event->getCommand();
            $command->before = true;
        }, null, ['my_command', 'my_other_command']);

        $this->command_manager->callCommandAs($command_1, 'my_command', null, 'php://temp', 'php://memory');
        $this->command_manager->callCommandAs($command_2, 'my_other_command', null, 'php://temp', 'php://memory');
        $this->assertTrue($command_1->before);
        $this->assertTrue($command_2->before);
    }

    function testAddBeforeCommandListenerToAll()
    {
        $command = new MyTestCommand();
        $this->command_manager->addBeforeCommandListener(function (CommandEvent $event) {
            $command = $event->getCommand();
            $command->before = true;
        });

        $this->command_manager->callCommandAs($command, 'my_command', null, 'php://temp', 'php://memory');
        $this->assertTrue($command->before);
    }

    function testAddAfterCommandListener()
    {
        $command = new MyTestCommand();
        $this->command_manager->addAfterCommandListener(function (CommandEvent $event) {
            $command = $event->getCommand();
            $command->after = true;
        }, 'my_command');

        $this->command_manager->callCommandAs($command, 'my_command', null, 'php://temp', 'php://memory');
        $this->assertTrue($command->after);
    }

    function testAddAfterCommandListenerToMany()
    {
        $command_1 = new MyTestCommand();
        $command_2 = new MyOtherTestCommand();

        $this->command_manager->addAfterCommandListener(function (CommandEvent $event) {
            $command = $event->getCommand();
            $command->after = true;
        }, ['my_command', 'my_other_command']);

        $this->command_manager->callCommandAs($command_1, 'my_command', null, 'php://temp', 'php://memory');
        $this->command_manager->callCommandAs($command_2, 'my_other_command', null, 'php://temp', 'php://memory');
        $this->assertTrue($command_1->after);
        $this->assertTrue($command_2->after);
    }

    function testAddAfterCommandListenerToAll()
    {
        $command = new MyTestCommand();
        $this->command_manager->addAfterCommandListener(function (CommandEvent $event) {
            $command = $event->getCommand();
            $command->after = true;
        });

        $this->command_manager->callCommandAs($command, 'my_command', null, 'php://temp', 'php://memory');
        $this->assertTrue($command->after);
    }

    function testExceptionListener()
    {
        try {
            $request = new Request('invalid', null, 'php://temp', 'php://memory');
            $this->command_manager->addOnExceptionListener(function (ExceptionEvent $event) {
                $request = $event->getRequest();
                $request->exception = true;

                $exception = $event->getException();
                $exception->exception = true;
            });

            $this->command_manager->run($request);
        } catch (\Exception $e) {
            $this->assertTrue($request->exception);
            $this->assertTrue($e->exception);
        }
    }

    function testInvalidCommandListenerShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->command_manager->addAfterCommandListener('listener', new \stdClass());
    }

    function testTest()
    {
        $this->assertInstanceOf(CommandTester::class, $this->command_manager->test('mycommand'));
    }
}

class MyTestCommand extends Command
{
    function handle()
    {
        $this->write('handled');
    }
}

class MyOtherTestCommand extends Command
{
    function handle()
    {
        $this->write('handled');
    }
}

class HelpTestCommand extends Command
{

    function prepare()
    {
        $this->setArgumentInfos('command', 0);
    }

    function handle()
    {

    }
}

class ConsoleTestTool extends ToolManager
{

    function make(Command $command, ...$params)
    {
        //...
    }
}