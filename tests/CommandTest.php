<?php

namespace App\Lib\Commandit\Tests;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use InvalidArgumentException;
use LogicException;
use \SitPHP\Commands\Command;
use \SitPHP\Commands\CommandManager;
use SitPHP\Commands\Output;
use SitPHP\Commands\Request;
use Doubles\TestCase;
use SitPHP\Commands\Tools\Bloc\BlocTool;
use SitPHP\Commands\Tools\Choice\ChoiceTool;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarTool;
use SitPHP\Commands\Tools\Question\QuestionTool;
use SitPHP\Commands\Tools\Section\SectionTool;
use SitPHP\Commands\Tools\Table\TableTool;

class CommandTest extends TestCase
{

    function testHideShow(){
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $this->assertFalse($command->isHidden());
        $command->hide();
        $this->assertTrue($command->isHidden());
        $command->show();
        $this->assertFalse($command->isHidden());
    }

    function testGetSetTitle(){
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setTitle('title');
        $this->assertEquals('title', $command->getTitle());
    }

    function testGetSetDescription(){
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setDescription('help');
        $this->assertEquals('help', $command->getDescription());
    }

    function testArgumentInfo(){
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setArgumentInfos('my_arg', 0, 'argument help');
        $this->assertEquals($command->getArgumentInfos('my_arg'), ['position' => 0, 'description' => 'argument help']);
        $this->assertEquals($command->getArgumentPosition('my_arg'), 0);
        $this->assertEquals($command->getArgumentDescription('my_arg'), 'argument help');
    }

    function testGetAllArgumentsInfos(){
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setArgumentInfos('my_arg', 0, 'argument help');

        $this->assertEquals(['my_arg' => ['position' => 0, 'description' => 'argument help']], $command->getAllArgumentsInfos());
    }

    function testSetArgumentTwiceShouldFail(){

        $this->expectException(InvalidArgumentException::class);
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setArgumentInfos('my_arg', 0, 'argument help 1');
        $command->setArgumentInfos('my_arg', 3, 'argument help 2');
    }

    function testSetArgumentInfoShouldFailWithNegativePosition(){
        $this->expectException(InvalidArgumentException::class);
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setArgumentInfos('my_arg', -1);

    }

    function testOptionInfo(){
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setOptionInfos('my_option', ['m', 'mo'], 'option help');
        $this->assertEquals($command->getOptionInfos('my_option'), ['flags' => ['m', 'mo'], 'description' => 'option help']);
        $this->assertEquals($command->getOptionFlags('my_option'), ['m', 'mo']);
        $this->assertEquals($command->getOptionDescription('my_option'), 'option help');
    }

    function testGetAllOptionsInfos(){
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setOptionInfos('my_option', ['m', 'mo'], 'option help');

        $this->assertEquals(['my_option' => ['flags' => ['m', 'mo'], 'description' => 'option help']], $command->getAllOptionsInfos());
    }

    function testGetOptionInfoWithStringFlags(){
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setOptionInfos('my_option', 'm | mo', 'option help');
        $this->assertEquals($command->getOptionInfos('my_option'), ['flags' => ['m', 'mo'], 'description' => 'option help']);
        $this->assertEquals($command->getOptionFlags('my_option'), ['m', 'mo']);
        $this->assertEquals($command->getOptionDescription('my_option'), 'option help');
    }

    function testGetOptionInfoWithoutFlagsOrDescription(){
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setOptionInfos('my_option');
        $this->assertEquals($command->getOptionInfos('my_option'), ['flags' => [], 'description' => null]);
    }

