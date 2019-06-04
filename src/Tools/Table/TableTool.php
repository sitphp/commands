<?php

// TODO: set column width to undefined

namespace SitPHP\Commands\Tools\Table;

use Exception;
use InvalidArgumentException;
use SitPHP\Commands\Command;
use SitPHP\Commands\Tool;
use SitPHP\Commands\Tools\Section\SectionTool;
use SitPHP\Helpers\Text;

class TableTool extends Tool
{

    // Internal properties
    private $resolved_column_width = [];
    private $table_data = [];
    /** @var $section TableTool */
    private $section;
    private $column_count;
    private $displayed = false;
    private $placed;

    // User properties
    /* @var $style TableStyle */
    private $style;
    private $rows = [];
    private $columns_width;
    private $columns_max_width;
    private $row_heigth;
    private $row_max_heigth;
    /**
     * @var TableManager
     */
    private $manager;
    /**
     * @var string
     */
    private $header;
    /**
     * @var string
     */
    private $footer;


    /**
     * TableTool constructor.
     *
     * @param Command $command
     * @param TableManager $manager
     */
    function __construct(Command $command, TableManager $manager)
    {
        parent::__construct($command);
        $this->manager = $manager;
        $this->setStyle('default');
    }


    /**
     * Table will be displayed in the same position as the first time it was displayed
     *
     * @return $this
     * @throws Exception
     */
    function placeHere()
    {
        /** @var SectionTool section */
        $this->section = $this->tool('section')->placeHere();
        $this->placed = true;
        return $this;
    }

    function isPlaced()
    {
        return $this->placed;
    }

    function setStyle(string $style)
    {
        $style = $this->manager->getStyle($style);
        if ($style === null) {
            throw new InvalidArgumentException('Undefined style ' . $style);
        }
        $this->style = clone $style;;
        return $this;
    }

    function getStyle(){
        return $this->style;
    }

    function setColumnMaxWidth(int $index, int $width)
    {
        $this->columns_max_width[$index] = $width;
        return $this;
    }

    function getColumnMaxWidth(int $index)
    {
        return $this->columns_max_width[$index] ?? null;
    }

    function setColumnWidth(int $index, int $width)
    {
        $this->columns_width[$index] = $width;
        return $this;
    }

    function getColumnWidth(int $index)
    {
        return $this->columns_width[$index] ?? null;
    }

    protected function getResolvedColumnWidth(int $index)
    {
        return $this->resolved_column_width[$index] ?? null;
    }

    function setRowHeight(int $index, int $height)
    {
        $this->row_heigth[$index] = $height;
        return $this;
    }

    function getRowHeight(int $index)
    {
        return $this->row_heigth[$index] ?? null;
    }

    function setRowMaxHeight(int $index, int $height)
    {
        $this->row_max_heigth[$index] = $height;
        return $this;
    }

    function getRowMaxHeight(int $index)
    {
        return $this->row_max_heigth[$index] ?? null;
    }

    function setRows(array $rows, int $verbosity = null)
    {
        if($this->verbosityPasses($verbosity)){
            return $this;
        }
        if (!is_array($rows[0])) {
            $rows = [$rows];
        }
        $this->rows = $rows;
        return $this;
    }

    function addRow($row, int $verbosity = null)
    {
        if($this->verbosityPasses($verbosity)){
            return $this;
        }
        $this->rows[] = $row;
        return $this;
    }

    function prependRow($row, int $verbosity = null){
        if($this->verbosityPasses($verbosity)){
            return $this;
        }
        array_unshift($this->rows, $row);
        return $this;
    }

    function removeRow(int $index, int $verbosity = null){
        if($this->verbosityPasses($verbosity)){
            return $this;
        }
        if(array_key_exists($index, $this->rows)){
            unset($this->rows[$index]);
        }
        return $this;
    }

    function clear(int $verbosity = null)
    {
        if($this->verbosityPasses($verbosity)){
            return $this;
        }
        $this->rows = [];
    }

    function getRow(int $index){
        return $this->rows[$index] ?? null;
    }

    function getAllRows(){
        return $this->rows;
    }

