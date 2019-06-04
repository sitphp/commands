<?php

namespace SitPHP\Commands\Tests\Tools;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use InvalidArgumentException;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Input;
use SitPHP\Commands\Output;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Question\QuestionManager;
use SitPHP\Commands\Tools\Question\QuestionStyle;
use SitPHP\Commands\Tools\Question\QuestionTool;


class QuestionToolTest extends TestCase
{

    public function makeQuestion()
    {
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        $question = new QuestionTool($command, new QuestionManager());

        return $question;
    }

    public function makeQuestionWithStyle()
    {
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        /** @var QuestionStyle & DoubleStub $style */
        $style = Double::mock(QuestionStyle::class)->getInstance();

        /** @var QuestionTool & DoubleStub $choice */
        $question = Double::mock(QuestionTool::class)->getInstance($command, new QuestionManager());
        $question::_method('getStyle')->return($style);


        return [$question, $style];
    }

    public function makeTtyQuestion(array $input_chars = null){
        $input = Double::mock(Input::class)->getInstance('php://temp');
        $input::_method('isatty')->return('true');
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
        $output::_method('isatty')->return('true');

        $request = new Request('my_command', null, $input, $output);

        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        $question = new QuestionTool($command, new QuestionManager());

        return $question;
    }

    public function testGetSetPrompt()
    {
        $question = $this->makeQuestion();
        $question->setPrompt('prompt');
        $this->assertEquals('prompt', $question->getPrompt());
    }

    public function testGetSetPromptFormat()
    {
        /**
         * @var QuestionTool & DoubleStub $choice
         * @var QuestionStyle & DoubleStub $style
         */
        list($question, $style) = $this->makeQuestionWithStyle();

        $style::_method('setPromptFormat')->count(1)->args(['format']);
        $style::_method('getPromptFormat')->count(1);

        $question->setPromptFormat('format');
        $this->assertEquals('format', $question->getPromptFormat());
    }

    public function testGetSetPlaceholderFormat()
    {
        /**
         * @var QuestionTool & DoubleStub $choice
         * @var QuestionStyle & DoubleStub $style
         */
        list($question, $style) = $this->makeQuestionWithStyle();

        $style::_method('setPlaceholderFormat')->count(1)->args(['format']);
        $style::_method('getPlaceholderFormat')->count(1);

        $question->setPlaceholderFormat('format');
        $this->assertEquals('format', $question->getPlaceholderFormat());
    }

