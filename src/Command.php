<?php


namespace SitPHP\Commands;

use Exception;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use SitPHP\Commands\Tools\Bloc\BlocTool;
use SitPHP\Commands\Tools\Choice\ChoiceTool;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarTool;
use SitPHP\Commands\Tools\Question\QuestionTool;
use SitPHP\Commands\Tools\Section\SectionTool;
use SitPHP\Commands\Tools\Table\TableTool;
use Throwable;

abstract class Command
{

    const VERBOSITY_SILENT = -2;
    const VERBOSITY_QUIET = -1;
    const VERBOSITY_NORMAL = 0;
    const VERBOSITY_VERBOSE = 1;
    const VERBOSITY_DEBUG = 2;

    // Internal properties
    private $argument_position_name = [];
    private $flags_option_name = [];

    // User properties
    private $execution_count = 0;
    private $title;
    private $command_description;
    private $options_infos = [];
    private $arguments_infos = [];
    /**
     * @var Request
     */
    private $current_request;
    /**
     * @var bool
     */
    private $is_disabled = false;
    /**
     * @var bool
     */
    private $is_hidden = false;
    /**
     * @var CommandManager
     */
    private $manager;


    /**
     * Command constructor.
     *
     */
    function __construct()
    {

        if (method_exists($this, 'prepare')) {
            $this->prepare();
        }
    }

    function setManager(CommandManager $manager){
        $this->manager = $manager;
    }

    /**
     * @return CommandManager
     */
    function getManager(){
        return $this->manager;
    }

    function hide()
    {
        $this->is_hidden = true;
    }

    function show()
    {
        $this->is_hidden = false;
    }

    function isHidden()
    {
        return $this->is_hidden;
    }

    /**
     * Set command title
     *
     * @param string $title
     */
    function setTitle(string $title)
    {
        $this->title = $title;
        @cli_set_process_title($title);
    }

    /**
     * Return command title
     *
     * @return mixed
     */
    function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the help message for the command
     *
     * @param string $message
     */
    function setDescription(string $message)
    {
        $this->command_description = $message;
    }

    /**
     * Get the help message of the command
     *
     * @return mixed
     */
    function getDescription()
    {
        return $this->command_description;
    }

    /**
     * Set argument definition
     *
     * @param string $name
     * @param int $position
     * @param string|null $description
     */
    function setArgumentInfos(string $name, int $position, string $description = null)
    {
        if ($position < 0) {
            throw new InvalidArgumentException('Invalid $position argument : expected int >= 1');
        }
        if($this->hasArgumentRegistered($name)){
            throw new InvalidArgumentException('Argument "'.$name.'" is not available');
        }
        $this->argument_position_name[$position] = $name;
        $this->arguments_infos[$name] = [
            'position' => $position,
            'description' => $description
        ];
    }

    /**
     * Return argument definition
     *
     * @param string $name
     * @return mixed|null
     */
    function getArgumentInfos(string $name)
    {
        return $this->arguments_infos[$name] ?? null;
    }

    /**
     * Return all argument definitions
     *
     * @return array
     */
    function getAllArgumentsInfos()
    {
        return $this->arguments_infos;
    }

    /**
     * Get help from argument definition
     *
     * @param string $name
     * @return mixed|null
     */
    function getArgumentDescription(string $name)
    {
        return $this->arguments_infos[$name]['description'] ?? null;
    }

    /**
     * Get position from argument definition
     *
     * @param string $name
     * @return mixed|null
     */
    function getArgumentPosition(string $name)
    {
        return $this->arguments_infos[$name]['position'] ?? null;
    }


    function hasArgumentRegistered(string $name){
        return isset($this->arguments_infos[$name]);
    }

    function hasOptionRegistered(string $name){
        return isset($this->options_infos[$name]);
    }

    function hasFlagRegistered(string $name){
        return isset($this->flags_option_name[$name]);
    }