    protected function prepareRow($row){
        if (is_array($row)) {
            $row = array_values($row);
            foreach ($row as $i => $cell) {
                if (is_string($cell)) {
                    $row[$i] = Cell::parse($cell);
                } else if (is_numeric($cell)) {
                    $row[$i] = (new Cell())->setContent((string) $cell);
                } else if (is_bool($cell)) {
                    $cell = $cell ? 'true' : 'false';
                    $row[$i] = (new Cell())->setContent($cell);
                } else if(!$cell instanceof Cell && $cell !== null){
                    throw new InvalidArgumentException('Invalid cell type at column ' . $i . ' : expected string, numeric, bool, null or instance of ' . Cell::class);
                }
            }
        } else if(is_string($row)) {
            if(!$this->manager->hasLine($row)) {
                throw new InvalidArgumentException('Undefined "'.$row.'" linebreak');
            }
            $row = $this->manager->getLine($row);
        } else if($row !== null && !$row instanceof Line) {
            throw new InvalidArgumentException('Invalid $row type : expected string, array, null or instance of '.Line::class);
        }
        return $row;
    }

    protected function prepareAllRows(array $rows){
        $prepared = [];
        foreach($rows as $row){
            $prepared[] = $this->prepareRow($row);
        }
        return $prepared;
    }


    function setPadding(int $padding)
    {
        $this->getStyle()->setPadding($padding);
        return $this;
    }

    function getPadding(){
        return $this->getStyle()->getPadding();
    }

    function setTopBorderChars(string $line_char, string $separation_char)
    {
        $this->getStyle()->setTopBorderChars($line_char, $separation_char);
        return $this;
    }

    function setBottomBorderChars(string $line_char, string $separation_char)
    {
        $this->getStyle()->setBottomBorderChars($line_char, $separation_char);
        return $this;
    }

    function setLeftBorderChars(string $line_char, string $separation_char)
    {
        $this->getStyle()->setLeftBorderChars($line_char, $separation_char);
        return $this;
    }

    function setRightBorderChars(string $line_char, string $separation_char)
    {
        $this->getStyle()->setRightBorderChars($line_char, $separation_char);
        return $this;
    }

    function setCellSeparationChar(string $char)
    {
        $this->getStyle()->setCellSeparationChar($char);
        return $this;
    }

    function setCornerChars(string $top_left, string $top_right, string $bottom_right, string $bottom_left)
    {
        $this->getStyle()->setCornerChars($top_left, $top_right, $bottom_right, $bottom_left);
        return $this;
    }

    function setLineChars(string $line_char, string $separation_char)
    {
        $this->getStyle()->setLineChars($line_char, $separation_char);
        return $this;
    }

    function setHeader(string $header){
        $this->header = $header;
        return $this;
    }

    function getHeader(){
        return $this->header;
    }

    function setFooter(string $footer){
        $this->footer = $footer;
        return $this;
    }

    function getFooter(){
        return $this->footer;
    }

    function display(int $verbosity = null)
    {
        $table = $this->getDisplay();
        $this->doDisplay($table, $verbosity);
        $this->displayed = true;
        return $this;
    }

    function getDisplay(){
        $this->table_data = [];
        $rows = $this->prepareAllRows($this->rows);

        if(empty($rows)){
            return '';
        }

        $this->column_count = $this->resolveColumnCount($rows);
        $this->resolved_column_width = $this->resolveColumnsWidth($rows);

        for ($row_line_index = 0; $row_line_index < count($rows); $row_line_index++) {
            $row = $rows[$row_line_index] ?? null;
            $this->insertNewDataRow($row_line_index, $row);
        }

        $table = '';


        $table .= $this->renderTopBorder();
        $row_index = 0;
        while (isset($this->table_data[$row_index])){
            $table .= $this->renderRow($row_index);
            $row_index++;
        };
        $table .= $this->renderBottomBorder();
        return $table;
    }

    protected function resolveColumnsWidth($table_cells)
    {
        $resolved_column_width = [];
        foreach ($table_cells as $i => $row) {
            if ($row === null || $row instanceof Line) {
                continue;
            }
            $current_row_column = 1;
            foreach ($row as $cell) {
                if ($this->getColumnWidth($current_row_column) === null && $cell !== null && $cell->getColspan() == 1) {
                    $text_length = $this->resolveDataTextLength($cell->getContent());
                    $max_width = $this->getColumnMaxWidth($current_row_column);
                    if ($max_width !== null && $max_width < $text_length) {
                        $resolved_column_width[$current_row_column] = $max_width;
                    } else if (!isset($resolved_column_width[$current_row_column]) || $text_length > $resolved_column_width[$current_row_column]) {
                        $resolved_column_width[$current_row_column] = $text_length;
                    }
                }
                if ($cell === null) {
                    $current_row_column++;
                } else {
                    $current_row_column += $cell->getColspan();
                }
            }
        }

        for ($i = 1; $i <= $this->column_count; $i++) {
            if (!isset($resolved_column_width[$i])) {
                $resolved_column_width[$i] = $this->getColumnWidth($i) ?? 6;
            }
        }

        return $resolved_column_width;
    }

