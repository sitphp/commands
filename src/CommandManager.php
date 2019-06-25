<?php

// TODO : Add tool style instances

namespace SitPHP\Commands;

use Exception;
use Throwable;
use InvalidArgumentException;

use SitPHP\Commands\Tools\Bloc\BlocManager;
use SitPHP\Commands\Tools\Choice\ChoiceManager;
use SitPHP\Commands\Tools\ProgressBar\ProgressBarManager;
use SitPHP\Commands\Tools\Question\QuestionManager;
use SitPHP\Commands\Tools\Table\TableManager;
use SitPHP\Commands\Tools\Section\SectionManager;

use SitPHP\Commands\Commands\HelpCommand;
use SitPHP\Commands\Commands\ListCommand;
use SitPHP\Commands\Events\CommandEvent;
use SitPHP\Commands\Events\ExceptionEvent;
use SitPHP\Commands\Events\RequestEvent;

use SitPHP\Benchmarks\BenchManager;
use SitPHP\Events\EventManager;
use SitPHP\Formatters\Formatter;
use SitPHP\Formatters\FormatterManager;

class CommandManager
{

    const ON_EXCEPTION_EVENT = 'console.on_error';
    const ON_REQUEST_EVENT = 'console.on_request';
    const BEFORE_COMMAND_EVENT = 'console.before_command';
    const AFTER_COMMAND_EVENT = 'console.after_command';

    /**
     * @var Formatter $formatter
     */
    private $formatter;
    /**
     * @var FormatterManager
     */
    private $formatter_manager;

    private $commands = [];
    private $commands_def = [];
    private $command_prepared = [];
    private $command_registered = [];


    private $tools_def = [];
    private $global_options = [];
    /**
     * @var EventManager
     */
    private $event_manager;

    /**
     * @var BenchManager
     */
    private $bench_manager;
    private $tool_managers;

    /**
     * @throws Exception
     */
    function __construct()
    {
        // Register base tools
        $this->setToolManager('choice', ChoiceManager::class);
        $this->setToolManager('progress_bar', ProgressBarManager::class);
        $this->setToolManager('table', TableManager::class);
        $this->setToolManager('section', SectionManager::class);
        $this->setToolManager('question', QuestionManager::class);
        $this->setToolManager('bloc', BlocManager::class);

        // Register base options
        $this->setGlobalOption('help', 'h', 'Show this help message');
        $this->setGlobalOption('silent', null, 'Silent mode : hide all messages');
        $this->setGlobalOption('quiet', null, 'Quiet mode : show only quiet messages');
        $this->setGlobalOption('verbose', null, 'Verbose mode : show verbose messages');
        $this->setGlobalOption('debug', null, 'Debug mode : show debug messages');
        $this->setGlobalOption('format', null, 'Force output to format');
        $this->setGlobalOption('no-format', null, 'Force output not to format');
        $this->setGlobalOption('no-interaction', null, 'Hide interactive messages');

        // Register base commands
        $this->setCommand('help', HelpCommand::class);
        $this->setCommand('list', ListCommand::class);
    }


    /*
     * Command methods
     */

    /**
     * Return a new command test
     *
     * @return CommandTester
     */
    function test()
    {
        return new CommandTester($this);
    }

    /**
     * Set command
     *
     * @param string $name
     * @param $command
     * @param array|null $config
     */
    function setCommand(string $name, $command, array $config = null)
    {
        $name = strtolower($name);
        $def = $config !== null ? $config : [];
        if(is_string($command)){
            $def['class'] = $command;
            $this->commands_def[$name] = $def;
        } else if ($command instanceof Command){
            $this->prepareCommand($command, $def);
            $this->commands[$name] = $command;
        } else {
            throw new InvalidArgumentException('Invalid $command argument : expected string or instance of '.Command::class);
        }
        $this->command_registered[$name] = true;
    }


    /**
     * Return registered command instance
     *
     * @param string $name
     * @return Command|null
     */
    function getCommand(string $name)
    {
        if(!isset($this->command_registered[$name]) || !$this->command_registered[$name]){
            return null;
        }

        if(isset($this->commands[$name])){
            return $this->commands[$name];
        }
        $command = $this->makeCommand($this->commands_def[$name]);
        if($command !== null){
            $this->commands[$name] = $command;
        }
        return $command;
    }

