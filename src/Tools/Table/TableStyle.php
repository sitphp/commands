<?php

namespace SitPHP\Commands\Tools\Table;

use Exception;
use InvalidArgumentException;

class TableStyle
{

    // User properties
    protected $padding = 1;
    protected $top_left_corner_char = '+';
    protected $top_right_corner_char = '+';
    protected $bottom_left_corner_char = '+';
    protected $bottom_right_corner_char = '+';
    protected $top_border_line_char = '-';
    protected $bottom_border_line_char = '-';
    protected $cell_separation_char = '|';
    protected $left_border_line_char = '|';
    protected $right_border_line_char = '|';
    protected $top_border_separation_char = '+';
    protected $bottom_border_separation_char = '+';
    protected $left_border_separation_char = '+';
    protected $right_border_separation_char = '+';
    protected $line_break_separation_char = '+';
    protected $line_break_line_char = '-';
    protected $padding_char = ' ';

    /**
     * Set table style padding
     *
     * @param int $padding
     * @return $this
     * @throws Exception
     */
    function setPadding(int $padding)
    {
        if ($padding < 0) {
            throw new InvalidArgumentException('Padding cannot be negative');
        }
        $this->padding = $padding;
        return $this;
    }

    /**
     * Return table padding
     *
     * @return int
     */
    function getPadding()
    {
        return $this->padding;
    }

    /**
     * Set padding char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setPaddingChar(string $char)
    {
        $this->padding_char = $char;
        return $this;
    }

    /**
     * Return padding char
     *
     * @return string
     */
    function getPaddingChar()
    {
        return $this->padding_char;
    }

    /*
     * Corner chars
     */

    function setCornerChars($top_left, $top_right, $bottom_right, $bottom_left)
    {
        $this->setTopLeftCornerChar($top_left);
        $this->setTopRightCornerChar($top_right);
        $this->setBottomRightCornerChar($bottom_right);
        $this->setBottomLeftCornerChar($bottom_left);
        return $this;
    }

    /**
     * Set top left corner char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setTopLeftCornerChar(string $char)
    {
        $this->top_left_corner_char = $char;
        return $this;
    }

    /**
     * Return top left corner char
     *
     * @return string
     */
    function getTopLeftCornerChar()
    {
        return $this->top_left_corner_char;
    }

    /**
     * Set top right corner char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setTopRightCornerChar(string $char)
    {
        $this->top_right_corner_char = $char;
        return $this;
    }

    /**
     * Return top right corner char
     *
     * @return string
     */
    function getTopRightCornerChar()
    {
        return $this->top_right_corner_char;
    }

    /**
     * Set bottom left corner char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setBottomLeftCornerChar(string $char)
    {
        $this->bottom_left_corner_char = $char;
        return $this;
    }

    function getBottomLeftCornerChar()
    {
        return $this->bottom_left_corner_char;
    }

    /**
     * Set bottom right corner char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setBottomRightCornerChar(string $char)
    {
        $this->bottom_right_corner_char = $char;
        return $this;
    }

    function getBottomRightCornerChar()
    {
        return $this->bottom_right_corner_char;
    }


    /*
     * Border chars
     */
    /**
     * Set top border line char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setTopBorderLineChar(string $char)
    {
        $this->top_border_line_char = $char;
        return $this;
    }

    function getTopBorderLineChar()
    {
        return $this->top_border_line_char;
    }

    /**
     * Set bottom border line char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setBottomBorderLineChar(string $char)
    {
        $this->bottom_border_line_char = $char;
        return $this;
    }

    function getBottomBorderLineChar()
    {
        return $this->bottom_border_line_char;
    }

    /**
     * Set left border line char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setLeftBorderLineChar(string $char)
    {
        $this->left_border_line_char = $char;
        return $this;
    }

    function getLeftBorderLineChar()
    {
        return $this->left_border_line_char;
    }

    /**
     * Set right border line char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setRightBorderLineChar(string $char)
    {
        $this->right_border_line_char = $char;
        return $this;
    }

    function getRightBorderLineChar()
    {
        return $this->right_border_line_char;
    }

    /**
     * Set top border separation line char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setTopBorderSeparationChar(string $char)
    {
        $this->top_border_separation_char = $char;
        return $this;
    }

    function getTopBorderSeparationChar()
    {
        return $this->top_border_separation_char;
    }

    /**
     * Set bottom border separation line char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setBottomBorderSeparationChar(string $char)
    {
        $this->bottom_border_separation_char = $char;
        return $this;
    }

    function getBottomBorderSeparationChar()
    {
        return $this->bottom_border_separation_char;
    }

    /**
     * Set left border separation line char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setLeftBorderSeparationChar(string $char)
    {
        $this->left_border_separation_char = $char;
        return $this;
    }

    function getLeftBorderSeparationChar()
    {
        return $this->left_border_separation_char;
    }

    /**
     * Set right border separation line char
     *
     * @param string $char
     * @return $this
     * @throws Exception
     */
    function setRightBorderSeparationChar(string $char)
    {
        $this->right_border_separation_char = $char;
        return $this;
    }

