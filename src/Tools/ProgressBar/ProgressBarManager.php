<?php

namespace SitPHP\Commands\Tools\ProgressBar;

use Closure;
use SitPHP\Commands\Command;
use SitPHP\Commands\ToolManager;

class ProgressBarManager extends ToolManager
{
    protected $placeholders = [];
    protected $styles = [];

    function __construct()
    {
        $this->buildStyle('default');
        $this->setPlaceholderCallback('bar', function (ProgressBarTool $progress_bar) {
            $progress_count = round($progress_bar->getProgress() * $progress_bar->getWidth() / $progress_bar->getSteps());
            $bar = '';
            for ($i = 1; $i <= $progress_count; $i++) {
                $bar .= $progress_bar->getProgressChar();
            }
            if(!empty($progress_bar->getIndicatorChar()) && $progress_bar->getProgress() !== 0 && $progress_bar->getProgress() !== $progress_bar->getSteps() && $progress_count < $progress_bar->getWidth()){
                $bar .= $progress_bar->getIndicatorChar();
                $progress_count++;
            }
            for ($i = $progress_count + 1 ; $i <= $progress_bar->getWidth(); $i++) {
                $bar .= $progress_bar->getSpaceChar();
            }
            return $bar;
        });
        $this->setPlaceholderCallback('steps', function (ProgressBarTool $progress_bar) {
            return $progress_bar->getProgress() . '/' . $progress_bar->getSteps();
        });
        $this->setPlaceholderCallback('message', function (ProgressBarTool $progress_bar) {
            return $progress_bar->getMessage() ?? '';
        });
        $this->setPlaceholderCallback('percents', function (ProgressBarTool $progress_bar) {
            return intval($progress_bar->getProgress() * 100 / $progress_bar->getSteps()) . '%';
        });
        $this->setPlaceholderCallback('elapsed', function (ProgressBarTool $progress_bar) {
            return $progress_bar->getElapsedTime();
        });
        $this->setPlaceholderCallback('memory', function (ProgressBarTool $progress_bar) {
            return $progress_bar->getCommand()->getManager()->getBenchManager()->getMemoryUsage();
        });

    }

    function make(Command $command, ...$params)
    {
        $steps = $params[0] ?? null;
        return new ProgressBarTool($command, $this, $steps);
    }

    function setPlaceholderCallback(string $name, Closure $callback)
    {
        $this->placeholders[$name] = $callback;
    }

    function getPlaceholderCallback($name){
        return $this->placeholders[$name] ?? null;
    }

    function getAllPlaceholderCallbacks(){
        return $this->placeholders;
    }

    function hasPlaceholderCallback(string $name){
        return isset($this->placeholders[$name]);
    }

    function buildStyle(string $name){
        return $this->styles[$name] = new ProgressBarStyle();
    }

    function getStyle(string $name){
        return $this->styles[$name] ?? null;
    }

    function removeStyle(string $name){
        unset($this->styles[$name]);
    }

    /**
     * @param $name
     * @return bool
     */
    function hasStyle(string $name)
    {
        return isset($this->styles[$name]);
    }

}