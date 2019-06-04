<?php

namespace SitPHP\Commands\Tools\Question;

use Closure;
use Exception;
use InvalidArgumentException;
use SitPHP\Commands\Command;
use SitPHP\Commands\Helpers\CharHelper;
use SitPHP\Commands\Output;
use SitPHP\Commands\Tool;
use SitPHP\Commands\Tools\Section\SectionTool;
use SitPHP\Helpers\Text;

class QuestionTool extends Tool
{

    // Internal properties
    private $is_placed = false;
    /**
     * @var QuestionTool
     */
    private $input_section;
    /**
     * @var QuestionTool
     */
    private $prompt_section;
    private $is_displayed = false;
    private $display_verbosity;

    // User properties
    private $placeholder;
    private $auto_complete;
    private $is_secret_typing = false;
    private $prompt;

    /**
     * @var QuestionStyle
     */
    private $style;

    /**
     * @var bool
     */
    private $is_displayable = true;
    /**
     * @var QuestionManager
     */
    private $manager;



    /**
     * @param QuestionTool $question
     * @param int|null $verbosity
     * @return string|null
     * @throws Exception
     */
    static function askQuestion(QuestionTool $question, int $verbosity = null){
        if(!$question->getCommand()->isInteractive()){
            return null;
        }

        $question->display($verbosity);

        // Check if display verbosity match request verbosity
        if ($question->display_verbosity !== null && $question->display_verbosity > $question->getCommand()->getVerbosity()) {
            return null;
        }

        if (!empty($question->getAutoComplete())) {
            $response = static::getAutoCompleteResponse($question);
        } else if ($question->isSecretTypingActive()) {
            $response = static::getSecretResponse($question);
        } else {
            $response = static::getStandardResponse($question);
        }
        $question->is_displayed = false;
        return trim($response);
    }

    /**
     * @param QuestionTool $question
     * @return bool|string
     */
    protected static function getStandardResponse(QuestionTool $question)
    {
        $input = $question->getInput();
        $output = $question->getOutput();

        if (!$input->isatty()) {
            $response = trim($input->readLine(), PHP_EOL);
            return $response;
        }

        $question->getCommand()->changeStty('-echo -icanon');
        $input_section = $question->input_section;
        $input_section->moveCursorToStartPosition();
        $written = '';
        do {
            $char = $input->readChar();
            if($char == ''){
                break;
            }
            if (CharHelper::isContentChar($char)) {
                $written .= $char;
                static::writeStandard($question, $written);
                $input_section->moveCursorToTipPosition();
            } else if (CharHelper::isBackspaceChar($char)) {
                if (mb_strlen($written) === 0) {
                    continue;
                }
                $written = substr($written, 0, -1);
                static::writeStandard($question, $written);
                $input_section->moveCursorToTipPosition();
            } else if (CharHelper::isReturnChar($char)) {
                static::writeStandard($question, $written);
                $output->moveCursorToTipPosition();
                break;
            } else {
                continue;
            }
        } while (true);
        $question->getCommand()->changeStty('echo icanon');
        return $written;
    }

    protected static function writeStandard($question, $message){
        $input_section = $question->input_section;
        $input_format = $question->style->getInputFormat();

        $input_section->overwrite(strtr($input_format, ['%input%' => $message]));
    }

    /**
     * @param QuestionTool $question
     * @return bool|string
     */
    protected static function getSecretResponse(QuestionTool $question)
    {
        $input = $question->getInput();
        $output = $question->getOutput();
        $input_section = $question->input_section;

        if (!$input->isatty()) {
            $written = trim($input->readLine(), PHP_EOL);
            return $written;
        }

        $question->getCommand()->changeStty('-echo -icanon');
        $question->input_section->moveCursorToStartPosition();
        $written = '';
        do {
            $char = $input->readChar();
            if($char == ''){
                break;
            }
            if (CharHelper::isContentChar($char)) {
                $written .= $char;
                static::writeSecret($question, $written);
                $input_section->moveCursorToTipPosition();
            } else if (CharHelper::isBackspaceChar($char)) {
                if (mb_strlen($written) === 0) {
                    continue;
                }
                $written = substr($written, 0, -1);
                static::writeSecret($question, $written);
                $input_section->moveCursorToTipPosition();
            } else if (CharHelper::isReturnChar($char) || $input->isEndOfFile()) {
                static::writeSecret($question, $written);
                $output->moveCursorToTipPosition();
                break;
            } else {
                continue;
            }
        } while (true);
        $question->getCommand()->changeStty('echo icanon');
        return $written;
    }