    function testSetOptionTwiceShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setOptionInfos('my_option', null, 'option help 1');
        $command->setOptionInfos('my_option', null, 'option help 2');

    }

    function testSetOptionFlagTwiceShouldFail(){
        $this->expectException(InvalidArgumentException::class);

        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setOptionInfos('my_option_1', 'f');
        $command->setOptionInfos('my_option_2', 'f');
    }

    public function testEnableDisable(){
        /** @var Command & DoubleStub $command */
        $command = Double::mock(Command::class)->getInstance();
        $this->assertFalse($command->isDisabled());
        $command->disable();
        $this->assertTrue($command->isDisabled());
        $command->enable();
        $this->assertFalse($command->isDisabled());
    }

    public function testExecute(){
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');
        $command = new MyCommand();
        $command->setManager(new CommandManager());

        $this->assertEquals(3, $command->execute($request));
    }

    public function testGetExecutionCount(){
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');

        $command = new MyCommand();
        $command->setManager(new CommandManager());

        $this->assertEquals(0, $command->getExecutionCount());
        $command->execute($request);
        $this->assertEquals(1, $command->getExecutionCount());
    }

    public function testGetArgsAndOptions(){
        $request = new Request('my_command', ['param_1', 'param_2', '-o1=option_1', '-oo=option_2', '--option_3'], 'php://temp', 'php://memory', 'php://memory');

        $command = new MyCommand();
        $command->setManager(new CommandManager());

        $status =  $command->execute($request);

        $this->assertEquals(3, $status);
        $this->assertEquals('my_command', $command->name);
        $this->assertEquals('my title', $command->title);
        $this->assertSame($request, $command->request);

        // Test args
        $this->assertEquals('param_1', $command->param_1);
        $this->assertEquals('param_2', $command->param_2);
        $this->assertEquals(['param_1' => 'param_1', 'param_2' => 'param_2'], $command->params);
        $this->assertNull($command->param_undefined);

        // Test options
        $this->assertEquals('option_1', $command->option_1);
        $this->assertEquals('option_2', $command->option_2);
        $this->assertTrue($command->option_3);
        $this->assertEquals(['option_1' => 'option_1', 'option_2' => 'option_2', 'option_3' => true], $command->options);
        $this->assertNull($command->option_undefined);

        // Test help
        $this->assertEquals('my title', $command->getTitle());
        $this->assertEquals('my help', $command->getDescription());
        $this->assertEquals('param_1 help', $command->getArgumentDescription('param_1'));
        $this->assertNull($command->getArgumentDescription('param_2'));
        $this->assertEquals('option_1 help', $command->getOptionDescription('option_1'));
        $this->assertEquals('option_2 help', $command->getOptionDescription('option_2'));
        $this->assertNull($command->getOptionDescription('option_3'));

        // Test verbosity
        $this->assertEquals(0, $command->verbosity);
        $this->assertEquals(false, $command->is_silent);
        $this->assertEquals(false, $command->is_quiet);
        $this->assertEquals(false, $command->is_verbose);
        $this->assertEquals(false, $command->is_debug);
    }
    public function testCommandWithUnexpectedParameterShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $request = new Request('my_command', ['param_1', 'param_2', 'param_3'], 'php://temp', 'php://memory', 'php://memory');
        $command = new MyCommand();
        $command->setManager(new CommandManager());
        $command->execute($request);
    }
    public function testCommandWithInvalidOptionShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $request = new Request('my_command', ['param_1', 'param_2', '--invalid-option'], 'php://temp', 'php://memory', 'php://memory');

        $command = new MyCommand();
        $command->setManager(new CommandManager());
        $command->execute($request);
    }
    public function testCommandWithInvalidFlagShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $request = new Request('my_command', ['param_1', 'param_2', '-io'], 'php://temp', 'php://memory', 'php://memory');

        $command = new MyCommand();
        $command->setManager(new CommandManager());
        $command->execute($request);
    }

    public function testCommandWithoutHandleMethodShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $request = new Request('my_invalid_command', ['param_1', 'param_2', '-io'], 'php://temp', 'php://memory', 'php://memory');
        $command = new MyCommand();
        $command->setManager(new CommandManager());
        $command->execute($request);
    }

    /*
     * Test tool
     */
    public function testTool()
    {
        $request = new Request('my_command');
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        $this->assertInstanceOf(SectionTool::class, $command->tool('section'));
    }

    public function testToolWithUndefinedToolShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $request = new Request('my_command');
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());
        $command->tool('undefined');
    }

    public function testToolShouldFailWhenRequestIsNotSet()
    {
        $this->expectException(LogicException::class);
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command->setManager(new CommandManager());
        $command->tool('section');
    }


    /*
     * Test write and tools
     */
    public function testWriteAndTools(){

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)->getInstance('php://memory');
        $output::_method('write')
            ->count(3)
            ->args(['write', Command::VERBOSITY_VERBOSE, 30, true], 1)
            ->args(['writeLn'."\n", Command::VERBOSITY_VERBOSE, 30, true], 2)
            ->args(["\n\n", Command::VERBOSITY_VERBOSE], 3);

        /** @var DoubleStub & Output $error_output */
        $error_output = Double::mock(Output::class)->getInstance('php://memory');
        $error_output::_method('write')
            ->count(3)
            ->args(['write', Command::VERBOSITY_VERBOSE, 30, true], 1)
            ->args(['writeLn'."\n", Command::VERBOSITY_VERBOSE, 30, true], 2)
            ->args(["\n\n", Command::VERBOSITY_VERBOSE],3);

        /** @var DoubleStub & Request $request */
        $request = Double::mock(Request::class)->getInstance('my_command', null,'php://temp', $output, $error_output);
        /** @var DoubleStub & CommandManager $command_manager */
        $command_manager = Double::mock(CommandManager::class)->getInstance();
        $command_manager::_method('call')
            ->count(1)
            ->args(['other_command', ['param1', 'param2'], $request->getInput(), $request->getOutput(), $request->getErrorOutput()])
            ->return('call');

        $command = new MyOtherCommand();
        $command->setManager($command_manager);
        $command->execute($request);

        $this->assertInstanceOf(BlocTool::class, $command->bloc);
        $this->assertEquals('content', $command->bloc->getContent());

        $this->assertInstanceOf(QuestionTool::class, $command->question);
        $this->assertEquals('prompt', $command->question->getPrompt());
        $this->assertEquals(['auto', 'complete'], $command->question->getAutoComplete());

        $this->assertInstanceOf(QuestionTool::class, $command->secret);
        $this->assertEquals('prompt', $command->secret->getPrompt());
        $this->assertTrue($command->secret->isSecretTypingActive());

        $this->assertInstanceOf(ChoiceTool::class, $command->choice);
        $this->assertEquals(['choice1', 'choice2'], $command->choice->getAllChoices());
        $this->assertEquals('prompt', $command->choice->getPrompt());
        $this->assertEquals('title', $command->choice->getTitle());

        $this->assertInstanceOf(SectionTool::class, $command->section);

        $this->assertInstanceOf(ProgressBarTool::class, $command->progress_bar);
        $this->assertEquals(3, $command->progress_bar->getSteps());

        $this->assertInstanceOf(TableTool::class, $command->table);
        $this->assertEquals([['cell1', 'cell2']], $command->table->getAllRows());

        $this->assertEquals('call', $command->call);
    }

}


