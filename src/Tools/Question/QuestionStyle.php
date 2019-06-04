<?php


namespace SitPHP\Commands\Tools\Question;


class QuestionStyle
{
    protected $input_format = '<cs background-color="dark_grey" color="white">%input%</cs>';
    protected $auto_complete_format = '<cs color="dark_grey">%autocomplete%</cs>';
    protected $prompt_format = '%prompt% ';
    protected $placeholder_format = '<cs color="dark_grey">%placeholder%</cs>';
    /**
     * @var string
     */
    private $name;

    function __construct(string $name = null)
    {
        $this->name = $name;
    }

    function getName(){
        return $this->name;
    }

    function setPromptFormat(string $format)
    {
        $this->prompt_format = $format;
        return $this;
    }

    function getPromptFormat(){
        return $this->prompt_format;
    }


    function setInputFormat(string $format)
    {
        $this->input_format = $format;
        return $this;
    }

    function getInputFormat()
    {
        return $this->input_format;
    }

    function setAutocompleteFormat(string $format)
    {
        $this->auto_complete_format = $format;
        return $this;
    }

    function getAutocompleteFormat()
    {
        return $this->auto_complete_format;
    }

    function setPlaceholderFormat(string $format){
        $this->placeholder_format = $format;
    }

    function getPlaceholderFormat(){
        return $this->placeholder_format;
    }
}