    protected function resolveColumnCount($table_cells)
    {
        $column_count = 0;
        foreach ($table_cells as $i => $row) {
            if ($row === null || $row instanceof Line) {
                continue;
            }
            $current_row_column = 1;
            foreach ($row as $cell) {
                if ($cell === null) {
                    $current_row_column++;
                } else {
                    $current_row_column += $cell->getColspan();
                }
            }
            $current_row_column--;
            if ($current_row_column > $column_count) {
                $column_count = $current_row_column;
            }
        }
        return $column_count;
    }

    /**
     * Insert a row new inside data table
     *
     * @param int $row_index
     * @param null $row_data
     * @throws Exception
     */
    protected function insertNewDataRow(int $row_index, $row_data = null)
    {
        // Line break
        if ($row_data instanceof Line) {
            $new_row = $row_data;
        }
        // Create row of empty cells
        else if ($row_data === null) {
            $new_row = [];
            for ($i = 0; $i < $this->column_count; $i++) {
                $new_row[$i] = null;
            }
        }
        // $row_data is an array
        else {
            $new_row = [];
            $current_row_column = 0;
            foreach ($row_data as $cell) {
                $new_row[$current_row_column] = $cell;
                $current_row_column++;
                if ($cell === null) {
                    continue;
                }
                // '' to indicate colspan space
                for ($i = 1; $i < $cell->getColspan(); $i++) {
                    $new_row[$current_row_column] = '';
                    $current_row_column++;
                }
            }
            // Fill remaining row columns with free space
            for ($i = $current_row_column; $i < $this->column_count; $i++) {
                $new_row[$i] = null;
            }
        }

        // Insert new row inside table data
        array_splice($this->table_data, $row_index, 0, [$new_row]);
    }

    protected function updateCell(int $row_index, int $column_index, Cell $updated_cell)
    {
        // Insert empty row if cell row doesn't exist or is a line break
        if (!isset($this->table_data[$row_index]) || $this->table_data[$row_index] instanceof Line) {
            $this->insertNewDataRow($row_index);
        }
        // Insert empty row if cell row is not empty within current cell colspan
        else {
            for ($i = 0; $i < $updated_cell->getColspan(); $i++) {
                if ($this->table_data[$row_index][$column_index + $i] !== null) {
                    $this->insertNewDataRow($row_index);
                    break;
                }
            }
        }
        // '' to indicate colspan space
        for ($i = 1; $i< $updated_cell->getColspan(); $i++){
            $this->table_data[$row_index][$column_index + $i] = '';
        }
        $this->table_data[$row_index][$column_index] = $updated_cell;
    }

    /**
     * @param string $data
     * @return int
     * @throws Exception
     */
    protected function resolveDataTextLength(string $data): int
    {
        $style = $this->getCommand()->getManager()->getFormatter();
        $text = $style->plain($data);
        // Resolve text width
        $text_width = 0;
        $cell_text_lines = explode("\n", $text);
        foreach ($cell_text_lines as $cell_text_line) {
            $line_width = mb_strlen($cell_text_line);
            if ($line_width > $text_width) {
                $text_width = $line_width;
            }
        }
        return $text_width;
    }

    protected function doDisplay(string $table, int $verbosity = null)
    {
        // Make sure table starts in new line
        if ($this->isPlaced()) {
            $before_after_buffers = $this->section->getBufferSplit();
            if (!empty($before_after_buffers['before']) && mb_substr(end($before_after_buffers['before']), -1) !== "\n") {
                $table = "\n" . $table;
            }
        } else {
            $buffer = $this->getOutput()->getBuffer();
            if (!empty($buffer) && mb_substr(end($buffer), -1) !== "\n") {
                $table = "\n" . $table;
            }
        }

        if ($this->isPlaced()) {
            $this->section->overwrite($table, $verbosity);
        } else {
            $this->getOutput()->write($table, $verbosity);
        }
    }

    /**
     * Renders a table row
     *
     * @param int $index
     * @return string
     * @throws Exception
     */
    protected function renderRow(int $index)
    {
        $data = $this->table_data[$index];
        if ($data instanceof Line) {
            $row = $this->renderLineBreak($data);
        } else if (is_array($data)) {
            $row_lines = $this->resolveRowLines($index);
            $row = '';
            foreach ($row_lines as $row_line) {
                $row .= $this->renderRowLine($row_line);
            }
        } else {
            throw new Exception('Invalid row format : expected array or instance of '.Line::class);
        }
        return $row;
    }


