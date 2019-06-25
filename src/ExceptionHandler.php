<?php

namespace SitPHP\Commands;

use InvalidArgumentException;
use SitPHP\Commands\Tools\Table\TableTool;
use Throwable;

class ExceptionHandler
{
    /**
     * @param CommandManager $command_manager
     * @param Request $request
     * @param Throwable $exception
     * @throws Throwable
     */
    function handleForConsole(CommandManager $command_manager, Request $request, Throwable $exception)
    {
        $error_output = $request->getErrorOutput();
        $error_output->writeLn(PHP_EOL . '<cs background-color="red" color="white"> Error </cs><error> : ' . $exception->getMessage() . '</error>' . PHP_EOL . 'in file "' . $exception->getFile() . '" at line ' . $exception->getLine());

        $stacktrace = $this->parseStacktrace($exception);
        if(!empty($stacktrace)){
            $this->displayStacktrace($command_manager, $request, $stacktrace);
        }
        $error_output->lineBreak();

    }

    protected function parseStacktrace(Throwable $e)
    {
        $parsed = [];
        $stacktrace = $e->getTrace();
        foreach ($stacktrace as $trace) {
            $args = [];
            if (!empty($trace['args'])) {
                foreach ($trace['args'] as $a) {
                    switch (gettype($a)) {
                        case 'integer':
                        case 'double':
                            $args[] = $a;
                            break;
                        case 'string':
                            $a = htmlspecialchars(substr($a, 0, 64)) . ((strlen($a) > 64) ? '...' : '');
                            $args[] = "\"$a\"";
                            break;
                        case 'array':
                            $args[] = 'Array(' . count($a) . ')';
                            break;
                        case 'object':
                            $args[] = 'Object(' . get_class($a) . ')';
                            break;
                        case 'resource':
                            $args[] = 'Resource(' . get_resource_type($a) . ')';
                            break;
                        case 'boolean':
                            $args[] = $a ? 'True' : 'False';
                            break;
                        case 'NULL':
                            $args[] = 'Null';
                            break;
                        default:
                            $args[] = 'Unknown';
                    }
                }
            }

            $parsed[] = [
                'file' => $trace['file'] ?? 'Internal function',
                'line' => $trace['line'] ?? NULL,
                'call' => isset($trace['class'], $trace['type']) ? $trace['class'].$trace['type'].$trace['function'] : $trace['function'],
                'args' => $args,
            ];
        }
        return $parsed;
    }

    protected function displayStacktrace(CommandManager $command_manager, Request $request, $stacktrace){
        $error_output = $request->getErrorOutput();
        $error_output->writeLn(PHP_EOL . '<cs color="yellow">Stacktrace :' . PHP_EOL . '-------------------</cs>');
        /** @var TableTool $stacktrace_table */
        $stacktrace_table = $this->tool($command_manager, $request, 'table');
        $stacktrace_table
            ->useErrorOutput()
            ->setStyle('transparent');

        if(!$request->getVerbosity() > Command::VERBOSITY_NORMAL){
            reset($stacktrace);
            $i = 0;
            do{
                $i++;
                $trace = current($stacktrace);
                $stacktrace_table->addRow(['<cs color="red">#' . $i.'</cs>', '<cs bold="true">'.$trace['call'].'</cs>' . "\n" . $trace['file'] . ' (' . $trace['line'] . ') ']);
            } while(next($stacktrace) && $i < 3);
            $stacktrace_table->display();

            if(count($stacktrace) > 3){
                $error_output->lineBreak();
                $error_output->writeLn('<cs color="green">Run command in verbose mode (--verbose) or debug mode (--debug) to see more stacktrace.</cs>');
            }
        } else {
            foreach ($stacktrace as $i => $trace) {
                $stacktrace_table->addRow(['<cs color="red">#' . $i.'</cs>', '<cs bold="true">'.$trace['call'].'</cs>' . "\n" . $trace['file'] . ' (' . $trace['line'] . ') ']);
            }
            $stacktrace_table->display();
        }
    }

    protected function tool(CommandManager $command_manager, Request $request, string $name, ...$build_params){
        $tool_manager = $command_manager->getToolManager($name);
        if($tool_manager === null){
            throw new InvalidArgumentException('Undefined tool "'.$name.'"');
        }
        $command = $command_manager->resolveCommand($request->getCommandName());
        if(!$command){
            throw new InvalidArgumentException('Undefined request command "'.$request->getCommandName().'"');
        }
        return $tool_manager->make($command, ...$build_params);
    }
}