    protected static function writeSecret($question, $message){
        $input_section = $question->input_section;
        $input_format = $question->style->getInputFormat();

        $input_section->overwrite(strtr($input_format, ['%input%' => str_repeat('*', mb_strlen($message))]));
    }


    /**
     * @param QuestionTool $question
     * @return bool|string
     */
    protected static function getAutoCompleteResponse(QuestionTool $question)
    {
        $input = $question->getInput();
        $output = $question->getOutput();

        if (!$input->isatty()) {
            $written = trim($input->readLine(), PHP_EOL);
            $current_matches = static::resolveAutoCompleteMatches($written, $question);
            if($written !== '' && !empty($current_matches)){
                $autocomplete = static::resolveAutocompleteMatchText($written, $current_matches[0]);
                $response = $written.$autocomplete;
            } else {
                $response = $written;
            }
            return $response;
        }

        $written = '';
        $current_match_index = null;
        $current_matches = static::resolveAutoCompleteMatches($written, $question);
        $question->getCommand()->changeStty('-echo -icanon');
        $question->input_section->moveCursorToStartPosition();
        do {
            $char = $input->readChar();
            if($char == ''){
                break;
            }
            // Return key pressed
            if (CharHelper::isReturnChar($char)) {
                if ($current_match_index !== null && !empty($current_match = $current_matches[$current_match_index])) {
                    $autocomplete = static::resolveAutocompleteMatchText($written, $current_match);
                    $written .= $autocomplete;
                }
                static::writeAutoComplete($question, $written);
                $output->moveCursorToTipPosition();
                break;
            } // Tab key pressed
            else if (CharHelper::isTabChar($char)) {
                if ($current_match_index !== null && !empty($current_match = $current_matches[$current_match_index])) {
                    // Update match
                    $current_match_index = null;
                    $current_matches = static::resolveAutoCompleteMatches($written, $question);

                    // Write
                    $autocomplete = static::resolveAutocompleteMatchText($written, $current_match);
                    $written .= $autocomplete;
                    static::writeAutoComplete($question, $written);
                }
                continue;
            } // Arrow up or down key pressed
            else if (CharHelper::isArrowUpChar($char) || CharHelper::isArrowDownChar($char)) {
                if (empty($current_matches)) {
                    continue;
                }
                if (CharHelper::isArrowUpChar($char)) {
                     if ($current_match_index === null) {
                         $current_match_index = 0;
                     } else {
                        $current_match_index = isset($current_matches[$current_match_index + 1]) ? $current_match_index + 1 : 0;
                     }
                } else {
                    if ($current_match_index === null) {
                        $current_match_index = count($current_matches) - 1;
                    } else {
                        $current_match_index = isset($current_matches[$current_match_index - 1]) ? $current_match_index - 1 : count($current_matches) - 1;
                    }
                }
                $autocomplete = static::resolveAutocompleteMatchText($written, $current_matches[$current_match_index]);
                static::writeAutocomplete($question, $written, $autocomplete);
                continue;

            } // Backspace key pressed
            else if (CharHelper::isBackspaceChar($char)) {
                if ($current_match_index !== null) {
                    static::writeAutocomplete($question, $written);
                    $current_match_index = null;
                } else {
                    $written = mb_substr($written, 0, -1);
                    static::writeAutocomplete($question, $written);
                    $current_match_index = null;
                    $current_matches = static::resolveAutoCompleteMatches($written, $question);
                }
            } // Control or arrow left/right key pressed
            else if (CharHelper::isControlKeyChar($char) || CharHelper::isArrowLeftChar($char) || CharHelper::isArrowRightChar($char)) {
                continue;
            } // Content key pressed
            else {
                $written .= $char;
                $autocomplete = null;
                // Update match
                $current_matches = static::resolveAutoCompleteMatches($written, $question);
                if (!empty($current_matches)) {
                    $current_match_index = 0;
                    $autocomplete = static::resolveAutocompleteMatchText($written, $current_matches[$current_match_index]);
                } else {
                    $current_match_index = null;
                }
                static::writeAutocomplete($question, $written, $autocomplete);
            }
        } while(true);
        $question->getRequest()->changeStty('echo icanon');
        return $written;
    }

