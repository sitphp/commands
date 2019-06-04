<?php


namespace SitPHP\Commands\Tools\Choice;


class ChoiceStyle
{
    protected $title_format = '<cs bold="true">%title%</cs>'
        . PHP_EOL
        . '--------------------------------------';
    protected $prompt_format =
        '--------------------------------------'
        . PHP_EOL
        . '%prompt% ';

    protected $input_format = '<cs background-color="dark_grey" color="white">%input%</cs>';
    protected $auto_complete_format = '<cs color="dark_grey">%autocomplete%</cs>';
    protected $choice_key_format = '<cs color = "yellow">[%key%]</cs>';
    protected $choice_value_format = '<cs>%value%</cs>';
    protected $quit_key_format = '<cs color = "light_grey">[%key%]</cs>';
    protected $quit_value_format = '<cs color = "light_grey">%value%</cs>';
    protected $error_format = ' <error>[%error%]</error>';
    protected $error_message = 'Invalid choice';
    protected $quit_message = ['key' => 'q', 'value' => 'Quit/Exit'];
    private $name;


    function __construct(string $name = null)
    {
        $this->name = $name;
    }

    function getName(){
        return $this->name;
    }

    function setQuitMessage(string $key, string $value){
        $this->quit_message['key'] = $key;
        $this->quit_message['value'] = $value;
    }

    function getQuitMessage(){
        return $this->quit_message;
    }

    function setTitleFormat(string $format)
    {
        $this->title_format = $format;
        return $this;
    }

    function getTitleFormat()
    {
        return $this->title_format;
    }

    function setPromptFormat(string $format)
    {
        $this->prompt_format = $format;
        return $this;
    }

    function getPromptFormat()
    {
        return $this->prompt_format;
    }

    function setChoiceFormat(string $key_format, string $value_format)
    {
        $this->choice_key_format = $key_format;
        $this->choice_value_format = $value_format;
    }

    function getChoiceFormat()
    {
        return ['key' => $this->choice_key_format, 'value' => $this->choice_value_format];
    }

    function setInputFormat(string $format){
        $this->input_format = $format;
    }

    function getInputFormat(){
        return $this->input_format;
    }


    function setQuitFormat(string $key, string $value)
    {
        $this->quit_key_format = $key;
        $this->quit_value_format = $value;
    }

    function getQuitFormat()
    {
        return ['key' => $this->quit_key_format, 'value' => $this->quit_value_format];
    }

    function setAutoCompleteFormat(string $format)
    {
        $this->auto_complete_format = $format;
        return $this;
    }

    function getAutoCompleteFormat()
    {
        return $this->auto_complete_format;
    }

    function setErrorFormat(string $format)
    {
        $this->error_format = $format;
        return $this;
    }

    function getErrorFormat()
    {
        return $this->error_format;
    }

    function setErrorMessage(string $error_message)
    {
        $this->error_message = $error_message;
        return $this;
    }

    function getErrorMessage()
    {
        return $this->error_message;
    }
}