    /**
     * Set option definition
     *
     * @param string $name
     * @param $flags
     * @param string|null $help
     */
    function setOptionInfos(string $name, $flags = null, string $help = null)
    {
        if($this->hasOptionRegistered($name)){
            throw new InvalidArgumentException('Option "'.$name.'" is not available');
        }

        // Resolve flags
        if ($flags !== null) {
            if (is_string($flags)) {
                $flags = array_map('trim', explode('|', $flags));
            } else if (is_array($flags)) {
                $flags = array_map('trim', $flags);
            } else {
                throw new InvalidArgumentException('Invalid $flag argument type : expected string of array');
            }
        } else {
            $flags = [];
        }

        foreach ($flags as $flag) {
            if($this->hasFlagRegistered($flag)){
                throw new InvalidArgumentException('Flag "'.$flag.'" is not available');
            }
        }

        // Save flag option name for commodity
        foreach ($flags as $flag) {
            $this->flags_option_name[$flag] = $name;
        }

        // Set option definition
        $this->options_infos[$name] = [
            'flags' => $flags,
            'description' => $help,
        ];
    }

    /**
     * Return option definition
     *
     * @param $name
     * @return mixed|null
     */
    function getOptionInfos($name)
    {
        return $this->options_infos[$name] ?? null;
    }

    /**
     * Return all options definitions
     *
     * @return array
     */
    function getAllOptionsInfos()
    {
        return $this->options_infos;
    }

    /**
     * Return help from option definition
     *
     * @param string $name
     * @return mixed|null
     */
    function getOptionDescription(string $name)
    {
        return $this->options_infos[$name]['description'] ?? null;
    }

    /**
     * Return flags from option definition
     *
     * @param string $name
     * @return mixed|null
     */
    function getOptionFlags(string $name)
    {
        return $this->options_infos[$name]['flags'] ?? null;
    }

    function enable()
    {
        $this->is_disabled = false;
    }

    function disable()
    {
        $this->is_disabled = true;
    }

    function isDisabled()
    {
        return $this->is_disabled;
    }

    /**
     * Execute the command with given request
     *
     * @param Request $request
     * @return int
     * @throws Exception
     */
    function execute(Request $request)
    {
        $this->methodExpectsManager(__METHOD__);
        if (!method_exists(static::class, 'handle')) {
            throw new RuntimeException('You must implement a handle method in class "' . static::class . '"');
        }
        if ($this->is_disabled) {
            return 100;
        }
        $this->validateRequest($request);
        $this->current_request = $request;

        $status_code = $this->handle() ?? 0;
        // Make sure output ends with new line
        $buffer = $this->getRequest()->getOutput()->getBuffer();
        if(!empty($buffer) && mb_substr(end($buffer), -1) !== "\n"){
            $this->lineBreak();
        }

        $this->execution_count++;
        $this->current_request = null;
        return $status_code;
    }

    /**
     * Return how many times the command was executed
     *
     * @return int
     */
    function getExecutionCount()
    {
        return $this->execution_count;
    }

    /**
     * Return request command name
     *
     * @return mixed
     */
    protected function getCommandName()
    {
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->getCommandName();
    }

    /*
     * Request
     */

    /**
     * Return current request
     *
     * @return Request
     */
    function getRequest()
    {
        return $this->current_request;
    }


    /*
     * Verbosity
     */

    /**
     * Return request verbosity
     *
     * @return int
     */
    function getVerbosity()
    {
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->getVerbosity();
    }

    /**
     * Check if request if silent
     *
     * @return bool
     */
    function isSilent()
    {
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->isSilent();
    }

    /**
     * Check if request if quiet
     *
     * @return bool
     */
    function isQuiet()
    {
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->isQuiet();
    }

    /**
     * Check if request is verbose
     *
     * @return bool
     */
    function isVerbose()
    {
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->isVerbose();
    }

    /**
     * Check if request is debug
     *
     * @return bool
     */
    function isDebug()
    {
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->isDebug();
    }

    function changeStty(string $changes){
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->changeStty($changes);
    }

    function restoreStty(){
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->restoreStty();
    }

    function isInteractive(){
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->isInteractive();
    }

    /*
     * Args and options
     */

    /**
     * Return request argument
     *
     * @param string $name
     * @return string|array|null
     */
    protected function getArgument(string $name)
    {
        $this->methodExpectsRequest(__METHOD__);
        $param_infos = $this->getArgumentInfos($name);

        if (null === $param_infos) {
            return null;
        }
        $arg = $this->getRequest()->getArg($param_infos['position']);
        return $this->getRequest()->getArg($param_infos['position']);
    }