    function getRightBorderSeparationChar()
    {
        return $this->right_border_separation_char;
    }

    function clearBorderLeft()
    {
        $this->setLeftBorderSeparationChar('')
            ->setLeftBorderLineChar('')
            ->setTopLeftCornerChar('')
            ->setBottomLeftCornerChar('');
        return $this;
    }

    function clearBorderRight()
    {
        $this->setRightBorderSeparationChar('')
            ->setRightBorderLineChar('')
            ->setTopRightCornerChar('')
            ->setBottomRightCornerChar('');
        return $this;
    }

    function clearBorderTop()
    {
        $this->setTopLeftCornerChar('')
            ->setTopRightCornerChar('')
            ->setTopBorderLineChar('')
            ->setTopBorderSeparationChar('');
        return $this;
    }

    function clearBorderBottom()
    {
        $this->setBottomLeftCornerChar('')
            ->setBottomRightCornerChar('')
            ->setBottomBorderLineChar('')
            ->setBottomBorderSeparationChar('');
        return $this;
    }

    function setTopBorderChars($line_char, $separation_char)
    {
        $this->setTopBorderLineChar($line_char);
        $this->setTopBorderSeparationChar($separation_char);
        return $this;
    }

    function setBottomBorderChars($line_char, $separation_char)
    {
        $this->setBottomBorderLineChar($line_char);
        $this->setBottomBorderSeparationChar($separation_char);
        return $this;
    }

    function setLeftBorderChars($line_char, $separation_char)
    {
        $this->setLeftBorderLineChar($line_char);
        $this->setLeftBorderSeparationChar($separation_char);
        return $this;
    }

    function setRightBorderChars($line_char, $separation_char)
    {
        $this->setRightBorderLineChar($line_char);
        $this->setRightBorderSeparationChar($separation_char);
        return $this;
    }

    /*
     * Cell chars
     */
    function setCellSeparationChar(string $char)
    {
        $this->cell_separation_char = $char;
        return $this;
    }

    function getCellSeparationChar()
    {
        return $this->cell_separation_char;
    }


    /*
     * Linebreak chars
     */
    function setLineBreaksSeparationChar(string $char)
    {
        $this->line_break_separation_char = $char;
        return $this;
    }

    function getLineBreaksSeparationChar()
    {
        return $this->line_break_separation_char;
    }

    function setLineBreaksLineChar(string $char)
    {
        $this->line_break_line_char = $char;
        return $this;
    }

    function getLineBreaksLineChar()
    {
        return $this->line_break_line_char;
    }

    function setLineChars($line_char, $separation_char)
    {
        $this->setLineBreaksLineChar($line_char);
        $this->setLineBreaksSeparationChar($separation_char);
        return $this;
    }

    function clearLineBreaks()
    {
        $this->setLineBreaksLineChar('')
            ->setLineBreaksSeparationChar('');
        return $this;
    }
}