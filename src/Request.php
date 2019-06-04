<?php

namespace SitPHP\Commands;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use SitPHP\Commands\Helpers\PhpHelper;
use SitPHP\Helpers\Text;

class Request
{

    // Internal properties
    private $stty_terms;

    // User properties
    /**
     * @var Input $input
     * @var Output $output
     * @var Output $error_output
     * @var CommandManager
    */
    private $command_name;
    private $verbosity;
    private $is_interactive;

    private $args = [];
    private $options = [];
    private $flags = [];

    /** @var Input */
    private $input;
    /** @var Output */
    private $output;
    private $error_output;
    /**
     * @var array|null
     */
    private $params = [];

    /**
     * Create a new request from global
     *
     * @return Request
     * @throws Exception
     */
    static function createFromGlobal()
    {
        if (!PhpHelper::isCli()) {
            throw new RuntimeException('Should run command in cli mode');
        }

        $params = self::resolveGlobalParams();
        $command_name = self::resolveCommandName($params);

        $input = new Input('php://stdin');
        $output = new Output('php://stdout');
        $error_output = new Output('php://stderr');

        return new self($command_name, $params, $input, $output, $error_output);
    }

    /**
     * Return global parameters
     *
     * @return mixed
     */
    protected static function resolveGlobalParams()
    {
        $argv = $_SERVER['argv'];
        array_shift($argv);
        return $argv;
    }

    /**
     * Resolve the command name from $params array and removes it
     *
     * @param array $params
     * @return mixed|string
     */
    protected static function resolveCommandName(array &$params){
        foreach($params as $key => $value){
            if($value[0] !== '-'){
                $command_name = $value;
                unset($params[$key]);
                break;
            }
        }
        if (!isset($command_name)) {
            $command_name = 'list';
        }
        return $command_name;
    }


    /**
     * Request constructor.
     *
     * @param string $command_name
     * @param array|null $params
     * @param null $input
     * @param null $output
     * @param null $error_output
     * @throws Exception
     */
    function __construct(string $command_name, array $params = null, $input = null, $output = null, $error_output = null)
    {

        // Save command name
        $this->command_name = $command_name;
        $this->params = $params;

        // Save arguments
        $argsAndOptions = self::resolveArgsAndOptions($params);
        if($argsAndOptions === null){
            throw new InvalidArgumentException('Invalid $params argument : expected array of strings');
        }
        $this->args = $argsAndOptions['args'];
        $this->options = $argsAndOptions['options'];
        $this->flags = $argsAndOptions['flags'];

        // Resolve input and output
        $input = $input ?? 'php://stdin';
        $output = $output ?? 'php://stdout';
        $error_output = $error_output ?? 'php://stderr';

        // Build input/outputs
        $input = $this->input = $this->resolveCommandInput($input);
        if($input === null){
            throw new InvalidArgumentException('Invalid $input argument : expected path (string) or instance of ' . Input::class);
        }
        $output = $this->output = $this->resolveCommandOutput($output);
        if($output === null){
            throw new InvalidArgumentException('Invalid $output argument : expected path (string) or instance of ' . Output::class);
        }
        $error_output = $this->error_output = $this->resolveCommandErrorOutput($error_output);
        if($error_output === null){
            throw new InvalidArgumentException('Invalid $error_output argument : expected path (string) or instance of ' . Output::class);
        }

        // Resolve interactive
        $this->is_interactive = $this->resolveInteractivity();

        // Check if standard output and error output should share the same buffer and cursor
        if($error_output->isatty() && $output->isatty()
            || ($output->isFile() && $error_output->isFile() && $output->getPath() ===
                $error_output->getPath())){
            $buffer_ref = &$output->getBufferRef();
            $cursor_position_ref = &$output->getCursorPositionRef();
            $error_output->setBufferRef($buffer_ref);
            $error_output->setCursorPositionRef($cursor_position_ref);
        }

        // Resolve formatting
        if($this->getOption('format')){
            $output->enableFormatting();
        } else if($this->getOption('no-format')){
            $output->disableFormatting();
        }


        // Resolve request verbosity and pass it to input and output
        $this->verbosity = $verbosity = $this->resolveVerbosity();
        $input->setVerbosity($verbosity);
        $output->setVerbosity($verbosity);
        $error_output->setVerbosity($verbosity);
    }

    /**
     * Check if request is interactive
     *
     * @return bool
     */
    function isInteractive(){
        return $this->is_interactive;
    }


    /**
     * Change stty parameters
     *
     * @param string $changes
     * @return bool
     */
    function changeStty(string $changes)
    {
        if(!$this->getInput()->isatty()){
            return false;
        }
        if ($this->stty_terms === null) {
            $response = PhpHelper::shellExec('stty -g');
            $this->stty_terms = $response[0];
        }
        PhpHelper::shellExec('stty ' . $changes);
        return true;
    }

    /**
     * Restore stty to original state
     *
     * @return bool
     */
    function restoreStty()
    {
        if(!$this->getInput()->isatty()){
            return false;
        }
        if ($this->stty_terms === null) {
            return true;
        }
        return $this->changeStty($this->stty_terms);
    }

    function isSilent()
    {
        return $this->verbosity === Command::VERBOSITY_SILENT;
    }

