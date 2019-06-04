<?php

namespace SitPHP\Commands\Tools\Choice;

use InvalidArgumentException;
use SitPHP\Commands\Command;
use SitPHP\Commands\Tool;
use SitPHP\Commands\Tools\Question\QuestionTool;
use SitPHP\Commands\Tools\Section\SectionTool;
use SitPHP\Commands\Tools\Table\TableTool;

class ChoiceTool extends Tool
{

    // Internal properties
    private $is_placed = false;
    /** @var ChoiceTool $question */
    private $question;
    /** @var ChoiceTool $choice_section */
    private $choice_section;
    /** @var ChoiceTool $error_section */
    private $error_section;
    private $is_displayed = false;
    private $is_displayable = true;
    private $verbosity;
    /**
     * @var ChoiceManager
     */
    private $manager;


    // User defined properties
    private $title;
    private $choices = [];
    private $prompt = '';
    private $default;
    private $max_attempts;
    private $is_quit_active = false;
    /**
     * @var ChoiceStyle
     */
    private $style;
    /**
     * @var bool
     */
    private $is_multi_select_active;


    function __construct(Command $command, ChoiceManager $manager)
    {
        parent::__construct($command);
        $this->manager = $manager;
        $this->setStyle('default');

        // Don't display if request is not interactive no output is a tty
        if(!$command->isInteractive()){
            $this->is_displayable = false;
        }
        else if(!$this->getOutput()->isatty()){
            // If standard output in not a tty try, use the error output instead if it is a tty
            if($this->getErrorOutput()->isatty()){
                $this->useErrorOutput();
            } else {
                $this->is_displayable = false;
            }
        }

        // Prepare question and error bloc
        /** @var QuestionTool question */
        $this->question = $this->tool('question');

    }

    function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    function getTitle()
    {
        return $this->title;
    }

    function setPrompt(string $prompt)
    {
        $this->prompt = $prompt;
        return $this;
    }

    function getPrompt(){
        return $this->prompt;
    }

    function setChoices(array $choices, int $verbosity = null)
    {
        if (isset($verbosity) && $this->getRequest()->getVerbosity() > $verbosity) {
            return $this;
        }
        $this->choices = $choices;
        return $this;
    }

    function getChoice($index){
        return $this->choices[$index] ?? null;
    }

    function getAllChoices()
    {
        return $this->choices;
    }
    function addChoice(string $choice, int $verbosity = null)
    {
        if (isset($verbosity) && $this->getRequest()->getVerbosity() > $verbosity) {
            return $this;
        }
        $this->choices[] = $choice;
        return $this;
    }

    function setMaxAttempts(int $max_attempts)
    {
        $this->max_attempts = $max_attempts;
        return $this;
    }

    function getMaxAttempts()
    {
        return $this->max_attempts;
    }

    function setDefault(string $default)
    {
        $this->default = $default;
        return $this;
    }

    function getDefault()
    {
        return $this->default;
    }

    function setQuitMessage(string $key, string $value){
        $this->getStyle()->setQuitMessage($key, $value);
        return $this;
    }

    function getQuitMessage(){
        return $this->getStyle()->getQuitMessage();
    }

    function enableQuit()
    {
        $this->is_quit_active = true;
        return $this;
    }

    function disableQuit(){
        $this->is_quit_active = false;
    }

    function isQuitActive()
    {
        return $this->is_quit_active;
    }

    function enableMultiSelect()
    {
        $this->is_multi_select_active = true;
        return $this;
    }

    function disableMultiSelect(){
        $this->is_multi_select_active = false;
    }

    function isMultiSelectActive()
    {
        return $this->is_multi_select_active;
    }


    function placeHere()
    {
        /** @var SectionTool choice_section */
        $this->choice_section = $this->tool('section')->placeHere();
        $this->question->placeHere();
        /** @var SectionTool error_section */
        $this->error_section = $this->tool('section')->placeHere();
        $this->is_placed = true;
        return $this;
    }

    function isPlaced()
    {
        return $this->is_placed;
    }

    function isDisplayable(){
        return $this->is_displayable;
    }