    protected function renderTopBorder()
    {
        $line_char = $this->getStyle()->getTopBorderLineChar();
        $separation_char = $this->getStyle()->getTopBorderSeparationChar();
        $left_char = $this->getStyle()->getTopLeftCornerChar();
        $right_char= $this->getStyle()->getTopRightCornerChar();

        return $this->renderLine($line_char, $separation_char, $left_char, $right_char, $this->header);
    }

    protected function renderBottomBorder()
    {
        $line_char = $this->getStyle()->getBottomBorderLineChar();
        $separation_char = $this->getStyle()->getBottomBorderSeparationChar();
        $left_char = $this->getStyle()->getBottomLeftCornerChar();
        $right_char= $this->getStyle()->getBottomRightCornerChar();

        return $this->renderLine($line_char, $separation_char, $left_char, $right_char, $this->footer);
    }

    /**
     * @param Line $line_break
     * @return string
     */
    protected function renderLineBreak(Line $line_break)
    {
        return $this->renderLine($line_break->getLineChar(), $line_break->getSeparationChar(), $line_break->getLeftBorderChar(), $line_break->getRightBorderChar(), $line_break->getTitle());
    }

    protected function renderLine(string $line_char = null, string $separation_char = null, string $left_border_char = null, string $right_border_char = null, string $title = null){

        $padding = $this->getStyle()->getPadding();
        $line_chars = [];
        for ($i = 1; $i <= $this->column_count; $i++) {
            $column_width = $this->getResolvedColumnWidth($i) + $padding * 2;
            for($j =1 ; $j<= $column_width; $j++){
                $line_chars[] = $line_char ?? $this->getStyle()->getLineBreaksLineChar();
            }
            if($i < $this->column_count){
                $line_chars[] = $separation_char ?? $this->getStyle()->getLineBreaksSeparationChar();
            }
        }

        if(isset($title)){
            $formatter = $this->getCommand()->getManager()->getFormatter();
            $title_length = mb_strlen($formatter->plain($title)) + 2;
            $line_length = count($line_chars);

            if($line_length - 4 >= $title_length){
                $title_offset =  floor(($line_length - $title_length) / 2);
                array_splice($line_chars, $title_offset, $title_length, [' '.$title.' ']);
            } else {
                array_splice($line_chars, 1, $line_length - 2, [' '.Text::cut($title, $line_length - 7, '...', false).' ']);
            }
        }

        $line = $left_border_char ?? $this->getStyle()->getLeftBorderSeparationChar();
        $line .= implode('', $line_chars);
        $line.= $right_border_char ?? $this->getStyle()->getRightBorderSeparationChar();

        if ($line !== '') {
            $line .= "\n";
        }
        return $line;
    }


    protected function resolveRowLines(int $row_index)
    {
        $row_cells_display = $this->prepareRowCellsDisplay($row_index);
        $row_height = $this->resolveRowHeight($row_index, $row_cells_display);

        // Resolve row lines by column
        $row_columns = [];
        foreach ($row_cells_display as $column_index => $cell_display) {
            if ($cell_display['rowspan'] > 1) {

                // Update next row cell with the rest of the current cell content
                $updated_cell = (new Cell())
                    ->setContent(implode('', array_slice($cell_display['lines'], $row_height)))
                    ->setRowspan($cell_display['rowspan'] - 1)
                    ->setColspan($cell_display['colspan']);
                $this->updateCell($row_index +1, $column_index, $updated_cell);
                $cell_display['lines'] = array_slice($cell_display['lines'], 0, $row_height);
            }


            // Resolve row columns lines
            for ($j = 0; $j < $row_height; $j++) {
                if (!isset($cell_display['lines'][$j])) {
                    $row_columns[$column_index][$j] = [
                        'data' => '',
                        'colspan' => $cell_display['colspan'],
                        'width' => $cell_display['width']
                    ];
                } else {
                    $row_columns[$column_index][$j] = [
                        'data' => $cell_display['lines'][$j],
                        'colspan' => $cell_display['colspan'],
                        'width' => $cell_display['width']
                    ];
                }
            }
        }

        // Resolve row lines
        $row_lines = [];
        foreach ($row_columns as $column_index => $row_column) {
            foreach ($row_column as $j => $row_value) {
                $row_lines[$j][$column_index] = $row_value;
            }
        }

        return $row_lines;
    }

