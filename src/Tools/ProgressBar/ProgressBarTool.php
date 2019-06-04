<?php

namespace SitPHP\Commands\Tools\ProgressBar;

use Exception;
use InvalidArgumentException;
use LogicException;
use SitPHP\Benchmarks\Bench;
use SitPHP\Commands\Command;
use SitPHP\Commands\Tool;
use SitPHP\Commands\Tools\Section\SectionTool;

class ProgressBarTool extends Tool
{
    // Internal properties
    private $is_displayed = false;
    private $is_finished = false;
    private $section;
    private $progress;
    /**
     * @var Bench
     */
    private $bench;

    // User properties
    private $current_message;
    private $steps = 5;
    private $placed = false;
    private $is_auto_finish_active = true;

    /**
     * @var ProgressBarManager
     */
    private $manager;
    /**
     * @var ProgressBarStyle
     */
    private $style;
    /**
     * @var int|null
     */
    private $current_verbosity;


    /**
     * ProgressBarCommandTool constructor.
     *
     * @param Command $command
     * @param ProgressBarManager $manager
     * @param int $steps
     * @throws Exception
     */
    function __construct(Command $command, ProgressBarManager $manager, int $steps)
    {
        parent::__construct($command);
        $this->manager = $manager;
        $bench_manager = $command->getManager()->getBenchManager();
        $this->bench = $bench_manager->benchmark();
        $this->setStyle('default');
        $this->setSteps($steps);
        $this->progress = 0;

        if($command->isVerbose()){
            $this->setFormat('%steps% [%bar%] (%percents% %elapsed% %memory%) %message%');
        }
    }

    function setSteps(int $steps){
        if($steps < 1){
            throw new InvalidArgumentException('Invalid $steps argument : expected int >= 1');
        }
        $this->steps = $steps;
        if(!$this->is_displayed){
            return;
        }
        if($steps <= $this->progress){
            $this->progress = $steps;
            $this->goToStep($steps, $this->current_message);
        } else {
            $this->doDisplay($this->current_message);
        }
    }

    /**
     * Return progress steps
     *
     * @return int
     */
    function getSteps()
    {
        return $this->steps;
    }

    /**
     * Display the progress bar
     *
     * @param string|null $message
     * @param int $verbosity
     * @return ProgressBarTool|void
     * @throws Exception
     */
    function display(string $message = null, int $verbosity = Command::VERBOSITY_NORMAL)
    {
        if($this->is_displayed){
            return;
        }
        $this->current_verbosity = $verbosity;
        $this->bench->start();
        $this->doDisplay($message);
        $this->is_displayed = true;
        return $this;
    }

    /**
     * Progress one step
     *
     * @param string|null $message
     * @throws Exception
     */
    function progress(string $message = null)
    {
        $this->expectDisplayed(__METHOD__);
        if($this->is_finished){
            return;
        }

        $progress = $this->progress + 1;
        if($progress >= $this->steps){
            $progress = $this->steps;
        }
        $this->progress = $progress;
        if ($this->is_auto_finish_active && $this->progress === $this->steps) {
            $this->finish($message);
        } else {
            $this->doDisplay($message);
        }
    }

    /**
     * Regress one step
     *
     * @param string|null $message
     * @throws Exception
     */
    function regress(string $message = null)
    {
        $this->expectDisplayed(__METHOD__);
        if($this->is_finished){
            return;
        }

        $progress = $this->progress - 1;
        if($progress < 0){
            $progress = 0;
        }
        $this->progress = $progress;

        $this->doDisplay($message);
    }

    /**
     * Jump to n steps
     *
     * @param int $steps
     * @param null $message
     * @throws Exception
     */
    function jump(int $steps, $message = null)
    {
        $this->expectDisplayed(__METHOD__);
        if ($this->is_finished) {
            return;
        }

        $progress = $this->progress + $steps;
        if ($progress > $this->steps) {
            $progress = $this->steps;
        }
        $this->progress = $progress;
        if ($this->is_auto_finish_active && $this->progress === $this->steps) {
            $this->finish($message);
        } else {
            $this->doDisplay($message);
        }
    }

    /**
     * Dive to n steps
     *
     * @param int $steps
     * @param null $message
     * @throws Exception
     */
    function dive(int $steps, $message = null)
    {
        $this->expectDisplayed(__METHOD__);

        if ($this->is_finished) {
            return;
        }
        $progress = $this->progress - $steps;
        if ($progress < 0) {
            $progress = 0;
        }
        $this->progress = $progress;
        $this->doDisplay($message);
    }

    /**
     * @param int $step
     * @param $message
     * @throws Exception
     */
    function goToStep(int $step, $message){
        $this->expectDisplayed(__METHOD__);
        if($step >= $this->steps) {
            $step = $this->steps;
        }
        $this->progress = $step;
        if($step === $this->steps && $this->is_auto_finish_active){
            $this->finish($message);
        } else {
            $this->progress = $step;
            $this->doDisplay($message);
        }
    }