    public function testGetSetAutoComplete()
    {
        $question = $this->makeQuestion();
        $autocomplete = ['item', 'items'];
        $question->setAutoComplete($autocomplete);
        $this->assertEquals($autocomplete,$question->getAutoComplete());
        $question->setAutoComplete(function (){
            return 'autocomplete';
        });
        $callback = $question->getAutoComplete();
        $this->assertEquals('autocomplete', $callback());
    }
    public function testSetAutoCompleteWithInvalidArgumentShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $question = $this->makeQuestion();
        $question->setAutoComplete(new \stdClass());
    }

    public function testGetSetAutocompleteFormat()
    {
        /**
         * @var QuestionTool & DoubleStub $choice
         * @var QuestionStyle & DoubleStub $style
         */
        list($question, $style) = $this->makeQuestionWithStyle();

        $style::_method('setAutocompleteFormat')->count(1)->args(['format']);
        $style::_method('getAutocompleteFormat')->count(1);

        $question->setAutocompleteFormat('format');
        $this->assertEquals('format', $question->getAutocompleteFormat());
    }


    public function testGetSetPlaceholder()
    {
        $question = $this->makeQuestion();
        $question->setPlaceholder('placeholder');
        $this->assertEquals('placeholder', $question->getPlaceholder());
    }

    public function testGetSetInputFormat()
    {
        /**
         * @var QuestionTool & DoubleStub $choice
         * @var QuestionStyle & DoubleStub $style
         */
        list($question, $style) = $this->makeQuestionWithStyle();

        $style::_method('setInputFormat')->count(1)->args(['format']);
        $style::_method('getInputFormat')->count(1);

        $question->setInputFormat('format');
        $this->assertEquals('format', $question->getInputFormat());
    }

    public function testIsSecretTypingActive()
    {
        $question = $this->makeQuestion();
        $question->enableSecretTyping();
        $this->assertTrue($question->isSecretTypingActive());
        $question->disableSecretTyping();
        $this->assertFalse($question->isSecretTypingActive());
    }

    public function testSetUndefinedStyleShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $question = $this->makeQuestion();
        $question->setStyle('undefined');
    }

    public function testDisplay()
    {
        $question = $this->makeTtyQuestion();
        $question->setStyle('default');
        $question->setPrompt('prompt')
            ->display();

        $output = $question->getOutput();
        rewind($output->getHandle());

        $this->assertEquals('prompt ', stream_get_contents($output->getHandle()));
    }

    public function testPlaceholderDisplay(){
        $question = $this->makeTtyQuestion();
        $question->setStyle('default');

        $input = $question->getInput();
        $output = $question->getOutput();
        fwrite($input->getHandle(), "\n");
        rewind($input->getHandle());
        $question->setPrompt('prompt')
            ->setPlaceholder('placeholder');

        $question->display();
        $this->assertEquals('prompt <cs color="dark_grey">placeholder</cs>', implode('', $output->getBuffer()));
        $question->ask();
        $this->assertEquals('prompt <cs background-color="dark_grey" color="white"></cs>', implode('', $output->getBuffer()));
    }


    public function testAskVerbosity()
    {
        $question = $this->makeTtyQuestion();
        $question->setStyle('default');
        $response = $question->setPrompt('prompt')
            ->ask(Command::VERBOSITY_VERBOSE);

        $output = $question->getOutput();
        rewind($output->getHandle());

        $this->assertNull($response);
        $this->assertEquals('', stream_get_contents($output->getHandle()));
    }

    public function testAskStandard(){
        $question = $this->makeTtyQuestion(["\177",'i','t',"\177",'t','e','m', "\n"]);
        $question->setStyle('default');
        $output = $question->getOutput();
        $response = $question->setPrompt('prompt')
            ->ask();

        $this->assertEquals('item', $response);
        $this->assertEquals('prompt <cs background-color="dark_grey" color="white">item</cs>',implode('', $output->getBuffer()));
    }

    public function testNoTtyAskStandard(){
        $question = $this->makeQuestion();

        $output = $question->getOutput();
        $input = $question->getInput();
        fwrite($input->getHandle(), 'item'."\n");
        rewind($input->getHandle());

        $response = $question->setPrompt('prompt')
            ->ask();

        rewind($output->getHandle());
        $this->assertEquals('item', $response);
    }

    public function testAskSecret(){
        $question = $this->makeTtyQuestion(["\177",'i','t',"\177",'t','e','m', "\n"]);
        $question->setStyle('default');
        $output = $question->getOutput();

        $response = $question
            ->setPrompt('prompt')
            ->enableSecretTyping()
            ->ask();

        $this->assertEquals('item', $response);
        $this->assertEquals('prompt <cs background-color="dark_grey" color="white">****</cs>',implode('', $output->getBuffer()));
    }

    public function testNoTttyAskSecret(){
        $question = $this->makeQuestion();

        $input = $question->getInput();
        fwrite($input->getHandle(), 'item'."\n");
        rewind($input->getHandle());

        $response = $question
            ->setPrompt('prompt')
            ->enableSecretTyping()
            ->ask();

        $this->assertEquals('item', $response);
    }

    public function testAskAutocomplete(){
        $question = $this->makeTtyQuestion(["\177",'i','t',"\177","\177",'t', "\n"]);
        $question->setStyle('default');
        $output = $question->getOutput();

        $response = $question->setPrompt('prompt')
            ->setAutoComplete(['item1', 'item2'])
            ->ask();

        $this->assertEquals('item1', $response);
        $this->assertEquals('prompt <cs background-color="dark_grey" color="white">item1</cs>', implode('', $output->getBuffer()));
    }

    public function testAskAutocompleteWithNonAutocompleteAnswer(){
        $question = $this->makeTtyQuestion();
        $question->setStyle('default');

        $output = $question->getOutput();
        $input = $question->getInput();
        fwrite($input->getHandle(), 'undefined'."\n");
        rewind($input->getHandle());

        $response = $question->setPrompt('prompt')
            ->setAutoComplete(['item1', 'item2'])
            ->ask();

        $this->assertEquals('undefined', $response);
        $this->assertEquals('prompt <cs background-color="dark_grey" color="white">undefined</cs>', implode('', $output->getBuffer()));
    }

    public function testAskAutocompleteWithEmptyInput(){
        $question = $this->makeTtyQuestion();
        $question->setStyle('default');
        $input = $question->getInput();
        $output = $question->getOutput();
        fwrite($input->getHandle(), "\n");
        rewind($input->getHandle());

        $response = $question->setPrompt('prompt')
            ->setAutoComplete(['item1', 'item2'])
            ->ask();

        $this->assertEquals('', $response);
        $this->assertEquals('prompt <cs background-color="dark_grey" color="white"></cs>', implode('', $output->getBuffer()));
    }

    public function testAskAutocompleteWithArrows(){
        $question = $this->makeTtyQuestion(["\033[A","\033[B","\033[B", "\n"]);
        $question->setStyle('default');
        $output = $question->getOutput();
        $response = $question->setPrompt('prompt')
            ->setAutoComplete(['item1', 'item2'])
            ->ask();

        $this->assertEquals('item1', $response);
        $this->assertEquals('prompt <cs background-color="dark_grey" color="white">item1</cs>', implode('', $output->getBuffer()));
    }

    public function testAskAutocompleteWithTab(){
        $question = $this->makeTtyQuestion(["\033[B", "\t", "\n"]);
        $question->setStyle('default');
        $output = $question->getOutput();
        $response = $question->setPrompt('prompt')
            ->setAutoComplete(['item1', 'item2'])
            ->ask();

        $this->assertEquals('item2', $response);
        $this->assertEquals('prompt <cs background-color="dark_grey" color="white">item2</cs>', implode('', $output->getBuffer()));
    }

    public function testNoTttyAskAutoComplete(){
        $question = $this->makeQuestion();

        $input = $question->getInput();
        fwrite($input->getHandle(), 'it'."\n");
        rewind($input->getHandle());

        $response = $question->setPrompt('prompt')
            ->setAutoComplete(['item1', 'item2'])
            ->ask();

        $this->assertEquals('item1', $response);
    }

    public function testPlacedDisplay()
    {
        $question = $this->makeTtyQuestion();
        $question->setStyle('default');
        $question->setPrompt('prompt')
            ->placeHere();
        $input = $question->getInput();
        $output = $question->getOutput();
        fwrite($input->getHandle(), "answer1\nanswer2\n");
        rewind($input->getHandle());

        $response1 = $question->ask();
        $response2 = $question->ask();

        $this->assertEquals('answer1', $response1);
        $this->assertEquals('answer2', $response2);

        rewind($output->getHandle());
        $this->assertEquals('prompt <cs background-color="dark_grey" color="white">answer2</cs>', implode('', $output->getBuffer()));
    }

    public function testAskNonInteractive(){


        /** @var DoubleStub & Request $request */
        $request = Double::mock(Request::class)->getInstance('my_command', null, 'php://temp', 'php://memory', 'php://memory');
        $request::_method('isInteractive')->return(false);

        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        $question = new QuestionTool($command, new QuestionManager());

        $response = $question->ask();
        $this->assertNull($response);
    }
}
