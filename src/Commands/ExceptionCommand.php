<?php


namespace SitPHP\Commands\Commands;


use SitPHP\Commands\Command;
use Throwable;

class ExceptionCommand extends Command
{

    /**
     * @var Throwable
     */
    private $exception;

    function setException(Throwable $exception){
       $this->exception = $exception;
    }

   function handle(){
        if($this->exception === null){
            throw new \LogicException('Exception should be set');
        }
       $exception = $this->exception;
       $this->errorWriteLn(PHP_EOL . '<cs background-color="red" color="white"> Error </cs><error> : ' . $exception->getMessage() . '</error>' . PHP_EOL . 'in file "' . $exception->getFile() . '" at line ' . $exception->getLine());

       $stacktrace = $this->parseStacktrace($exception);
       if(!empty($stacktrace)){
           $this->displayStacktrace($stacktrace);
       }
       $this->errorLineBreak();
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

    function displayStacktrace($stacktrace){
        $this->errorWriteLn(PHP_EOL . '<cs color="yellow">Stacktrace :' . PHP_EOL . '-------------------</cs>');
        $stacktrace_table = $this->table();
        $stacktrace_table
            ->useErrorOutput()
            ->setStyle('transparent');

        if(!$this->getVerbosity() > Command::VERBOSITY_NORMAL){
            reset($stacktrace);
            $i = 0;
            do{
                $i++;
                $trace = current($stacktrace);
                $stacktrace_table->addRow(['<cs color="red">#' . $i.'</cs>', '<cs bold="true">'.$trace['call'].'</cs>' . "\n" . $trace['file'] . ' (' . $trace['line'] . ') ']);
            } while(next($stacktrace) && $i < 3);
            $stacktrace_table->display();

            if(count($stacktrace) > 3){
                $this->errorLineBreak();
                $this->errorWriteLn('<cs color="green">Run command in verbose mode (--verbose) or debug mode (--debug) to see more stacktrace.</cs>');
            }
        } else {
            foreach ($stacktrace as $i => $trace) {
                $stacktrace_table->addRow(['<cs color="red">#' . $i.'</cs>', '<cs bold="true">'.$trace['call'].'</cs>' . "\n" . $trace['file'] . ' (' . $trace['line'] . ') ']);
            }
            $stacktrace_table->display();
        }
    }
}