    /**
     * @param QuestionTool $question
     * @param $text
     * @param string|null $auto_complete
     */
    protected static function writeAutoComplete(QuestionTool $question, $text, string $auto_complete = null)
    {
        $input_section = $question->input_section;
        $input_format = $question->style->getInputFormat();
        $output = $question->getOutput();

        // Resolve text autocomplete matches
        if (isset($auto_complete)) {
            $text = strtr($input_format, ['%input%' => $text]);
            $styled_auto_complete = strtr($question->style->getAutocompleteFormat(), ['%autocomplete%' => strtr($auto_complete, ['<' => '\<'])]);
            $text .= $styled_auto_complete;
            $input_section->overwrite($text);
            $input_section->moveCursorToTipPosition();
            $output->moveCursorLeft(mb_strlen($auto_complete));

        } else {
            $text = strtr($input_format, ['%input%' => $text]);
            $input_section->overwrite($text);
            $input_section->moveCursorToTipPosition();
            $current_match_index = null;
        }
    }

    /**
     * @param string $text
     * @param QuestionTool $question
     * @return array
     */
    protected static function resolveAutoCompleteMatches(string $text, QuestionTool $question)
    {
        if($question->auto_complete instanceof Closure){
            $closure = $question->auto_complete;
            $autocomplete = $closure($text);
        } else {
            $autocomplete = $question->auto_complete;
        }
        if($autocomplete === null){
            $autocomplete = [];
        }
        else if(!is_array($autocomplete)){
            throw new InvalidArgumentException('Invalid autocomplete values : expected array');
        }

        $matches = [];
        foreach ($autocomplete as $autocomplete_value) {
            if ($autocomplete_value === $text) {
                continue;
            } else if (Text::startsWith($autocomplete_value, $text)) {
                $matches[] = $autocomplete_value;
            }
        }
        return $matches;
    }

    /**
     * @param $text
     * @param $match
     * @return bool|string
     */
    protected static function resolveAutocompleteMatchText($text, $match)
    {
        $line_length = strlen($text);
        $match_length = strlen($match);
        $autocomplete_length = $match_length - $line_length;
        $autocomplete = substr($match, $line_length, $autocomplete_length);
        return $autocomplete;
    }

    /**
     * QuestionTool constructor.
     *
     * @param Command $command
     * @param QuestionManager $manager
     * @throws Exception
     */
    function __construct(Command $command, QuestionManager $manager)
    {
        parent::__construct($command);
        $this->manager = $manager;

        // Find out where to display the question and ask for answer
        if(!$command->isInteractive() || !$this->getInput()->isatty()){
            $this->is_displayable = false;
        }
        // If standard output in not a tty try, use the error output or create an stty output from input path
        else if(!$this->getOutput()->isatty()){
            if($this->getErrorOutput()->isatty()){
                $this->useErrorOutput();
            } else {
                $output = new Output($this->getInput()->getPath());
                $output->setFormatter($this->getCommand()->getManager()->getFormatter());
                $this->setOutput($output);
            }
        }
    }

    function setPrompt(string $text)
    {
        $this->prompt = $text;
        return $this;
    }

    /**
     * @return mixed
     */
    function getPrompt()
    {
        return $this->prompt;
    }

    /**
     * @param string $text
     * @return $this
     */
    function setPlaceholder(string $text)
    {
        $this->placeholder = $text;
        return $this;
    }

    /**
     * @return mixed
     */
    function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param $data
     * @return $this
     * @throws Exception
     */
    function setAutoComplete($data)
    {
        $this->is_secret_typing = false;
        if(is_array($data)){
            $this->auto_complete = array_values($data);
        } else if($data instanceof Closure){
            $this->auto_complete = $data;
        } else {
            throw new InvalidArgumentException('Invalid autocomplete $data : expected array or instance of '. Closure::class);
        }


        return $this;
    }