    /**
     * End progress bar
     *
     * @param null $message
     * @throws Exception
     */
    function finish($message = null)
    {
        $this->expectDisplayed(__METHOD__);

        $this->bench->stop();
        $this->progress = $this->steps;
        $this->doDisplay($message);
        $this->is_finished = true;
    }

    /**
     * Stick the progress bar to position
     *
     * @return $this
     * @throws Exception
     */
    function placeHere()
    {
        /** @var SectionTool $section */
        $section = $this->tool('section');
        $section->placeHere();
        $this->section = $section;
        $this->placed = true;
        return $this;
    }

    /**
     * Check of progress is finished
     *
     * @return bool
     */
    function isFinished()
    {
        return $this->is_finished;
    }


    /**
     * @return $this
     */
    function enableAutoFinish()
    {
        $this->is_auto_finish_active = true;
        return $this;
    }

    /**
     * @return $this
     */
    function disableAutoFinish()
    {
        $this->is_auto_finish_active = false;
        return $this;
    }

    /**
     * @return bool
     */
    function isAutoFinishActive()
    {
        return $this->is_auto_finish_active;
    }

    /**
     * Return current progress
     *
     * @return int
     */
    function getProgress()
    {
        return $this->progress;
    }

    /**
     * Return current message
     *
     * @return mixed
     */
    function getMessage(){
        return $this->current_message;
    }


    /**
     * @param bool $raw
     * @param int $round
     * @param string $format
     * @return float|string
     */
    function getElapsedTime(bool $raw = false, int $round = 3, string $format = '%time%%unit%'){
        return $this->bench->getElapsed($raw, $round, $format);
    }

    /**
     * @return string
     */
    function getStartTime(){
        return $this->bench->getStartTime();
    }

    /**
     * @return string
     */
    function getStopTime(){
        return $this->bench->getStopTime();
    }

    /**
     * Check if progress bar is stick
     *
     * @return bool
     */
    function isPlaced()
    {
        return $this->placed;
    }

    /*
     * Style methods
     */

    /**
     * Set progress bar style
     *
     * @param $style
     * @return ProgressBarTool
     * @throws Exception
     */
    function setStyle(string $style)
    {
        $style = $this->manager->getStyle($style);
        if ($style === null) {
            throw new InvalidArgumentException('Undefined style ' . $style);
        }
        $this->style = clone $style;
        return $this;
    }

    function getStyle(){
        return $this->style;
    }

    /**
     * Set progress bar format
     *
     * @param string $format
     * @return $this
     */
    function setFormat(string $format)
    {
        $this->getStyle()->setFormat($format);
        return $this;
    }

    /**
     * @return string
     */
    function getFormat(){
        return $this->getStyle()->getFormat();
    }

    /**
     * Set progress bar width
     *
     * @param int $width
     * @return $this
     */
    function setWidth(int $width)
    {
        $this->getStyle()->setWidth($width);
        return $this;
    }

    /**
     * @return int
     */
    function getWidth(){
        return $this->getStyle()->getWidth();
    }

    /**
     * Set progress character
     *
     * @param string $char
     * @return $this
     */
    function setProgressChar(string $char)
    {
        $this->getStyle()->setProgressChar($char);
        return $this;
    }

    /**
     * @return string
     */
    function getProgressChar(){
        return $this->getStyle()->getProgressChar();
    }


    /**
     * Set indicator character
     *
     * @param string $char
     * @return $this
     */
    function setIndicatorChar(string $char)
    {
        $this->getStyle()->setIndicatorChar($char);
        return $this;
    }

    function getIndicatorChar(){
        return $this->getStyle()->getIndicatorChar();
    }

    /**
     * Set space character
     *
     * @param string $char
     * @return $this
     */
    function setSpaceChar(string $char)
    {
        $this->getStyle()->setSpaceChar($char);
        return $this;
    }

    /**
     * @return string
     */
    function getSpaceChar(){
        return $this->getStyle()->getSpaceChar();
    }

    /**
     * @param string $method
     */
    protected function expectDisplayed(string $method){
        if (!$this->is_displayed) {
            throw new LogicException('Display before running "'.$method.'" method');
        }
    }

    /**
     * Display progress bar
     *
     * @param null $message
     * @throws Exception
     */
    protected function doDisplay($message = null)
    {
        $this->current_message = $message;
        $progress_line = $this->resolveProgressLine() . PHP_EOL;
        if (!$this->placed) {
            $this->getOutput()->write($progress_line, $this->current_verbosity);
        } else {
            $this->section->overwrite($progress_line, $this->current_verbosity);
        }

    }

    /**
     * @return string
     */
    protected function resolveProgressLine()
    {
        $format = $this->getStyle()->getFormat();
        $str_params = [];
        foreach ($this->manager->getAllPlaceholderCallbacks() as $name => $callback) {
            $str_params['%'.$name.'%'] = $callback($this);
        }
        return strtr($format, $str_params);
    }
}