    /**
     * Check if request is quiet
     *
     * @return bool
     */
    function isQuiet()
    {
        return $this->verbosity === Command::VERBOSITY_QUIET;
    }

    /**
     * Check if request is verbose
     *
     * @return bool
     */
    function isVerbose()
    {
        return $this->verbosity === Command::VERBOSITY_VERBOSE;
    }

    /**
     * Check if request is debug
     *
     * @return bool
     */
    function isDebug()
    {
        return $this->verbosity === Command::VERBOSITY_DEBUG;
    }

    /**
     * Return request verbosity
     *
     * @return int
     */
    function getVerbosity()
    {
        return $this->verbosity;
    }

    /**
     * Return request command name
     *
     * @return string
     */
    function getCommandName()
    {
        return $this->command_name;
    }

    /**
     * @return array
     */
    function getParams(){
        return $this->params;
    }

    /**
     * Return request option
     *
     * @param string $name
     * @return mixed|null
     */
    function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * Return all request options
     *
     * @return array|mixed
     */
    function getAllOptions(){
        return $this->options;
    }

    /**
     * Return request flag
     *
     * @param string $name
     * @return mixed|null
     */
    function getFlag(string $name)
    {
        return $this->flags[$name] ?? null;
    }

    /**
     * Return all request flags
     *
     * @return array|mixed
     */
    function getAllFlags(){
        return $this->flags;
    }

    /**
     * Return request argument
     *
     * @param int|null $index
     * @return array|string|null
     */
    function getArg(int $index)
    {
        return $this->args[$index] ?? null;
    }

    /**
     * Return all request arguments
     *
     * @return array|mixed
     */
    function getAllArgs(){
        return $this->args;
    }

    /**
     * Returns the request input
     *
     * @return Input
     */
    function getInput()
    {
        return $this->input;
    }

    /**
     * Returns the request output
     *
     * @return Output
     */
    function getOutput()
    {
        return $this->output;
    }

    /**
     * Return request error output
     *
     * @return Output
     */
    function getErrorOutput()
    {
        return $this->error_output;
    }

    /**
     * Resolve input
     *
     * @param $input
     * @return Input
     * @throws Exception
     */
    protected function resolveCommandInput($input){
        if (is_string($input)) {
            $input_instance = new Input($input);
        } else if (is_a($input, Input::class)) {
            $input_instance = $input;
        } else {
            return null;
        }
        return $input_instance;
    }

    /**
     * Resolve output
     *
     * @param $output
     * @return Output
     * @throws Exception
     */
    protected function resolveCommandOutput($output){
        if (is_string($output)) {
            $output_instance = new Output($output);
        } else if (is_a($output, Output::class)) {
            $output_instance = $output;
        } else {
            return null;
        }
        return $output_instance;
    }

    /**
     * Resolve error-output
     *
     * @param $error_output
     * @return Output
     * @throws Exception
     */
    protected function resolveCommandErrorOutput($error_output){
        if (is_string($error_output)) {
            $error_output_instance = new Output($error_output);
        } else if (is_a($error_output, Output::class)) {
            $error_output_instance = $error_output;
        } else {
            return null;
        }
        return $error_output_instance;
    }

    /**
     * Resolve interactivity
     *
     * @return bool
     */
    protected function resolveInteractivity(){
        return $this->getOption('no-interaction') === null ? true : false;
    }

    /**
     * Resolve verbosity
     *
     * @return int
     */
    protected function resolveVerbosity()
    {
        if ($this->getOption('debug')) {
            $verbosity = Command::VERBOSITY_DEBUG;
        } else if ($this->getOption('verbose')) {
            $verbosity = Command::VERBOSITY_VERBOSE;
        } else if ($this->getOption('quiet')) {
            $verbosity = Command::VERBOSITY_QUIET;
        } else if ($this->getOption('silent')) {
            $verbosity = Command::VERBOSITY_SILENT;
        } else {
            $verbosity = Command::VERBOSITY_NORMAL;
        }
        return $verbosity;
    }


    /**
     * Resolve args and options from parameters
     *
     * @param array|null $parameters
     * @return array|null
     */
    protected static function resolveArgsAndOptions(array $parameters = null)
    {

        $parameters = $parameters ?? [];
        $arguments = [];
        $options = [];
        $flags = [];

        foreach ($parameters as $key => $parameter) {
            if(!is_string($parameter)){
                return null;
            }
            if (Text::startsWith($parameter, '--')) {
                $options_parts = self::parseOption($parameter);
                $options[$options_parts['name']] = $options_parts['value'];
            } else if (Text::startsWith($parameter, '-')) {
                $flag_parts = self::parseOption($parameter);
                $flags[$flag_parts['name']] = $flag_parts['value'];
            } else {
                $arguments[] = $parameter;
            }
        }
        return ['args' => $arguments, 'options' => $options, 'flags' => $flags];
    }

    /**
     * Parse option
     *
     * @param string $option
     * @return array
     */
    protected static function parseOption(string $option)
    {
        $option = ltrim($option, '-');
        $option_parts = explode('=', $option, 2);
        return ['name' => trim($option_parts[0]), 'value' => isset($option_parts[1]) ? trim($option_parts[1]) : true];
    }
}