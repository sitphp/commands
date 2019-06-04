<?php

namespace SitPHP\Commands;


use Doublit\Doublit;
use Doublit\Lib\DoubleStub;
use ReflectionException;
use SitPHP\Commands\Tools\Question\QuestionTool;
use SitPHP\Commands\Tools\Question\QuestionManager;

class CommandTester
{
    private $stub_index = 0;
    private $prompt_stub_index = [];
    private $status_code;

    /**
     * @var Input
     */
    private $input;

    /**
     * @var Output
     */
    private $output;

    private $inputs = [];

    /**
     * @var CommandManager
     */
    private $command_manager;

    function __construct(CommandManager $command_manager)
    {
        $command_manager->setToolManager('question', $this->resolveQuestionManagerMock());
        $this->command_manager = $command_manager;
        $this->input = new Input('php://memory');
        $this->output =  new Output('php://memory');;
    }

    function setAnswer(string $answer, string $prompt = null){
        if(isset($prompt)){
            if(!isset($this->inputs[$prompt])){
                $this->inputs[$prompt] = [];
            }
            $this->inputs[$prompt][] = $answer;
        } else {
            $this->inputs[] = $answer;
        }
        return $this;
    }

    function call(string $command_name, array $params = null, bool $formatting = false){
        $params = $this->prepareParams($params, $formatting);
        $this->status_code = $this->command_manager->call($command_name, $params, $this->getInput(), $this->getOutput(), $this->getErrorOutput());
        rewind($this->getOutput()->getHandle());
        $response = stream_get_contents($this->getOutput()->getHandle());
        return $response;
    }

    function callCommandAs(Command $command, string $name, array $params = null, bool $formatting = false){
        $params = $this->prepareParams($params, $formatting);
        $this->status_code = $this->command_manager->callCommandAs($command, $name, $params, $this->getInput(), $this->getOutput(), $this->getErrorOutput());
        rewind($this->getOutput()->getHandle());
        $response = stream_get_contents($this->getOutput()->getHandle());
        return $response;
    }

    function getStatusCode(){
        return $this->status_code;
    }

    protected function getInput(){
        return $this->input;
    }
    protected function getOutput(){
        return $this->output;
    }
    protected function getErrorOutput(){
        return $this->output;
    }

    protected function prepareParams(array $params = null, bool $formatting = false){
        if($params === null){
            $params = [];
        }
        if($formatting){
            $params[] = '--format';
        } else {
            $params[] = '--no-format';
        }
        return $params;
    }

    protected function resolveQuestionManagerMock(){
        /** @var QuestionManager | DoubleStub $question_manager_mock */
        $question_manager_mock = Doublit::mock(QuestionManager::class)->getClass();
        $question_manager_mock::_method('make')->stub(function (Command $command, ...$params) use ($question_manager_mock)
        {
            $question_mock = $this->resolveQuestionMock();
            $manager = new $question_manager_mock($this->command_manager);
            $tool = new $question_mock($command , $manager);
            $tool->setStyle('default');

            return $tool;
        });

        return $question_manager_mock;
    }

    /**
     * @return QuestionTool
     * @throws ReflectionException
     */
    protected function resolveQuestionMock(){
        /** @var QuestionTool | DoubleStub $question_mock */
        $question_mock = Doublit::mock(QuestionTool::class)->getClass();
        $question_mock::_method('askQuestion')->stub(function(QuestionTool $question, int $verbosity = null) {

            $prompt = $question->getPrompt();
            $input = $this->resolvePromptInput($prompt);

            ftruncate($this->input->getHandle(),0);
            fwrite($this->input->getHandle(), $input.PHP_EOL);
            rewind($this->input->getHandle());

            return QuestionTool::askQuestion($question, $verbosity);
        });

        return $question_mock;
    }

    protected function resolvePromptInput(string $prompt){
        if($prompt !== null && isset($this->inputs[$prompt])){
            if(!isset($this->prompt_stub_index[$prompt])){
                $this->prompt_stub_index[$prompt] = 0;
            }
            $stub_index = $this->prompt_stub_index[$prompt];
            $input = $this->inputs[$prompt][$stub_index] ?? '';
            $this->prompt_stub_index[$prompt]++;
        } else {
            $input = $this->inputs[$this->stub_index] ?? '';
            $this->stub_index++;
        }

        return $input;
    }
}