class MyCommand extends Command {

    public $title;
    public $name;
    public $request;

    public $param_1;
    public $param_2;
    public $param_undefined;
    public $params;

    public $option_1;
    public $option_2;
    public $option_3;
    public $option_undefined;
    public $options;

    public $flag_o1;
    public $flag_undefined;
    public $flags;

    public $verbosity;
    public $is_silent;
    public $is_quiet;
    public $is_verbose;
    public $is_debug;

    function prepare(){
        $this->setTitle('my title');
        $this->setDescription('my help');
        $this->setArgumentInfos('param_1', 0, 'param_1 help');
        $this->setArgumentInfos('param_2', 1);
        $this->setOptionInfos('option_1', 'o1', 'option_1 help');
        $this->setOptionInfos('option_2', ['o2', 'oo'], 'option_2 help');
        $this->setOptionInfos('option_3', null);
    }

    function handle()
    {
        $this->title = $this->getTitle();
        $this->name = $this->getCommandName();
        $this->request = $this->getRequest();

        // Params
        $this->param_1 = $this->getArgument('param_1');
        $this->param_2 = $this->getArgument('param_2');
        $this->param_undefined = $this->getArgument('undefined');
        $this->params = $this->getAllArgs();

        // Options
        $this->option_1 = $this->getOption('option_1');
        $this->option_2 = $this->getOption('option_2');
        $this->option_3 = $this->getOption('option_3');
        $this->option_undefined = $this->getOption('undefined');
        $this->options = $this->getAllOptions();

        // Flags
        $this->flag_o1 = $this->getFlag('o1');
        $this->flag_undefined = $this->getFlag('undefined');
        $this->flags = $this->getAllFlags();

        // Verbosity
        $this->verbosity = $this->getVerbosity();
        $this->is_silent = $this->isSilent();
        $this->is_quiet = $this->isQuiet();
        $this->is_verbose = $this->isVerbose();
        $this->is_debug = $this->isDebug();

        return 3;
    }
}

class MyOtherCommand extends Command {

    /** @var BlocTool */
    public $bloc;
    /** @var QuestionTool */
    public $question;
    /** @var QuestionTool */
    public $secret;
    /** @var ChoiceTool */
    public $choice;
    /** @var SectionTool */
    public $section;
    /** @var ProgressBarTool */
    public $progress_bar;
    /** @var TableTool */
    public $table;

    public $call;

    function handle(){
        $this->write('write', self::VERBOSITY_VERBOSE, 30, true);
        $this->writeLn('writeLn', self::VERBOSITY_VERBOSE, 30, true);
        $this->lineBreak(2, self::VERBOSITY_VERBOSE);
        $this->errorWrite('write', self::VERBOSITY_VERBOSE, 30, true);
        $this->errorWriteLn('writeLn', self::VERBOSITY_VERBOSE, 30, true);
        $this->errorLineBreak(2, self::VERBOSITY_VERBOSE);

        $this->bloc = $this->bloc('content');
        $this->question = $this->question('prompt', ['auto', 'complete']);
        $this->secret = $this->secret('prompt');
        $this->choice = $this->choice(['choice1', 'choice2'], 'prompt', 'title');
        $this->section = $this->section();
        $this->progress_bar = $this->progressBar(3);
        $this->table = $this->table(['cell1', 'cell2']);

        $this->call = $this->call('other_command', ['param1', 'param2']);
    }
}

class MyInvalidCommand extends Command{

}
class MyQuietCommand extends Command{
    function handle(){
        $this->write('quiet', Command::VERBOSITY_QUIET);
    }
}
class MyVerboseCommand extends Command{
    function handle(){
        $this->write('verbose', Command::VERBOSITY_VERBOSE);
    }
}
class MyDebugCommand extends Command{
    function handle(){
        $this->write('debug', Command::VERBOSITY_DEBUG);
    }
}
class MyFormattedCommand extends Command{
    function handle(){
        $this->write('response <cs color="red">formatted</cs>');
    }
}