    /**
     * Return all command instances
     *
     * @return array
     * @throws Exception
     */
    function getAllCommands()
    {
        $commands = [];
        foreach($this->command_registered as $name => $registered){
            if($registered && null !== $command = $this->getCommand($name)){
                $commands[$name] = $this->getCommand($name);
            }
        }
        return $commands;
    }

    function hasCommand(string $name){
        return isset($this->commands_def[$name]);
    }


    /**
     * Remove command
     *
     * @param string $name
     * @throws Exception
     */
    function removeCommand(string $name)
    {
        unset($this->commands_def[$name]);
        unset($this->command_registered[$name]);
        if(isset($this->commands[$name])){
            $command_id = spl_object_hash($this->commands[$name]);
            unset($this->command_registered[$command_id]);
            unset($this->commands[$name]);
        }
    }


    /**
     * Resolve command instance from command name
     *
     * @param string $name
     * @return mixed|Command|null
     */
    function resolveCommand(string $name){
        if(isset($this->commands[$name])){
            return $this->commands[$name];
        }
        if(isset($this->commands_def[$name])){
            $command = $this->makeCommand($this->commands_def[$name]);
            if($command !== null){
                $this->commands[$name] = $command;
            }
            return $command;
        }
        $command_def = [
            'class' => $this->resolveCommandClass($name)
        ];
        $command = $this->makeCommand($command_def);
        if($command !== null){
            $this->commands[$name] = $command;
        }
        return $command;
    }

    /*
     * Tool methods
     */

    /**
     * Set tool
     *
     * @param string $name
     * @param string $class
     * @throws Exception
     */
    function setToolManager(string $name, string $class)
    {
        $this->tools_def[$name] = $class;
    }

    /**
     * @param string $name
     * @return ToolManager
     */
    function getToolManager(string $name){
        if(!isset($this->tool_managers[$name])){
            $tool_class = $this->getToolManagerClass($name);
            if(!isset($tool_class)){
                return null;
            }
            if(!is_subclass_of($tool_class,ToolManager::class)){
                throw new InvalidArgumentException('Tool manager "'.$name.'" cannot be instantiated : expected subclass of '.ToolManager::class);
            }
            $this->tool_managers[$name] = new $tool_class($this);
        }
        return $this->tool_managers[$name];
    }

    /**
     * Remove a tool
     *
     * @param string $name
     */
    function removeToolManager(string $name)
    {
        unset($this->tools_def[$name]);
        unset($this->tool_managers[$name]);
    }

    function getToolManagerClass(string $name){
        return $this->tools_def[$name] ?? null;
    }

    /**
     * Return table manager
     *
     * @return TableManager
     */
    function getTableManager(){
        return $this->getToolManager('table');
    }

    /**
     * @return ProgressBarManager
     */
    function getProgressBarManager(){
        return $this->getToolManager('progress_bar');
    }

    /**
     * @return BlocManager
     */
    function getBlocManager(){
        return $this->getToolManager('bloc');
    }

    /**
     * @return QuestionManager
     */
    function getQuestionManager(){
        return $this->getToolManager('question');
    }

    /**
     * @return ChoiceManager
     */
    function getChoiceManager(){
        return $this->getToolManager('choice');
    }

    /**
     * @return SectionManager
     */
    function getSectionManager(){
        return $this->getToolManager('section');
    }


    /*
     * Global options methods
     */

    /**
     * Set global option to be
     *
     * @param string $name
     * @param $flags
     * @param string|null $description
     */
    function setGlobalOption(string $name, $flags, string $description = null)
    {
        $this->global_options[$name] = [
            'flags' => $flags,
            'description' => $description
        ];
    }

    /**
     * Return global option
     *
     * @param string $name
     * @return array|null
     * @throws Exception
     */
    function getGlobalOption(string $name)
    {
        return $this->global_options[$name] ?? null;
    }


    function getAllGlobalOptions(){
        return $this->global_options;
    }

    /**
     * Remove global option
     *
     * @param string $name
     */
    function removeGlobalOption(string $name)
    {
        unset($this->global_options[$name]);
    }



    /*
     * Run methods
     */

    /**
     * Run command from request
     *
     * @param Request $request
     * @return int
     * @throws Exception
     * @throws Throwable
     */
    function run(Request $request)
    {
        return $this->doRun($request);
    }

    /**
     * Call a command
     *
     * @param $command_name
     * @param array|null $params
     * @param null $input
     * @param null $output
     * @param null $error_output
     * @return int
     * @throws Exception
     * @throws Throwable
     */
    function call(string $command_name, array $params = null, $input = null, $output = null, $error_output = null)
    {
        $request = new Request($command_name, $params, $input, $output, $error_output);
        return $this->doRun($request);
    }


