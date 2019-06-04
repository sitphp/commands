<?php

namespace SitPHP\Commands;

use InvalidArgumentException;

abstract class Tool
{
    /** @var Request $request */
    private $request;
    /** @var Input $input */
    private $input;
    /** @var Output $output */
    private $output;
    /**
     * @var Output
     */
    private $error_output;
    private $is_using_error_output = false;
    /**
     * @var Command
     */
    private $command;

    /**
     * CommandTool constructor.
     *
     * @param Command $command
     */
    function __construct(Command $command)
    {
        $this->command = $command;
        $request = $command->getRequest();

        if($request === null){
            throw new InvalidArgumentException('Undefined command request');
        }
        $this->request = $request;
        $this->input = $request->getInput();
        $this->output = $request->getOutput();
        $this->error_output = $request->getErrorOutput();
    }

    /**
     * @return $this
     */
    function useStandardOutput(){
        $this->output = $this->request->getOutput();
        $this->is_using_error_output = false;
        return $this;
    }

    /**
     * @return $this
     */
    function useErrorOutput(){
        $this->output = $this->error_output;
        $this->is_using_error_output = true;
        return $this;
    }

    /**
     * @return bool
     */
    function isUsingErrorOutput(){
        return $this->is_using_error_output;
    }

    /**
     * Return request
     *
     * @return Command
     */
    function getCommand(){
        return $this->command;
    }

    function getRequest(){
        return $this->command->getRequest();
    }

    /**
     * @param Input $input
     */
    function setInput(Input $input){
        $this->input = $input;
    }

    /**
     * @return Input
     */
    function getInput(){
        return $this->input;
    }

    /**
     * @param Output $output
     */
    function setOutput(Output $output){
        $this->output = $output;
    }

    /**
     * @return Output
     */
    function getOutput(){
        return $this->output;
    }

    /**
     * @param Output $output
     */
    function setErrorOutput(Output $output){
        $this->error_output = $output;
    }

    /**
     * @return Output
     */
    function getErrorOutput(){
        return $this->error_output;
    }

    /**
     * @param string $name
     * @param array|null $params
     * @return Tool
     */
    function tool(string $name, array $params = null){
        $tool = $this->command->tool($name, $params);

        // Make sure new tool uses same input/output as current tool
        $tool->setInput($this->input);
        $tool->setOutput($this->output);
        $tool->setErrorOutput($this->error_output);

        return $tool;
    }
}