    /**
     * @return array|Closure
     */
    function getAutoComplete(){
        return $this->auto_complete;
    }


    /**
     * @return $this
     */
    function enableSecretTyping()
    {
        $this->is_secret_typing = true;
        return $this;
    }

    /**
     * @return $this
     */
    function disableSecretTyping()
    {
        $this->is_secret_typing = false;
        return $this;
    }

    /**
     * @return bool
     */
    function isSecretTypingActive()
    {
        return $this->is_secret_typing;
    }

    /**
     * @return $this
     * @throws Exception
     */
    function placeHere()
    {
        $this->prompt_section = $this->questionSection()->placeHere();
        $this->input_section = $this->questionSection()->placeHere();

        $this->is_placed = true;
        return $this;
    }

    /**
     * @return bool
     */
    function isPlaced(){
        return $this->is_placed;
    }

    /**
     * @param int|null $verbosity
     * @return $this
     * @throws Exception
     */
    function display(int $verbosity = null)
    {

        // Save display verbosity for ask use
        $this->display_verbosity = $verbosity;

        // If the question has already been displayed or is not displayable
        if(!$this->is_displayable || $this->is_displayed){
            return $this;
        }


        // Display prompt
        if ($this->getPrompt() !== null) {

            $prompt = strtr($this->getStyle()->getPromptFormat(), ['%prompt%' => $this->prompt]);
            if ($this->isPlaced()) {
                $this->prompt_section->overwrite($prompt, $verbosity);
            } else {
                $this->getOutput()->write($prompt, $verbosity);
            }
        }
        // Prepare ask section
        if (!$this->isPlaced()) {
            $this->input_section = $this->questionSection()->placeHere();
        } else {
            $this->input_section->clear($verbosity);
        }

        // Display placeholder
        if (null !== $placeholder = $this->getPlaceholder()) {
            $placeholder = strtr($this->getStyle()->getPlaceholderFormat(), ['%placeholder%' => $placeholder]);
            $this->input_section->overwrite($placeholder, $verbosity);
        }

        $this->is_displayed = true;
        return $this;
    }

    /**
     * @param int|null $verbosity
     * @return string|null
     * @throws Exception
     */
    function ask(int $verbosity = null)
    {
        return static::askQuestion($this, $verbosity);
    }


    /*
     * Style methods
     */

    /**
     * Set question style
     *
     * @param $style
     * @return QuestionTool
     * @throws Exception
     */
    function setStyle($style)
    {
        $style = $this->manager->getStyle($style);
        if ($style === null) {
            throw new InvalidArgumentException('Undefined style ' . $style);
        }
        $this->style = clone $style;
        return $this;
    }

    /**
     * @return QuestionStyle
     */
    function getStyle(){
        return $this->style;
    }

    /**
     * @param string $format
     * @return $this
     */
    function setPromptFormat(string $format)
    {
        $this->getStyle()->setPromptFormat($format);
        return $this;
    }
    function getPromptFormat(){
        return $this->getStyle()->getPromptFormat();
    }


    /**
     * @param string $format
     * @return $this
     */
    function setInputFormat(string $format)
    {
        $this->getStyle()->setInputFormat($format);
        return $this;
    }
    function getInputFormat(){
        return $this->getStyle()->getInputFormat();
    }


    /**
     * @param string $format
     * @return $this
     */
    function setAutocompleteFormat(string $format)
    {
        $this->getStyle()->setAutocompleteFormat($format);
        return $this;
    }
    function getAutocompleteFormat(){
        return $this->getStyle()->getAutocompleteFormat();
    }

    /**
     * @param string $format
     * @return QuestionTool
     */
    function setPlaceholderFormat(string $format){
        $this->getStyle()->setPlaceholderFormat($format);
        return $this;
    }
    function getPlaceholderFormat(){
        return $this->getStyle()->getPlaceholderFormat();
    }

    /**
     * @return SectionTool
     */
    protected function questionSection()
    {
        /** @var SectionTool $section */
        $section = $this->tool('section');
        return $section;
    }
}