    /**
     * Return all request arguments
     *
     * @return array
     */
    protected function getAllArgs()
    {
        $this->methodExpectsRequest(__METHOD__);
        $params = [];
        foreach ($this->getRequest()->getAllArgs() as $index => $value) {
            if (isset($this->argument_position_name[$index])) {
                $param_name = $this->argument_position_name[$index];
                $params[$param_name] = $value;
            }
        }
        return $params;
    }

    /**
     * Return request option
     *
     * @param string $label
     * @return mixed|null
     */
    protected function getOption(string $label)
    {
        $this->methodExpectsRequest(__METHOD__);
        // Look for option in request options
        $option_value = $this->getRequest()->getOption($label);
        if ($option_value !== null) {
            return $option_value;
        }

        // Look for option in request flags
        $option_definition = $this->getOptionInfos($label);
        if (null === $option_definition) {
            return null;
        }
        foreach ($option_definition['flags'] as $flag) {
            $flag_value = $this->getRequest()->getFlag($flag);
            if ($flag_value !== null) {
                return $flag_value;
            }
        }
        return null;
    }

    /**
     * Return all request options
     *
     * @return array|mixed
     */
    protected function getAllOptions()
    {
        $this->methodExpectsRequest(__METHOD__);
        $options = $this->getRequest()->getAllOptions();
        foreach ($this->getRequest()->getAllFlags() as $name => $value) {
            $options[$this->flags_option_name[$name]] = $value;
        }
        return $options;
    }

    /**
     * Return request flag
     *
     * @param string $name
     * @return mixed|null
     */
    protected function getFlag(string $name)
    {
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->getFlag($name);
    }

    /**
     * Return all request flags
     *
     * @return array|mixed
     */
    protected function getAllFlags()
    {
        $this->methodExpectsRequest(__METHOD__);
        return $this->getRequest()->getAllFlags();
    }

    /**
     * Runs another command
     *
     * @param $command_name
     * @param array|null $params
     * @return int
     * @throws Exception
     * @throws Throwable
     */
    protected function call(string $command_name, array $params = null)
    {
        $this->methodExpectsManager(__METHOD__);
        $this->methodExpectsRequest(__METHOD__);
        $request = $this->getRequest();
        return $this->getManager()->call($command_name, $params, $request->getInput(), $request->getOutput(), $request->getErrorOutput());
    }

    /**
     * Writes message to output to standard output
     *
     * @param $message
     * @param int $verbosity
     * @param int|null $width
     * @param bool|null $escape_tags
     * @return bool
     * @throws Exception
     */
    protected function write($message, int $verbosity = null, int $width = null, bool $escape_tags = null)
    {
        return $this->getRequest()->getOutput()->write($message, $verbosity, $width, $escape_tags);
    }

    /**
     * Writes a message and new line to standard output
     *
     * @param $message
     * @param int $verbosity
     * @param int|null $width
     * @param bool|null $escape_tags
     * @return bool
     * @throws Exception
     */
    protected function writeLn($message = '', int $verbosity = null, int $width = null, bool $escape_tags = null)
    {
        return $this->getRequest()->getOutput()->writeLn($message, $verbosity, $width, $escape_tags);
    }


    protected function lineBreak(int $count = 1, int $verbosity = null){
        return $this->getRequest()->getOutput()->lineBreak($count, $verbosity);
    }

    /**
     * Write message to error output
     *
     * @param $message
     * @param null $verbosity
     * @param int|null $width
     * @param bool|null $escape_tags
     * @return bool
     * @throws Exception
     */
    protected function errorWrite($message, $verbosity = null, int $width = null, bool $escape_tags = null)
    {
        return $this->getRequest()->getErrorOutput()->write($message, $verbosity, $width, $escape_tags);
    }

    /**
     * Write message with new line to error output
     *
     * @param $message
     * @param null $verbosity
     * @param int|null $width
     * @param bool|null $escape_tags
     * @return bool
     * @throws Exception
     */
    protected function errorWriteLn($message, $verbosity = null, int $width = null, bool $escape_tags = null)
    {
        return $this->getRequest()->getErrorOutput()->writeLn($message, $verbosity, $width, $escape_tags);
    }

    protected function errorLineBreak(int $count = 1, int $verbosity = null){
        return $this->getRequest()->getErrorOutput()->lineBreak($count, $verbosity);
    }