    function display(int $verbosity = null){
        // If the choice is not displayable or has already been displayed
        if(!$this->is_displayable || $this->is_displayed){
            return $this;
        }

        // Save display verbosity for later use
        $this->verbosity = $verbosity;

        // Display choice text
        $choice_text = $this->makeChoiceText();
        if ($this->isPlaced()) {
            $this->choice_section->overwrite($choice_text, $verbosity);
        } else {
            $this->getOutput()->write($choice_text, $verbosity);
        }
        // Display question
        $this->question
            ->setPrompt($this->prompt)
            ->setPromptFormat($this->getStyle()->getPromptFormat())
            ->setAutocompleteFormat($this->getStyle()->getAutoCompleteFormat())
            ->setInputFormat($this->getStyle()->getInputFormat())
            ->display($verbosity);

        $this->is_displayed = true;
        return $this;
    }

    function ask(int $verbosity = null)
    {
        if(!$this->is_displayable){
            return null;
        }
        // Check if display verbosity matches request verbosity
        if($this->verbosity !== null){
            $verbosity = $this->verbosity;
        }
        if ($verbosity !== null && $verbosity > $this->getRequest()->getVerbosity()) {
            return null;
        }

        $autocomplete = $this->makeAutoCompleteValues();
        $this->question->setAutocomplete($autocomplete);

        $attempts = 0;
        do {
            $retry = false;
            $attempts++;
            $this->display();

            // Validate response
            $answer = $this->question->ask();
            if($this->is_multi_select_active){
                $response = $this->resolveMultiSelectResponse($answer, $autocomplete, $attempts);
                if($response === false){
                    $retry = true;
                }
            } else {
                $response = $this->resolveSingleSelectResponse($answer, $autocomplete, $attempts);
                if($response === false){
                    $retry = true;
                }
            }
            if (isset($this->max_attempts) && $attempts >= $this->max_attempts) {
                $retry = false;
            }
            if($retry && !$this->isPlaced()){
                $this->getOutput()->lineBreak();
            }
        } while ($retry === true);

        $this->verbosity = null;
        $this->is_displayed = false;
        return $response;
    }

