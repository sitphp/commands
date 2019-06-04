<?php

namespace SitPHP\Commands;

use Exception;
use InvalidArgumentException;
use SitPHP\Commands\Helpers\PhpHelper;
use SitPHP\Formatters\Formatter;
use SitPHP\Resources\Stream;

class Output
{
    // Internal properties
    /**
     * @var array
     */
    protected $buffer = [];
    /**
     * @var Resource
     */
    protected $handle;


    // User properties
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var int|null
     */
    protected $verbosity = Command::VERBOSITY_NORMAL;
    /**
     * @var array
     */
    protected $cursor_position = ['line' => 1, 'column' => 0];
    /**
     * @var Stream stream
     */
    protected $stream;
    /**
     * @var Formatter
     */
    private $formatter;
    /**
     * @var bool
     */
    private $is_formatting;


    /**
     * CommandOutput constructor.
     *
     * @param $path
     */
    function __construct($path)
    {
        $this->stream = new Stream($path, 'w+');
    }

    /**
     * @param Formatter $formatter
     */
    function setFormatter(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @return Formatter
     */
    function getFormatter(){
        return $this->formatter;
    }

    /*
     * Stream methods
     */
    /**
     * @return bool
     */
    function isPipe()
    {
        return $this->getStream()->isPipe();
    }

    /**
     * @return bool
     * @throws Exception
     */
    function isatty()
    {
        return $this->getStream()->isatty();
    }

    /**
     * @return bool
     */
    function isFile()
    {
        return $this->getStream()->isFile();
    }

    /**
     * @return bool
     */
    function isChar()
    {
        return $this->getStream()->isChar();
    }

    /**
     * Return output path
     *
     * @return string
     */
    function getPath()
    {
        return $this->getStream()->getPath();
    }

    /**
     * Returns handling resource
     * @return resource
     * @throws Exception
     */
    function getHandle()
    {
        return $this->getStream()->getHandle();
    }


    /**
     * @return bool
     * @throws Exception
     */
    function close()
    {
        return $this->getStream()->close();
    }

    /**
     * Check if pointer is at end of file
     *
     * @return bool
     * @throws Exception
     */
    function isEndOfFile()
    {
        return $this->getStream()->isEndOfFile();
    }

    /**
     * @return bool
     * @throws Exception
     */
    function flush()
    {
        return $this->getStream()->flush();
    }

    /**
     * Write remaining data to output buffer
     *
     * @return bool|int
     * @throws Exception
     * @see http://php.net/manual/function.fpassthru.php
     */
    function passThru()
    {
        return $this->getStream()->passThru();
    }

    /**
     * Move handler pointer to offset position
     *
     * @param int $offset
     * @param int $whence
     * @return int
     * @throws Exception
     */
    function seek(int $offset, int $whence = SEEK_SET)
    {
        return $this->getStream()->seek($offset, $whence);
    }

    /**
     * Return handler pointer position
     *
     * @return bool|int
     * @throws Exception
     */
    function tell()
    {
        return $this->getStream()->tell();
    }

    /**
     * Rewind the position of file pointer
     *
     * @return bool
     * @throws Exception
     * @see http://php.net/manual/function.rewind.php
     */
    function rewind()
    {
        return $this->getStream()->rewind();
    }

    /**
     * @param string $message
     * @throws Exception
     */
    function put(string $message){
        return $this->getStream()->put($message);
    }

    /**
     * @param int $maxlength
     * @param int $offset
     * @return bool|string
     */
    function getContents(int $maxlength = -1, int $offset = -1)
    {
        return $this->getStream()->getContents($maxlength, $offset);
    }

    /*
     * Verbosity methods
     */
    /**
     * Set output verbosity level
     *
     * @param int $level
     */
    function setVerbosity(int $level)
    {
        $this->verbosity = $level;
    }

    /**
     * Return verbosity level
     *
     * @return int
     */
    function getVerbosity()
    {
        return $this->verbosity;
    }

    /*
     * Formatting methods
     */
    /**
     * Disable formatting
     */
    function enableFormatting(){
        $this->is_formatting = true;
    }

    /**
     *  Enable formatting
     */
    function disableFormatting(){
        $this->is_formatting = false;
    }

    function resetFormatting(){
        $this->is_formatting = null;
    }

    /**
     * Return formatting
     *
     * @return bool|null
     */
    function isFormattingActive()
    {
        return $this->is_formatting;
    }

    /*
     * Write methods
     */
    /**
     * Write message at tip
     *
     * @param string $message
     * @param int $verbosity
     * @param int|null $width
     * @param bool $escape_tags
     * @return bool
     * @throws Exception
     */
    function write(string $message, int $verbosity = null, int $width = null, bool $escape_tags = null)
    {

        // Check verbosity
        $verbosity = $verbosity ?? Command::VERBOSITY_NORMAL;
        if ($verbosity > $this->verbosity) {
            return false;
        }

        // Format message
        $message = $this->prepareMessage($message, $width, $escape_tags);

        // Retrieve tip cursor position before updating buffer
        $tip_cursor_position = $this->getTipCursorPosition();

        // Update buffer
        $this->buffer[] = $message;

        // Write message
        if($this->isatty()){
            $this->moveCursorToPosition($tip_cursor_position['line'], $tip_cursor_position['column']);
            $this->put($message);
        } else if($this->isFile()){
            $this->rewind();
            ftruncate($this->getStream()->getHandle(), 0);
            $this->put(implode('', $this->buffer));
        } else {
            $this->put($message);
        }

        // Update cursor position
        $tip_cursor_position = $this->getTipCursorPosition();
        $this->updateCursorPosition($tip_cursor_position['line'], $tip_cursor_position['column']);
        return true;
    }


    /**
     * Write message at tip with new line
     * @param string $message
     * @param int|null $verbosity
     * @param int|null $width
     * @param bool $escape_tags
     * @return bool
     * @throws Exception
     */
    function writeLn(string $message, int $verbosity = null, int $width = null, bool $escape_tags = null)
    {
        return $this->write($message . PHP_EOL, $verbosity, $width, $escape_tags);
    }

    /**
     * @param int $count
     * @param int|null $verbosity
     * @return bool
     * @throws Exception
     */
    function lineBreak(int $count = 1, int $verbosity = null){
        return $this->write(str_repeat(PHP_EOL, $count), $verbosity);
    }

    /**
     * Clear screen
     */
    function clear()
    {
        // Clear buffer
        $this->buffer = [];

        // Clear screen
        $this->moveCursorToPosition(2, 0);
        $this->clearFromCursorDown();
        $this->moveCursorToStartPosition();
        $this->clearFromCursorDown();
    }

    /**
     * @param int $position
     * @param string $message
     * @param int|null $verbosity
     * @param int|null $width
     * @param bool|null $escape_tags
     * @return bool
     * @throws Exception
     */
    function writeAtBufferPosition(int $position, string $message, int $verbosity = null, int $width = null, bool $escape_tags = null)
    {
        // Check verbosity
        $verbosity = $verbosity ?? Command::VERBOSITY_NORMAL;
        if ($verbosity > $this->verbosity) {
            return false;
        }

        // Format message and resolve update
        $message = $this->prepareMessage($message, $width, $escape_tags);
        $this->displayContentAtBufferPosition('write', $message, $position);

        return true;
    }

    /**
     * @param int $position
     * @param string $message
     * @param int|null $verbosity
     * @param int|null $width
     * @param bool|null $escape_tags
     * @return bool
     * @throws Exception
     */
    function overwriteAtBufferPosition(int $position, string $message, int $verbosity = null, int $width = null, bool $escape_tags = null)
    {
        // Check verbosity
        $verbosity = $verbosity ?? Command::VERBOSITY_NORMAL;
        if ($verbosity > $this->verbosity) {
            return false;
        }

        // Format message and resolve update
        $message = $this->prepareMessage($message, $width, $escape_tags);
        $this->displayContentAtBufferPosition('overwrite', $message, $position);

        return true;
    }

    /**
     * @param int $position
     * @param string $message
     * @param int|null $verbosity
     * @param int|null $width
     * @param bool|null $escape_tags
     * @return bool
     * @throws Exception
     */
    function prependAtBufferPosition(int $position, string $message, int $verbosity = null, int $width = null, bool $escape_tags = null)
    {
        // Check verbosity
        $verbosity = $verbosity ?? Command::VERBOSITY_NORMAL;
        if ($verbosity > $this->verbosity) {
            return false;
        }

        // Format message and resolve update
        $message = $this->prepareMessage($message, $width, $escape_tags);
        $this->displayContentAtBufferPosition('prepend', $message, $position);

        return true;
    }

    /**
     * @param $type
     * @param $content
     * @param int $position
     * @throws Exception
     */
    function displayContentAtBufferPosition($type, $content, int $position){

        if ($this->getBufferAtPosition($position) === null) {
            throw new InvalidArgumentException('No buffer at position ' . $position);
        }

        $before_after_buffers = $this->getBufferSplitAtPosition($position);
        switch ($type){
            case 'prepend' :
                $update = $content.$before_after_buffers['content'];
                break;

            case 'write' :
                $update = $before_after_buffers['content'].$content;
                break;

            case 'overwrite' :
                $update = $content;
                break;

            default:
                throw new InvalidArgumentException('Unknown "'.$type.'" type');
        }

        // Update buffer
        $new_buffer = $before_after_buffers['before'];
        $new_buffer[] = $update;
        foreach ($before_after_buffers['after'] as $after_buffer_content) {
            $new_buffer[] = $after_buffer_content;
        }
        $this->buffer = $new_buffer;

        // Write message to screen
        if ($this->isatty()) {
            // Move cursor to buffer position

            $buffer_split = $this->getBufferSplitAtPosition($position);
            $start_buffer_position = $this->getContentCursorPosition(implode('',$buffer_split['before']));
            $this->moveCursorToPosition($start_buffer_position['line'], $start_buffer_position['column']);
            $this->clearFromCursorDown();
            $this->put($update . implode('', $before_after_buffers['after']));
        }
        // Write message to file
        else if ($this->isFile()){
            $this->rewind();
            ftruncate($this->getStream()->getHandle(), 0);
            $this->put(implode('', $this->buffer));
        } else {
            $this->put($content);
        }

        // Update cursor position
        $tip_cursor_position = $this->getTipCursorPosition();
        $this->updateCursorPosition($tip_cursor_position['line'], $tip_cursor_position['column']);
    }

    /**
     * @param int $position
     * @return mixed|null
     */
    function getBufferAtPosition(int $position)
    {
        return $this->buffer[$position] ?? null;
    }

    /**
     * @param int $position
     * @return array
     */
    function getBufferSplitAtPosition(int $position)
    {
        $buffer = $this->getBuffer();
        $before_section_buffer = $buffer;
        $after_section_buffer = array_splice($before_section_buffer, $position);
        $content = array_shift($after_section_buffer);
        return ['before' => $before_section_buffer, 'content' => $content, 'after' => $after_section_buffer];
    }

    /**
     * Returns writing buffer
     */
    function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Overrides writing buffer
     * @param array $buffer
     */
    function setBufferRef(array &$buffer)
    {
        $this->buffer = &$buffer;
    }
    /**
     * Return buffer reference
     *
     * @return array
     */
    function &getBufferRef()
    {
        return $this->buffer;
    }

    /*
     * Cursor methods
     */

    /**
     * @return array
     */
    function getCursorPosition()
    {
        return $this->cursor_position;
    }

    /**
     * @param array $cursor_position
     */
    function setCursorPositionRef(array &$cursor_position)
    {
        $this->cursor_position = &$cursor_position;
    }

    /**
     * @return array
     */
    function &getCursorPositionRef()
    {
        return $this->cursor_position;
    }

    /**
     * @param int $line
     * @param int $column
     */
    function moveCursorToPosition(int $line, int $column)
    {
        // Resolve destination position
        $destination_position = ['line' => $line, 'column' => $column];

        // Resolve current cursor position
        $cursor_position = $this->getCursorPosition();

        // Move cursor
        $line_offset = $cursor_position['line'] - $destination_position['line'];
        $this->moveCursorLeft($cursor_position['column']);
        if ($line_offset >= 0) {
            $this->moveCursorUp($line_offset);
        } else {
            $this->moveCursorDown(-$line_offset);
        }
        $this->moveCursorRight($destination_position['column']);
        $this->updateCursorPosition($destination_position['line'], $destination_position['column']);
    }

    /**
     * Move cursor the the start position
     */
    function moveCursorToStartPosition()
    {
        $this->moveCursorToPosition(1, 0);
    }

    /**
     * Move cursor the the tip position
     */
    function moveCursorToTipPosition()
    {
        $tip_position = $this->getTipCursorPosition();
        $this->moveCursorToPosition($tip_position['line'], $tip_position['column']);
    }

    /**
     * @return array
     */
    function getTipCursorPosition()
    {
        return $this->getContentCursorPosition(implode('', $this->getBuffer()));
    }

    /**
     * @param string $content
     * @return array
     */
    function getContentCursorPosition(string $content)
    {
        if($this->formatter !== null){
            $content = $this->formatter->unFormat($content);
        }
        $line = mb_substr_count($content, PHP_EOL) + 1;
        $last_line = trim(substr($content, strrpos($content, PHP_EOL)), PHP_EOL);
        $column = mb_strlen($last_line);

        return ['line' => $line, 'column' => $column];
    }
    

    /*
    * Ansi methods
    */
    function writeAnsi($code){
        $this->getStream()->put($code);
    }

    function moveCursor(int $line, int $column)
    {
        $this->writeAnsi("\033[3;" . $line . ";" . $column . "t");
    }
    function moveCursorUp(int $lines)
    {
        if ($lines < 1) {
            return;
        }
        $this->writeAnsi("\033[" . $lines . "A");
    }

    function moveCursorDown(int $lines)
    {
        if ($lines < 1) {
            return;
        }
        $this->writeAnsi("\033[" . $lines . "B");
    }

    function moveCursorRight(int $columns)
    {
        if ($columns < 1) {
            return;
        }
        $this->writeAnsi("\033[" . $columns . "C");
    }

    function moveCursorLeft(int $columns)
    {
        if ($columns < 1) {
            return;
        }
        $this->writeAnsi("\033[" . $columns . "D");
    }

    function moveCursorToUpperLeftCorner()
    {
        $this->writeAnsi("\033[H");
    }

    function saveCursorPosition()
    {
        $this->writeAnsi("\0337");
    }

    function restoreCursorPosition()
    {
        $this->writeAnsi("\0338");
    }
    function clearFromCursorDown()
    {
        $this->writeAnsi("\033[0J");
    }

    function clearFromCursorUp()
    {
        $this->writeAnsi("\033[1J");
    }

    function clearLineFromCursorRight()
    {
        $this->writeAnsi("\033[K");
    }

    function clearLineFromCursorLeft()
    {
        $this->writeAnsi("\033[1K");
    }

    function clearEntireScreen()
    {
        $this->writeAnsi("\033[2J");
    }

    function clearEntireLine()
    {
        $this->writeAnsi("\033[2K");
    }

    function clearAll()
    {
        $this->writeAnsi("\033[2J");
    }

    public function enableCursor()
    {
        $this->writeAnsi("\033[?25h");
    }

    public function disableCursor()
    {
        $this->writeAnsi("\033[?25l");
    }

    function turnBoldModeOn()
    {
        $this->writeAnsi("\033[1m");
    }

    function turnLowIntensityModeOn()
    {
        $this->writeAnsi("\033[2m");
    }

    function turnUnderlineModeOn()
    {
        $this->writeAnsi("\033[4m");
    }

    function turnBlinkingModeOn()
    {
        $this->writeAnsi("\033[5m");
    }

    function turnReverseVideoModeOn()
    {
        $this->writeAnsi("\033[7m");
    }

    function turnInvisibleTextModeOn()
    {
        $this->writeAnsi("\033[8m");
    }

    function turnOffCharacterAttributes()
    {
        $this->writeAnsi("\033[0m");
    }

    function setWindowSize(int $width, int $height)
    {
        $this->writeAnsi("\033[8;" . $height . ";" . $width . "t");
    }

    function getWindowWidth() : int
    {
        return (int) PhpHelper::shellExec('tput cols');
    }

    function getWindowHeight() : int
    {
        return (int) PhpHelper::shellExec('tput lines');
    }

    function getWindowSize()
    {
        return [
            'width' => $this->getWindowWidth(),
            'height' => $this->getWindowHeight()
        ];
    }

    /**
     * @throws Exception
     */
    function windowMinimize()
    {
        $this->writeAnsi("\033[2t");
    }
    /**
     * Restore the window (de-minimize).
     */
    function windowRestore()
    {
        $this->writeAnsi("\033[1t");
    }
    /**
     * Raise the window to the front of the stacking order.
     */
    function windowRaise()
    {
        $this->writeAnsi("\033[5t");
    }
    /**
     * Lower the window to the bottom of the stacking order.
     */
    function windowLower()
    {
        $this->writeAnsi("\033[6t");
    }


    /**
     * @param string $message
     * @param int|null $width
     * @param bool|null $escape_tags
     * @return mixed|string
     * @throws Exception
     */
    protected function prepareMessage(string $message, int $width = null, bool $escape_tags = null){
        if($this->formatter === null){
            return $message;
        }

        if($escape_tags !== null){
            if ($escape_tags) {
                $message = $this->formatter->raw($message, $width);
            } else {
                $message = $this->formatter->format($message, $width);
            }
        } else if($this->is_formatting !== null){
            if ($this->is_formatting) {
                $message = $this->formatter->format($message, $width);
            } else {
                $message = $this->formatter->plain($message, $width);
            }
        } else {
            if($this->isatty()){
                $message = $this->formatter->format($message, $width);
            } else {
                $message = $this->formatter->plain($message, $width);
            }
        }
        return $message;
    }

    /**
     * @param int $line
     * @param int $column
     */
    protected function updateCursorPosition(int $line, int $column)
    {
        $this->cursor_position['line'] = $line;
        $this->cursor_position['column'] = $column;
    }

    /**
     * @return Stream
     */
    protected function getStream(){
        return $this->stream;
    }

}