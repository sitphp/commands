<?php

namespace SitPHP\Commands\Tests\Tools\Choice;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use InvalidArgumentException;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Input;
use SitPHP\Commands\Output;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Choice\ChoiceManager;
use SitPHP\Commands\Tools\Choice\ChoiceStyle;
use SitPHP\Commands\Tools\Choice\ChoiceTool;

class ChoiceToolTest extends TestCase
{
    public function makeChoice(){

        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        $choice = new ChoiceTool($command, new ChoiceManager());

        return $choice;
    }

    public function makeTtyChoice(array $input_chars = null){
        $input = Double::mock(Input::class)->getInstance('php://temp');
        $input::_method('isatty')->return(true);
        $input::_method('readChar')->return(function () use($input, $input_chars){
            if($input_chars === null){
                return fgetc($input->getHandle());
            }
            static $char_count = 0;
            $char = $input_chars[$char_count] ?? '';
            $char_count++;

            return $char;
        });

        $output = Double::mock(Output::class)->getInstance('php://memory');
        $output::_method('isatty')->return(true);

        $request = new Request('my_command', null, $input, $output, 'php://memory');

        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());
        $choice = new ChoiceTool($command, new ChoiceManager());

        return $choice;
    }

    public function makeChoiceWithStyle(){
        $choice_manager = new ChoiceManager();
        $request = new Request('my_command');
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        /** @var ChoiceStyle & DoubleStub $style */
        $style = Double::mock(ChoiceStyle::class)->getInstance();

        /** @var ChoiceTool & DoubleStub $choice */
        $choice = Double::mock(ChoiceTool::class)->getInstance($command, $choice_manager);
        $choice::_method('getStyle')->return($style);

        return [$choice, $style];
    }

    public function testDisplay(){
        $choice =$this->makeTtyChoice();

        $choice
            ->setTitleFormat('-- %title% --')
            ->setPromptFormat('%prompt% > ')
            ->setChoiceFormat('(%key%)', '-> %value%')
            ->setQuitFormat('(%key%)', '-> %value%')
            ->setTitle('My title')
            ->setPrompt('My prompt')
            ->setChoices([1 => 'choice1', 2 => 'choice2'])
            ->setQuitMessage('l', 'leave')
            ->enableQuit()
            ->display();

        rewind($choice->getOutput()->getHandle());

        $this->assertEquals("-- My title --
(1) -> choice1
(2) -> choice2
(l) -> leave  
My prompt > ",implode('',$choice->getOutput()->getBuffer()));
    }

    public function testDisplayTwice(){
        $choice =$this->makeTtyChoice();

        $choice
            ->setChoices([1 => 'choice1', 2 => 'choice2'])
            ->display()
            ->display();

        rewind($choice->getOutput()->getHandle());

        $this->assertEquals('<cs color = "yellow">[1]</cs> <cs>choice1</cs>
<cs color = "yellow">[2]</cs> <cs>choice2</cs>
--------------------------------------
 ',implode('',$choice->getOutput()->getBuffer()));
    }

    public function testDisplayShouldBeEmptyWhenRequestIsNotInteractive(){
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');

        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());
        $choice = new ChoiceTool($command, new ChoiceManager());
        $choice->setChoices(['choice1', 'choice2'])
            ->display();

        rewind($choice->getOutput()->getHandle());
        $this->assertEquals('', implode('',$choice->getOutput()->getBuffer()));
    }

    public function testDisplayShouldBeEmptyWhenOutputIsNotATty(){

        $choice = $this->makeChoice();
        $choice->setChoices(['choice1', 'choice2'])
            ->display();

        rewind($choice->getOutput()->getHandle());
        $this->assertEquals('', implode('',$choice->getOutput()->getBuffer()));
    }

    public function testGetSetMaxAttempts()
    {
        $choice = $this->makeChoice();
        $choice->setMaxAttempts(3);
        $this->assertEquals(3,$choice->getMaxAttempts());
    }

    public function testEnableDisableQuit()
    {
        $choice = $this->makeChoice();
        $choice->enableQuit();
        $this->assertTrue($choice->isQuitActive());
        $choice->disableQuit();
        $this->assertFalse($choice->isQuitActive());
    }

    public function testEnableDisableMultiSelect()
    {
        $choice = $this->makeChoice();
        $choice->enableMultiSelect();
        $this->assertTrue($choice->isMultiSelectActive());
        $choice->disableMultiSelect();
        $this->assertFalse($choice->isMultiSelectActive());
    }


    public function testGetSetTitle()
    {
        $choice = $this->makeChoice();
        $choice->setTitle('title');
        $this->assertEquals('title',$choice->getTitle());
    }

    public function testGetSetPrompt()
    {
        $choice = $this->makeChoice();
        $choice->setPrompt('prompt');
        $this->assertEquals('prompt',$choice->getPrompt());
    }

    public function testGetSetDefault()
    {
        $choice = $this->makeChoice();
        $choice->setDefault('default');

        $this->assertEquals('default', $choice->getDefault());
    }


    public function testGetSetAddChoices()
    {
        $choice = $this->makeChoice();
        $choice->setChoices(['choice1', 'choice2'])
            ->addChoice('choice3');

        $this->assertEquals(['choice1', 'choice2', 'choice3'], $choice->getAllChoices());
        $this->assertEquals('choice1', $choice->getChoice(0));
        $this->assertNull($choice->getChoice(3));
    }

    public function testSetAddChoiceVerbosity(){
        $choice = $this->makeChoice();
        $choice->setChoices(['choice1', 'choice2'], Command::VERBOSITY_QUIET);
        $choice->addChoice('choice3', Command::VERBOSITY_QUIET);

        $this->assertEquals([], $choice->getAllChoices());
    }

    public function testSetUndefinedStyleShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $choice = $this->makeChoice();
        $choice->setStyle('undefined');
    }

    public function testIsPlaced()
    {
        $choice = $this->makeChoice();
        $choice->placeHere();

        $this->assertTrue($choice->isPlaced());
    }

    public function testAsk()
    {
        $choice = $this->makeTtyChoice();
        $choice->setChoices(['choice1', 'choice2']);

        $input = $choice->getInput();

        fwrite($input->getHandle(), 1);
        rewind($input->getHandle());
        $response = $choice->ask();

        $this->assertEquals(1, $response);
    }

    public function testAskQuit()
    {
        $choice = $this->makeTtyChoice();
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->enableQuit();

        $input = $choice->getInput();

        fwrite($input->getHandle(), 'q');
        rewind($input->getHandle());
        $response = $choice->ask();

        $this->assertNull($response);
    }

    public function testAskError()
    {
        $choice = $this->makeTtyChoice(['3',"\n",'0 (choice1)',"\n"]);
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->setErrorMessage('%input% Error message')
            ->enableQuit();

        $output = $choice->getOutput();
        $response = $choice->ask();

        $this->assertEquals('0', $response);
        rewind($output->getHandle());
        $this->assertEquals('<cs color = "yellow">[0]</cs> <cs>choice1</cs>  
<cs color = "yellow">[1]</cs> <cs>choice2</cs>  
<cs color = "light_grey">[q]</cs> <cs color = "light_grey">Quit/Exit</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white">3</cs> <error>[3 Error message]</error>

<cs color = "yellow">[0]</cs> <cs>choice1</cs>  
<cs color = "yellow">[1]</cs> <cs>choice2</cs>  
<cs color = "light_grey">[q]</cs> <cs color = "light_grey">Quit/Exit</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white">0 (choice1)</cs>',implode('',$output->getBuffer()));
    }

    public function testAskPlacedError()
    {
        $choice = $this->makeTtyChoice(['3', "\n", '0 (choice1)', "\n"]);
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->placeHere()
            ->setErrorMessage('%input% Error message')
            ->enableQuit();

        $output = $choice->getOutput();

        $response = $choice->ask();

        $this->assertEquals('0', $response);
        $this->assertEquals('<cs color = "yellow">[0]</cs> <cs>choice1</cs>  
<cs color = "yellow">[1]</cs> <cs>choice2</cs>  
<cs color = "light_grey">[q]</cs> <cs color = "light_grey">Quit/Exit</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white">0 (choice1)</cs>',implode('',$output->getBuffer()));
    }

    public function testAskDefault()
    {
        $choice = $this->makeTtyChoice(["\n"]);
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->setDefault(0);

        $output = $choice->getOutput();

        $response = $choice->ask();

        $this->assertEquals('0', $response);
        rewind($output->getHandle());
        $this->assertEquals('<cs color = "yellow">[0]</cs> <cs>choice1</cs>
<cs color = "yellow">[1]</cs> <cs>choice2</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white"></cs>',implode('',$output->getBuffer()));
    }

    public function testAskPlaced()
    {
        $choice = $this->makeTtyChoice(["0", "\n", "1", "\n"]);
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->placeHere();

        $output = $choice->getOutput();

        $response1 = $choice->ask();
        $response2 = $choice->ask();

        $this->assertEquals(0, $response1);
        $this->assertEquals(1, $response2);

        rewind($output->getHandle());
        $this->assertEquals('<cs color = "yellow">[0]</cs> <cs>choice1</cs>
<cs color = "yellow">[1]</cs> <cs>choice2</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white">1 (choice2)</cs>',implode('', $output->getBuffer()));
    }

    public function testAskMaxAttempts()
    {
        $choice = $this->makeTtyChoice();
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->setMaxAttempts(2);

        $input = $choice->getInput();

        fwrite($input->getHandle(), 'a'."\n".'b'."\n".'c');
        rewind($input->getHandle());
        $response = $choice->ask();

        $this->assertFalse($response);
    }
    public function testAskMultiSelect()
    {
        $choice = $this->makeTtyChoice();
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->enableMultiSelect();

        $input = $choice->getInput();

        fwrite($input->getHandle(), '0,1 (choice2)');
        rewind($input->getHandle());
        $response = $choice->ask();

        $this->assertEquals([0,1], $response);
    }

    public function testAskMultiSelectQuit()
    {
        $choice = $this->makeTtyChoice();
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->enableQuit()
            ->enableMultiSelect();

        $input = $choice->getInput();

        fwrite($input->getHandle(), 'q');
        rewind($input->getHandle());
        $response = $choice->ask();

        $this->assertNull($response);
    }

    public function testAskMultiSelectError()
    {
        $choice = $this->makeTtyChoice();
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->enableQuit()
            ->enableMultiSelect()
            ->setErrorMessage('%input% Error message');

        $input = $choice->getInput();
        $output = $choice->getOutput();

        fwrite($input->getHandle(), '0,q'."\n".'3'."\n".'0, 1 (choice2)');
        rewind($input->getHandle());
        $response = $choice->ask();

        $this->assertEquals([0,1], $response);
        rewind($output->getHandle());
        $this->assertEquals('<cs color = "yellow">[0]</cs> <cs>choice1</cs>  
<cs color = "yellow">[1]</cs> <cs>choice2</cs>  
<cs color = "light_grey">[q]</cs> <cs color = "light_grey">Quit/Exit</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white">0,q</cs> <error>[q Error message]</error>

<cs color = "yellow">[0]</cs> <cs>choice1</cs>  
<cs color = "yellow">[1]</cs> <cs>choice2</cs>  
<cs color = "light_grey">[q]</cs> <cs color = "light_grey">Quit/Exit</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white">3</cs> <error>[3 Error message]</error>

<cs color = "yellow">[0]</cs> <cs>choice1</cs>  
<cs color = "yellow">[1]</cs> <cs>choice2</cs>  
<cs color = "light_grey">[q]</cs> <cs color = "light_grey">Quit/Exit</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white">0, 1 (choice2)</cs>',implode('',$output->getBuffer()));
    }

    public function testAskMultiSelectDefault()
    {
        $choice = $this->makeTtyChoice();
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->enableMultiSelect()
            ->setDefault(0);

        $input = $choice->getInput();
        $output = $choice->getOutput();

        fwrite($input->getHandle(), "\n");
        rewind($input->getHandle());
        $response = $choice->ask();

        $this->assertEquals([0], $response);
        rewind($output->getHandle());
        $this->assertEquals('<cs color = "yellow">[0]</cs> <cs>choice1</cs>
<cs color = "yellow">[1]</cs> <cs>choice2</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white"></cs>',implode('',$output->getBuffer()));
    }


    public function testAskShouldReturnNullAndNotDisplayWithoutTty()
    {
        $choice = $this->makeChoice();
        $response = $choice->setChoices(['choice1', 'choice2'])->display()->ask();
        $output = $choice->getOutput();
        rewind($output->getHandle());

        $this->assertEquals('',implode('',$output->getBuffer()));
        $this->assertNull($response);
    }

    public function testAskVerbosity(){
        $choice = $this->makeTtyChoice();
        $response = $choice->setChoices(['choice1', 'choice2'])->display(Command::VERBOSITY_VERBOSE)->ask();
        $output = $choice->getOutput();
        rewind($output->getHandle());

        $this->assertEquals('',implode('',$output->getBuffer()));
        $this->assertNull($response);
    }

    public function testChoiceShouldUseErrorOutputWhenStandardOutputIsNotATty(){
        $input = Double::mock(Input::class)->getInstance('php://temp');
        $input::_method('isatty')->return(true);

        $error_output = Double::mock(Output::class)->getInstance('php://memory');
        $error_output::_method('isatty')->return(true);

        $request = new Request('my_command', null, $input, 'php://memory', $error_output);

        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());
        $choice = new ChoiceTool($command, new ChoiceManager());

        $this->assertTrue($choice->isUsingErrorOutput());
    }

    public function testChoiceShouldNotBeDisplayableWithoutTtyOutput(){
        $choice = $this->makeChoice();
        $this->assertFalse($choice->isDisplayable());
    }
    public function testChoiceShouldNotBeDisplayableWithoutInteractiveRequest(){
        $choice = $this->makeChoice();
        $this->assertFalse($choice->isDisplayable());
    }
    public function testChoiceIsDisplayedOnNewLine(){
        $choice = $this->makeTtyChoice();

        $input = $choice->getInput();
        $output = $choice->getOutput();

        fwrite($input->getHandle(), "0\n");
        rewind($input->getHandle());

        $output->write('hello');
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->ask();

        rewind($output->getHandle());
        $this->assertEquals('hello
<cs color = "yellow">[0]</cs> <cs>choice1</cs>
<cs color = "yellow">[1]</cs> <cs>choice2</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white">0 (choice1)</cs>', implode('',$output->getBuffer()));
    }

    public function testPlacedChoiceIsDisplayedOnNewLine(){
        $choice = $this->makeTtyChoice();
        $input = $choice->getInput();
        $output = $choice->getOutput();

        fwrite($input->getHandle(), "0\n");
        rewind($input->getHandle());

        $output->write('hello');
        $choice
            ->setChoices(['choice1', 'choice2'])
            ->placeHere()
            ->ask();

        rewind($output->getHandle());
        $this->assertEquals('hello
<cs color = "yellow">[0]</cs> <cs>choice1</cs>
<cs color = "yellow">[1]</cs> <cs>choice2</cs>
--------------------------------------
 <cs background-color="dark_grey" color="white">0 (choice1)</cs>', implode('',$output->getBuffer()));
    }

    /*
   * Test style
   */
    public function testGetTitleFormat()
    {
        /**
         * @var ChoiceTool & DoubleStub $choice
         * @var ChoiceStyle & DoubleStub $style
         */
        list($choice, $style) = $this->makeChoiceWithStyle();

        $style::_method('setTitleFormat')->count(1)->args(['format']);
        $style::_method('getTitleFormat')->count(1);
        $choice->setTitleFormat('format');
        $this->assertEquals('format', $choice->getTitleFormat());
    }

    public function testGetSetErrorFormat()
    {
        /**
         * @var ChoiceTool & DoubleStub $choice
         * @var ChoiceStyle & DoubleStub $style
         */
        list($choice, $style) = $this->makeChoiceWithStyle();

        $style::_method('setErrorFormat')->count(1)->args(['format']);
        $style::_method('getErrorFormat')->count(1);
        $choice->setErrorFormat('format');
        $this->assertEquals('format', $choice->getErrorFormat());
    }

    public function testGetSetPromptFormat()
    {
        /**
         * @var ChoiceTool & DoubleStub $choice
         * @var ChoiceStyle & DoubleStub $style
         */
        list($choice, $style) = $this->makeChoiceWithStyle();

        $style::_method('setPromptFormat')->count(1)->args(['format']);
        $style::_method('getPromptFormat')->count(1);
        $choice->setPromptFormat('format');
        $this->assertEquals('format', $choice->getPromptFormat());
    }

    public function testGetSetAutoCompleteFormat()
    {
        /**
         * @var ChoiceTool & DoubleStub $choice
         * @var ChoiceStyle & DoubleStub $style
         */
        list($choice, $style) = $this->makeChoiceWithStyle();

        $style::_method('setAutoCompleteFormat')->count(1)->args(['format']);
        $style::_method('getAutoCompleteFormat')->count(1);
        $choice->setAutoCompleteFormat('format');
        $this->assertEquals('format', $choice->getAutoCompleteFormat());
    }

    public function testGetSetQuitFormat()
    {
        /**
         * @var ChoiceTool & DoubleStub $choice
         * @var ChoiceStyle & DoubleStub $style
         */
        list($choice, $style) = $this->makeChoiceWithStyle();

        $style::_method('setQuitFormat')->count(1)->args(['key', 'value']);
        $style::_method('getQuitFormat')->count(1);
        $choice->setQuitFormat('key', 'value');
        $this->assertEquals(['key' => 'key', 'value' => 'value'], $choice->getQuitFormat());
    }

    public function testGetSetChoiceFormat()
    {
        /**
         * @var ChoiceTool & DoubleStub $choice
         * @var ChoiceStyle & DoubleStub $style
         */
        list($choice, $style) = $this->makeChoiceWithStyle();

        $style::_method('setChoiceFormat')->count(1)->args(['key', 'value']);
        $style::_method('getChoiceFormat')->count(1);
        $choice->setChoiceFormat('key', 'value');
        $this->assertEquals(['key' => 'key', 'value' => 'value'], $choice->getChoiceFormat());
    }


    public function testGetSetErrorMessage()
    {
        /**
         * @var ChoiceTool & DoubleStub $choice
         * @var ChoiceStyle & DoubleStub $style
         */
        list($choice, $style) = $this->makeChoiceWithStyle();

        $style::_method('setErrorMessage')->count(1)->args(['message']);
        $style::_method('getErrorMessage')->count(1);
        $choice->setErrorMessage('message');
        $this->assertEquals('message', $choice->getErrorMessage());
    }

    public function testGetSetQuitMessage()
    {
        /**
         * @var ChoiceTool & DoubleStub $choice
         * @var ChoiceStyle & DoubleStub $style
         */
        list($choice, $style) = $this->makeChoiceWithStyle();

        $style::_method('setQuitMessage')->count(1)->args(['key', 'value']);
        $style::_method('getQuitMessage')->count(1);
        $choice->setQuitMessage('key', 'value');
        $this->assertEquals(['key' => 'key', 'value' => 'value'], $choice->getQuitMessage());
    }
}