    protected function renderRowLine(array $row_line)
    {
        $formatter = $this->getCommand()->getManager()->getFormatter();

        $padding = $this->getStyle()->getPadding();
        $padding_chars = str_repeat($this->getStyle()->getPaddingChar(), $padding);
        $separation_char = $this->getStyle()->getCellSeparationChar();

        $line = $this->getStyle()->getLeftBorderLineChar() . $padding_chars;
        // Fill given cell data
        $cells_data = [];
        foreach ($row_line as $i => $line_cell) {
            $text_length = mb_strlen($formatter->plain($line_cell['data']));
            $spaces_width = $line_cell['width'] - $text_length;
            $cell_data = $line_cell['data'];
            if ($spaces_width > 0) {
                $cell_data .= str_repeat(' ', $spaces_width);
            }
            $cells_data[] = $cell_data;
        }
        $line .= implode($padding_chars . $separation_char . $padding_chars, $cells_data);
        $line .= $padding_chars . $this->getStyle()->getRightBorderLineChar();
        if ($line !== '') {
            $line .= PHP_EOL;
        }
        return $line;
    }

    protected function resolveRowHeight(int $row_index, array $row_cells_display)
    {
        $row_height_index = $this->getRowHeightIndex($row_index);
        if (null !== $row_height = $this->getRowHeight($row_height_index)) {
            return $row_height;
        }

        $resolved_row_height = 1;
        foreach ($row_cells_display as $cell) {
            $cell_lines_count = count($cell['lines']);
            if ($cell_lines_count <= $resolved_row_height) {
                continue;
            }
            // If rowspan is 1, next row is not defined or next row is a line break
            if ($cell['rowspan'] == 1 || !isset($this->table_data[$cell['row'] + 1]) || $this->table_data[$cell['row'] + 1] instanceof Line) {
                $resolved_row_height = $cell_lines_count;
                continue;
            }

            /*// If there is already a defined cell in current cell space
            for ($i = 0; $i < $cell['colspan']; $i++) {
                if ($this->table_data[$cell['row'] + 1][$cell['column'] + $i] !== null) {
                    $resolved_row_height = $cell_lines_count;
                    continue 2;
                }
            }*/
        }

        $row_max_height = $this->getRowMaxHeight($row_height_index);
        if ($row_max_height !== null && $resolved_row_height > $row_max_height) {
            $resolved_row_height = $row_max_height;
        }

        return $resolved_row_height;
    }

    protected function getRowHeightIndex(int $row_index)
    {
        $row_height_index = 1;
        for ($i = 1; $i <= $row_index; $i++) {
            // If row is not a separator
            if (is_array($this->table_data[$i])) {
                $row_height_index++;
            }
        }
        return $row_height_index;
    }

    protected function prepareRowCellsDisplay(int $row_index)
    {
        $formatter = $this->getCommand()->getManager()->getFormatter();

        $padding = $this->getStyle()->getPadding();
        $row = $this->table_data[$row_index];
        $current_column = 1;
        $row_cells_display = [];
        /** @var Cell $cell */
        foreach ($row as $column_index => $cell) {
            if($cell === ''){
                continue;
            }
            if ($cell === null) {
                $cell_display = [
                    'data' => '',
                    'colspan' => 1,
                    'rowspan' => 1,
                    'row' => $row_index,
                    'column' => $column_index
                ];
            } else {
                $cell_display = [
                    'data' => $cell->getContent(),
                    'colspan' => $cell->getColspan(),
                    'rowspan' => $cell->getRowspan(),
                    'row' => $row_index,
                    'column' => $column_index
                ];
            }

            // Resolve display cell width
            $cell_width = 0;
            for ($j = 1; $j <= $cell_display['colspan']; $j++) {
                if ($j > 1) {
                    $cell_width += $padding * 2 + 1;
                }
                $cell_width += $this->getResolvedColumnWidth($current_column);
                $current_column++;
            }
            $cell_display['width'] = $cell_width;

            // Resolve cell lines
            $cell_data_lines = explode("\n", $formatter->split($cell_display['data'], $cell_width, false, true));
            $cell_display['lines'] = $cell_data_lines;

            // Save row cell display
            $row_cells_display[$column_index] = $cell_display;
        }
        return $row_cells_display;
    }

    protected function verbosityPasses(int $verbosity = null){
        return null !== $verbosity && $verbosity > $this->getRequest()->getVerbosity();
    }
}