    /**
     * @param Command $command
     * @param string $command_name
     * @param array|null $params
     * @param null $input
     * @param null $output
     * @param null $error_output
     * @return int
     * @throws Throwable
     */
    function callCommandAs(Command $command, string $command_name, array $params = null, $input = null, $output = null, $error_output = null){
        $request = new Request($command_name, $params, $input, $output, $error_output);
        return $this->doRun($request, $command);
    }


    /**
     * @param Request $request
     * @param Command|null $command
     * @return int
     * @throws Throwable
     */
    protected function doRun(Request $request, Command $command = null){
        $event_manager = $this->getEventManager();
        try{
            // Run on request event
            $event_manager->fire(new RequestEvent(self::ON_REQUEST_EVENT), ['request' => $request]);

            // Get command
            $command_name = $request->getCommandName();
            if($command === null){
                $command = $this->resolveCommand($command_name);
                if ($command === null) {
                    throw new InvalidArgumentException('Command "' . $command_name . '" could not be resolved : make sure it exists and is subclass of ' . Command::class . '.');
                }
            } else {
                $this->prepareCommand($command);
            }


            // Run help command instead when help option is active
            if (!$command instanceof HelpCommand && ($request->getOption('help') || $request->getFlag('h'))) {
                $help_request = new Request('help', [$request->getCommandName()], $request->getInput(), $request->getOutput(), $request->getErrorOutput());
                return $this->run($help_request);
            }

            // Execute command
            $event_manager->fire(new CommandEvent(self::BEFORE_COMMAND_EVENT.'.'.$command_name), ['command' => $command]);
            $status_code = $command->execute($request);
            $event_manager->fire(new CommandEvent(self::AFTER_COMMAND_EVENT.'.'.$command_name), ['command' => $command]);

            return $status_code;
        } catch (Throwable $e){
            $event_manager->fire(new ExceptionEvent(self::ON_EXCEPTION_EVENT), ['exception' => $e, 'request' => $request]);
            throw $e;
        }
    }

    /*
     * Dependencies
     */

    /**
     * Set event manager
     *
     * @param EventManager $eventManager
     */
    function setEventManager(EventManager $eventManager)
    {
        $this->event_manager = $eventManager;
    }

    /**
     * Return event manager
     *
     * @return EventManager
     */
    function getEventManager()
    {
        if (!isset($this->event_manager)) {
            $this->event_manager = new EventManager();
        };
        return $this->event_manager;
    }

    /**
     * @param FormatterManager $formatter_manager
     */
    function setFormatterManager(FormatterManager $formatter_manager){
        $this->formatter_manager = $formatter_manager;
        $this->formatter = null;
    }

    /**
     * @return FormatterManager
     * @throws Exception
     */
    function getFormatterManager(){
        if (!isset($this->formatter_manager)) {
            $this->formatter_manager = new FormatterManager();
        };
        return $this->formatter_manager;
    }

    /**
     * Return the formatter instance
     *
     * @return Formatter
     * @throws Exception
     */
    function getFormatter()
    {
        if(!isset($this->formatter)){
            $this->formatter = $this->makeFormatter();
        }
        return $this->formatter;
    }

    function getBenchManager(){
        if(!isset($this->bench_manager)){
            $this->bench_manager = new BenchManager();
        }
        return $this->bench_manager;
    }

    function setBenchManager(BenchManager $bench_manager){
        $this->bench_manager = $bench_manager;
    }


    /*
     * Event methods
     */

    /**
     * Add listener to event throwing after request is executed
     *
     * @param $listener
     * @param int $priority
     */
    function addOnRequestListener($listener, int $priority = null)
    {
        $event_manager = $this->getEventManager();
        $event_manager->addListener(self::ON_REQUEST_EVENT, $listener, $priority);
    }


    /**
     * Add listener to event throwing before command(s) is executed
     *
     * @param $listener
     * @param int $priority
     * @param null $command_names
     */
    function addBeforeCommandListener($listener, int $priority = null, $command_names = null)
    {
        $this->addCommandListener(self::BEFORE_COMMAND_EVENT, $listener, $priority, $command_names);
    }