    /*
     * Style methods
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

    function setTitleFormat(string $format)
    {
        $this->getStyle()->setTitleFormat($format);
        return $this;
    }
    function getTitleFormat(){
        return $this->getStyle()->getTitleFormat();
    }

    function setPromptFormat(string $format)
    {
        $this->getStyle()->setPromptFormat($format);
        return $this;
    }
    function getPromptFormat(){
        return $this->getStyle()->getPromptFormat();
    }

    function setChoiceFormat(string $key_format, string $value_format)
    {
        $this->getStyle()->setChoiceFormat($key_format, $value_format);
        return $this;
    }
    function getChoiceFormat(){
        return $this->getStyle()->getChoiceFormat();
    }

    function setInputFormat(string $format)
    {
        $this->getStyle()->setInputFormat($format);
        return $this;
    }
    function getInputFormat(){
        return $this->getStyle()->getInputFormat();
    }

    function setAutoCompleteFormat(string $format)
    {
        $this->getStyle()->setAutoCompleteFormat($format);
        return $this;
    }
    function getAutoCompleteFormat(){
        return $this->getStyle()->getAutoCompleteFormat();
    }

    function setQuitFormat(string $key_format, string $value_format){
        $this->getStyle()->setQuitFormat($key_format, $value_format);
        return $this;
    }
    function getQuitFormat(){
        return $this->getStyle()->getQuitFormat();
    }

    function setErrorFormat(string $format){
        $this->getStyle()->setErrorFormat($format);
        return $this;
    }

    function getErrorFormat(){
        return $this->getStyle()->getErrorFormat();
    }

    function setErrorMessage(string $error_message)
    {
        $this->getStyle()->setErrorMessage($error_message);
        return $this;
    }

    function getErrorMessage(){
        return $this->getStyle()->getErrorMessage();
    }


    protected function resolveSingleSelectResponse(string $input, array $autocomplete, int $attempts){
        $response = false;
        $quit_text = $this->getStyle()->getQuitMessage();
        if ($this->is_quit_active && ($input == $quit_text['key'] || $input == $autocomplete[$quit_text['key']])) {
            $response = null;
        } else if (false !== $key = array_search($input, $autocomplete)) {
            $response = $key;
        } else if(isset($autocomplete[$input])){
            $response = $input;
        } else if ($input === '' && $this->default !== null) {
            $response = $this->default;
        } else {
            $this->renderErrorMessage($input, $attempts);
        }
        return $response;
    }
    protected function resolveMultiSelectResponse(string $input, array $autocomplete, int $attempts){
        if($input === '' && $this->default !== null){
            $response = [$this->default];
            return $response;
        }
        $quit_text = $this->getStyle()->getQuitMessage();
        if($this->is_quit_active && ($input == $quit_text['key'] || $input == $autocomplete[$quit_text['key']])){
            return null;
        }
        $inputs = array_map('trim',explode(',', $input));
        $response = [];
        foreach ($inputs as $input){
            if($this->is_quit_active && ($input == $quit_text['key'] || $input == $autocomplete[$quit_text['key']])){
                $this->renderErrorMessage($input, $attempts);
                $response = false;
                break;
            } else if (isset($autocomplete[$input])) {
                $response[] = $input;
            } else if(false !== $key = array_search($input, $autocomplete)){
                $response[] = $key;
            } else {
                $this->renderErrorMessage($input, $attempts);
                $response = false;
                break;
            }
        }
        return $response;
    }

    protected function renderErrorMessage($input, $attempts){
        $error_message = strtr($this->getStyle()->getErrorMessage(), ['%input%' => $input]);
        $error_message = strtr($this->getStyle()->getErrorFormat(), ['%error%' => $error_message]);

        if ($this->isPlaced()) {
            if($this->max_attempts === null || $this->max_attempts > $attempts){
                $this->getRequest()->changeStty('-echo -icanon');
                $this->getOutput()->disableCursor();
                $this->error_section->overwrite($error_message);
                sleep(1);
                $this->error_section->clear();
                $this->getOutput()->enableCursor();
                $this->getRequest()->changeStty('echo icanon');
            } else {
                $this->error_section->overwriteLn($error_message);
            }
        } else {
            $this->getOutput()->writeLn($error_message);
            $this->is_displayed = false;
        }
    }

    protected function makeChoiceText()
    {
        /** @var TableTool $table */
        $table = $this->tool('table');
        $table->setStyle('transparent');

        // Build choices display
        $choices_text = '';

        // Make sure choices starts in new line
        if ($this->isPlaced()) {
            $before_after_buffers = $this->choice_section->getBufferSplit();
            if (!empty($before_after_buffers['before']) && mb_substr(end($before_after_buffers['before']), -1) !== "\n") {
                $choices_text = "\n" . $choices_text;
            }
        } else {
            $buffer = $this->getOutput()->getBuffer();
            if (!empty($buffer) && mb_substr(end($buffer), -1) !== "\n") {
                $choices_text = "\n" . $choices_text;
            }
        }

        // Title
        if (isset($this->title)) {
            $choices_text .= strtr($this->getTitleFormat(), ['%title%' => $this->title]) . PHP_EOL;
        }

        // Choices
        foreach ($this->choices as $key => $value) {
            $choice_format = $this->getStyle()->getChoiceFormat();
            $key = strtr($choice_format['key'], ['%key%' => $key]);
            $value = strtr($choice_format['value'], ['%value%' => $value]);
            $table->addRow([$key, $value]);
        }

        // Quit
        if ($this->is_quit_active) {
            $quit_format = $this->getStyle()->getQuitFormat();
            $quit_text = $this->getStyle()->getQuitMessage();
            $key = strtr($quit_format['key'], ['%key%' => $quit_text['key']]);
            $value = strtr($quit_format['value'], ['%value%' => $quit_text['value']]);
            $table->addRow([$key, $value]);
        }

        $choices_text .= $table->getDisplay();
        return $choices_text;
    }

    protected function makeAutoCompleteValues()
    {
        $autocomplete = [];
        foreach ($this->choices as $key => $value) {
            $autocomplete[$key] = $key.' ('.$value.')';
        }

        if ($this->is_quit_active) {
            $quit_text = $this->getStyle()->getQuitMessage();
            $autocomplete[$quit_text['key']] = $quit_text['key'].' ('.$quit_text['value'].')';
        }
        return $autocomplete;
    }
}