    /**
     * Return a new question instance
     *
     * @param $prompt
     * @param array|null $autocomplete
     * @return QuestionTool
     * @throws Exception
     */
    protected function question($prompt = null, $autocomplete = null)
    {
        /** @var QuestionTool $question */
        $question = $this->tool('question');
        if ($prompt !== null) {
            $question->setPrompt($prompt);
        }
        if ($autocomplete !== null) {
            $question->setAutoComplete($autocomplete);
        }
        return $question;
    }

    /**
     * Return a new secret question
     *
     * @param null $prompt
     * @return QuestionTool
     * @throws Exception
     */
    protected function secret($prompt = null)
    {
        /** @var QuestionTool $secret */
        $secret = $this->tool('question');
        $secret->enableSecretTyping();
        if ($prompt !== null) {
            $secret->setPrompt($prompt);
        }

        return $secret;
    }

    /**
     * Returns a new progress bar instance
     *
     * @param int $steps
     * @return ProgressBarTool
     * @throws Exception
     */
    protected function progressBar(int $steps)
    {
        /* @var ProgressBarTool $progress_bar */
        $progress_bar = $this->tool('progress_bar', $steps);
        return $progress_bar;
    }

    /**
     * Returns a new table instance
     *
     * @param array $rows
     * @return TableTool
     * @throws Exception
     */
    protected function table(array $rows = null)
    {
        /* @var TableTool $table */
        $table = $this->tool('table');
        if (null !== $rows) {
            $table->setRows($rows);
        }
        return $table;
    }

    /**
     * Return a new marker instance
     *
     * @return SectionTool
     * @throws Exception
     */
    protected function section()
    {
        /* @var SectionTool $section */
        $section = $this->tool('section');
        return $section;
    }

    /**
     * Return a new choice instance
     *
     * @param array $choices
     * @param string $prompt
     * @param string|null $title
     * @return ChoiceTool
     * @throws Exception
     */
    protected function choice(array $choices = null, string $prompt = null, string $title = null)
    {
        /* @var ChoiceTool $choice */
        $choice = $this->tool('choice');
        if ($choices !== null) {
            $choice->setChoices($choices);
        }
        if ($prompt !== null) {
            $choice->setPrompt($prompt);
        }
        if ($title !== null) {
            $choice->setTitle($title);
        }
        return $choice;
    }

    /**
     * Return a new bloc instance
     *
     * @param string $content
     * @return BlocTool
     * @throws Exception
     */
    protected function bloc(string $content = null)
    {
        /* @var BlocTool $bloc */
        $bloc = $this->tool('bloc');
        if ($content !== null) {
            $bloc->setContent($content);
        }
        return $bloc;
    }


    /**
     * Return a new tool instance
     *
     * @param string $name
     * @param mixed ...$build_params
     * @return Tool
     */
    function tool(string $name, ...$build_params){
        $this->methodExpectsManager(__METHOD__);
        $this->methodExpectsRequest(__METHOD__);
        $tool_manager = $this->getManager()->getToolManager($name);
        if($tool_manager === null){
            throw new InvalidArgumentException('Undefined tool '.$name);
        }
        return $tool_manager->make($this, ...$build_params);
    }


    /**
     * Validate options and flags
     *
     * @param Request $request
     * @throws Exception
     */
    protected function validateRequest(Request $request)
    {
        $args = $request->getAllArgs();
        foreach ($args as $index => $value) {
            if (!isset($this->argument_position_name[$index])) {
                throw new InvalidArgumentException('Unexpected command argument "' . $value . '"');
            }
        }

        $options = $request->getAllOptions();
        foreach ($options as $label => $value) {
            if ($this->getOptionInfos($label) === null) {
                throw new InvalidArgumentException('Unexpected command option "' . $label . '"');
            }
        }

        $flags = $request->getAllFlags();
        foreach ($flags as $label => $value) {
            if (!isset($this->flags_option_name[$label])) {
                throw new InvalidArgumentException('Unexpected command flag "' . $label . '"');
            }
        }
    }

    protected function methodExpectsRequest($method)
    {
        if (null === $this->getRequest()) {
            throw new LogicException('The "' . $method . '" method is only available during request execution');
        }
    }
    protected function methodExpectsManager($method)
    {
        if (null === $this->getManager()) {
            throw new LogicException('The "' . $method . '" method requires the manager to be set');
        }
    }

}