    /**
     * Add listener to event throwing after command(s) was executed
     *
     * @param $listener
     * @param null $command_names
     * @param int $priority
     */
    function addAfterCommandListener($listener, $command_names = null, int $priority = null)
    {
        $this->addCommandListener(self::AFTER_COMMAND_EVENT, $listener, $priority, $command_names);
    }

    function addOnExceptionListener($listener, int $priority = null){
        $this->getEventManager()->addListener(self::ON_EXCEPTION_EVENT, $listener, $priority);
    }


    /**
     * Add a listener to specified command(s) or to all commands if no command specified
     *
     * @param string $event_name
     * @param $listener
     * @param null $priority
     * @param null $command_names
     */
    protected function addCommandListener(string $event_name, $listener, $priority = null, $command_names = null)
    {
        $event_manager = $this->getEventManager();
        if (null === $command_names) {
            $event_manager->addListener($event_name, $listener, $priority);
        } else if (is_string($command_names)) {
            $event_manager->addListener($event_name . '.' . $command_names, $listener, $priority);
        } else if (is_array($command_names)) {
            foreach ($command_names as $item) {
                $event_manager->addListener($event_name . '.' . $item, $listener, $priority);
            }
        } else {
            throw new InvalidArgumentException('Invalid $command_class type : expected null, string or array');
        }
    }


    /**
     * Return command class
     *
     * @param string $command_name
     * @return string
     */
    protected function resolveCommandClass(string $command_name)
    {
        $command_parts = explode(':', $command_name, 2);
        if (isset($command_parts[1])) {
            $bundle = $this->normalizeCommandName($command_parts[0]);
            $command = $this->normalizeCommandName($command_parts[1]);
            $command_class = $bundle . '\Commands\\' . $command;
        } else {
            $command_class = $this->normalizeCommandName($command_parts[0]);
        }
        return $command_class;
    }

    /**
     * Normalize command class name
     *
     * @param $name
     * @return string
     */
    protected function normalizeCommandName($name)
    {
        $name = str_replace(['/', '-'], ['\\', ' '], $name);
        $file_parts = explode('\\', $name);
        $file_parts = array_map(function ($value){
            $value = ucwords($value);
            return str_replace(' ', '', $value);
        }, $file_parts);

        $class_name = trim(implode('\\', $file_parts));
        return $class_name;
    }


    /**
     * Make formatter
     *
     * @return Formatter
     * @throws Exception
     */
    protected function makeFormatter(){
        $formatter = $this->getFormatterManager()->formatter('cli');
        $formatter->buildTagStyle('error')->setColor('red');
        $formatter->buildTagStyle('error_strong')
            ->setBackgroundColor('red')
            ->setColor('white');
        $formatter->buildTagStyle('warning')->setColor('yellow');
        $formatter->buildTagStyle('warning_strong')
            ->setBackgroundColor('yellow')
            ->setColor('white');
        $formatter->buildTagStyle('info')->setColor('blue');
        $formatter->buildTagStyle('info_strong')
            ->setBackgroundColor('blue')
            ->setColor('white');
        $formatter->buildTagStyle('success')->setColor('green');
        $formatter->buildTagStyle('success_strong')
            ->setBackgroundColor('green')
            ->setColor('white');
        return $formatter;
    }


    /**
     * Make a command from it's definition
     *
     * @param array $command_def
     * @return Command|null
     */
    protected function makeCommand(array $command_def){
        $command_class = $command_def['class'];
        if (!is_subclass_of($command_def['class'], \SitPHP\Commands\Command::class)) {
            return null;
        }
        /** @var Command $command */
        $command = new $command_class();
        $this->prepareCommand($command, $command_def);
        return $command;
    }

    /**
     * Prepare a command
     *
     * @param Command $command
     * @param array $config
     */
    protected function prepareCommand(Command $command, array $config = []){
        if($this->isCommandPrepared($command)){
            return;
        }
        if(isset($config['hide']) && $config['hide']){
            $command->hide();
        }
        $command->setManager($this);
        foreach ($this->global_options as $name => $global_option) {
            $command->setOptionInfos($name, $global_option['flags'], $global_option['description']);
        }
        $command_id = spl_object_hash($command);
        $this->command_prepared[$command_id] = true;
    }

    /**
     * Check if command is prepared
     *
     * @param Command $command
     * @return bool
     */
    protected function isCommandPrepared(Command $command)
    {
        $command_id = spl_object_hash($command);
        return isset($this->command_prepared[$command_id]) && $this->command_prepared[$command_id];
    }
}