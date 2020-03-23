<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/20
 * Time: 15:44
 */

namespace Swozr\Taskr\Server\Tools\OutputStyle;

/**
 * @method int info($messages, $quit = false)
 * @method int note($messages, $quit = false)
 * @method int notice($messages, $quit = false)
 * @method int success($messages, $quit = false)
 * @method int primary($messages, $quit = false)
 * @method int warning($messages, $quit = false)
 * @method int danger($messages, $quit = false)
 * @method int error($messages, $quit = false)
 * Class Output
 * @package Swozr\Taskr\Server\Tools\Output
 */
class Output
{
    /**
     * Normal output stream
     *
     * @var resource|null
     */
    protected $outputStream = STDOUT;

    /**
     * Error output stream
     *
     * @var null|resource
     */
    protected $errorStream = STDERR;

    /**
     * Console window (font/background) color addition processing
     *
     * @var Style
     */
    protected $style;

    const BLOCK_METHODS = [
        // method => style
        'info' => 'info',
        'note' => 'note',
        'notice' => 'notice',
        'success' => 'success',
        'primary' => 'primary',
        'warning' => 'warning',
        'danger' => 'danger',
        'error' => 'error',
    ];

    /**
     * Output constructor.
     *
     * @param null|resource $outputStream
     */
    public function __construct($outputStream = null)
    {
        if ($outputStream) {
            $this->outputStream = $outputStream;
        }

        $this->getStyle();
    }

    /**
     * @param $method
     * @param array $args
     */
    public function __call($method, array $args = [])
    {
        $map = self::BLOCK_METHODS;
        if (!isset($map[$method])) {
            return false;
        }
        $msg = $args[0];
        $quit = $args[1] ?? false;
        $style = $map[$method];
        $text = sprintf('<%s>%s</%s>', $style, $msg, $style);

        return Console::write($text, true, $quit);
    }

    public static function mlist(array $data, array $opts = []){
        $stringList  = [];
        $ignoreEmpty = $opts['ignoreEmpty'] ?? true;
        $lastNewline = true;

        $opts['returned'] = true;
        if (isset($opts['lastNewline'])) {
            $lastNewline = $opts['lastNewline'];
            unset($opts['lastNewline']);
        }

        foreach ($data as $title => $list) {
            if ($ignoreEmpty && !$list) {
                continue;
            }

            $stringList[] = self::aList($list, $title, $opts);
        }

        Console::write(implode("\n", $stringList), $lastNewline);
    }

    public static function aList($data, string $title = '', array $opts = []){
        $string = '';
        $opts   = array_merge([
            'leftChar'    => '  ',
            // 'sepChar' => '  ',
            'keyStyle'    => 'info',
            'keyMinWidth' => 8,
            'titleStyle'  => 'comment',
            'returned'    => false,
            'ucFirst'     => false,
            'lastNewline' => true,
        ], $opts);

        // title
        if ($title) {
            $title  = ucwords(trim($title));
            $string .= self::wrap($title, $opts['titleStyle']) . PHP_EOL;
        }

        // handle item list
        $string .= self::spliceKeyValue((array)$data, $opts);

        // return formatted string.
        if ($opts['returned']) {
            return $string;
        }

        return Console::write($string, $opts['lastNewline']);
    }

    public static function Panel($data, string $title = 'Information Panel', array $opts = []){
        if (!$data) {
            Console::write('<info>No data to display!</info>');
            return -2;
        }

        $opts = array_merge([
            'borderChar' => '*',
            'sepChar'    => ' | ',
            'ucFirst'    => true,
            'titleStyle' => 'bold',
            'leftIndent' => '  ',
        ], $opts);

        $data  = is_array($data) ? array_filter($data) : [trim($data)];
        $title = trim($title);

        $panelData  = []; // [ 'label' => 'value' ]
        $borderChar = $opts['borderChar'];
        $leftIndent = $opts['leftIndent'];

        $labelMaxWidth = 0; // if label exists, label max width
        $valueMaxWidth = 0; // value max width

        foreach ($data as $label => $value) {
            // label exists
            if (!is_numeric($label)) {
                $width = mb_strlen($label, 'UTF-8');
                // save max value
                $labelMaxWidth = $width > $labelMaxWidth ? $width : $labelMaxWidth;
            }

            // translate array to string
            if (is_array($value)) {
                $temp = '';

                /** @var array $value */
                foreach ($value as $key => $val) {
                    if (is_bool($val)) {
                        $val = $val ? 'True' : 'False';
                    } else {
                        $val = (string)$val;
                    }

                    $temp .= (!is_numeric($key) ? "$key: " : '') . "<info>$val</info>, ";
                }

                $value = rtrim($temp, ' ,');
            } elseif (is_bool($value)) {
                $value = $value ? 'True' : 'False';
            } else {
                $value = trim((string)$value);
            }

            // get value width
            /** @var string $value */
            $value = trim($value);
            $width = mb_strlen(strip_tags($value), 'UTF-8'); // must clear style tag

            $valueMaxWidth     = $width > $valueMaxWidth ? $width : $valueMaxWidth;
            $panelData[$label] = $value;
        }

        $border     = null;
        $panelWidth = $labelMaxWidth + $valueMaxWidth;

        $opts['leftChar'] = $leftIndent . $borderChar . ' ';
        $opts['keyMaxWidth'] = $labelMaxWidth;

        Console::startBuffer();

        // output title
        if ($title) {
            $title       = ucwords($title);
            $titleStyle  = $opts['titleStyle'] ?: 'bold';
            $titleLength = mb_strlen($title, 'UTF-8');
            $panelWidth  = $panelWidth > $titleLength ? $panelWidth : $titleLength;
            $lenValue    = (int)(ceil($panelWidth / 2) - ceil($titleLength / 2));
            $indentSpace = str_pad(' ', $lenValue + 2 * 2, ' ');
            Console::write("$leftIndent{$indentSpace}<{$titleStyle}>{$title}</{$titleStyle}>");
        }

        // output panel top border
        if ($borderChar) {
            $border = str_pad($borderChar, $panelWidth + (3 * 4), $borderChar);
            Console::write($leftIndent . $border);
        }

        // output panel body
        $panelContent = self::spliceKeyValue($panelData, $opts);

        // already exists "\n"
        Console::write($panelContent, false);

        // output panel bottom border
        if ($border) {
            Console::write("{$leftIndent}{$border}\n");
        }

        Console::flushBuffer();
        unset($panelData);
        return 0;
    }

    /**
     * splice Array
     * @param  array $data
     * e.g [
     *     'system'  => 'Linux',
     *     'version'  => '4.4.5',
     * ]
     * @param  array $opts
     * @return string
     */
    public static function spliceKeyValue(array $data, array $opts = []): string
    {
        $text = '';
        $opts = array_merge([
            'leftChar'    => '',   // e.g '  ', ' * '
            'sepChar'     => ' ',  // e.g ' | ' OUT: key | value
            'keyStyle'    => '',   // e.g 'info','comment'
            'valStyle'    => '',   // e.g 'info','comment'
            'keyMinWidth' => 8,
            'keyMaxWidth' => null, // if not set, will automatic calculation
            'ucFirst'     => true,  // upper first char
        ], $opts);

        if (!is_numeric($opts['keyMaxWidth'])) {
            $opts['keyMaxWidth'] = self::getKeyMaxWidth($data);
        }

        // compare
        if ((int)$opts['keyMinWidth'] > $opts['keyMaxWidth']) {
            $opts['keyMaxWidth'] = $opts['keyMinWidth'];
        }

        $keyStyle = trim($opts['keyStyle']);

        foreach ($data as $key => $value) {
            $hasKey = !is_int($key);
            $text   .= $opts['leftChar'];

            if ($hasKey && $opts['keyMaxWidth']) {
                $key  = str_pad($key, $opts['keyMaxWidth'], ' ');
                $text .= self::wrap($key, $keyStyle) . $opts['sepChar'];
            }

            // if value is array, translate array to string
            if (is_array($value)) {
                $temp = '';

                /** @var array $value */
                foreach ($value as $k => $val) {
                    if (is_bool($val)) {
                        $val = $val ? '(True)' : '(False)';
                    } else {
                        $val = is_scalar($val) ? (string)$val : gettype($val);
                    }

                    $temp .= (!is_numeric($k) ? "$k: " : '') . "$val, ";
                }

                $value = rtrim($temp, ' ,');
            } elseif (is_bool($value)) {
                $value = $value ? '(True)' : '(False)';
            } else {
                $value = (string)$value;
            }

            $value = $hasKey && $opts['ucFirst'] ? ucfirst($value) : $value;
            $text  .= self::wrap($value, $opts['valStyle']) . "\n";
        }

        return $text;
    }

    /**
     * wrap a color style tag
     *
     * @param string $text
     * @param string $tag
     *
     * @return string
     */
    public static function wrap(string $text, string $tag): string
    {
        if (!$text || !$tag) {
            return $text;
        }

        return "<$tag>$text</$tag>";
    }

    /**
     * get key Max Width
     *
     * @param array $data
     *     [
     *     'key1'      => 'value1',
     *     'key2-test' => 'value2',
     *     ]
     * @param bool  $expectInt
     *
     * @return int
     */
    public static function getKeyMaxWidth(array $data, bool $expectInt = false): int
    {
        $keyMaxWidth = 0;

        foreach ($data as $key => $value) {
            // key is not a integer
            if (!$expectInt || !is_numeric($key)) {
                $width       = mb_strlen((string)$key, 'UTF-8');
                $keyMaxWidth = $width > $keyMaxWidth ? $width : $keyMaxWidth;
            }
        }

        return $keyMaxWidth;
    }

    /***************************************************************************
     * Output buffer
     ***************************************************************************/

    /**
     * start buffering
     */
    public function startBuffer()
    {
        Console::startBuffer();
    }

    /**
     * clear buffering
     */
    public function clearBuffer()
    {
        Console::clearBuffer();
    }

    /**
     * stop buffering and flush buffer text
     * {@inheritdoc}
     *
     * @see Console::stopBuffer()
     */
    public function stopBuffer(bool $flush = true, $nl = false, $quit = false, array $opts = [])
    {
        Console::stopBuffer($flush, $nl, $quit, $opts);
    }

    /**
     * stop buffering and flush buffer text
     * {@inheritdoc}
     */
    public function flush(bool $nl = false, $quit = false, array $opts = [])
    {
        Console::flushBuffer($nl, $quit, $opts);
    }

    /***************************************************************************
     * Output Message
     ***************************************************************************/

    /**
     * Read input information
     *
     * @param string $question 若不为空，则先输出文本
     * @param bool $nl true 会添加换行符 false 原样输出，不添加换行符
     *
     * @return string
     */
    public function read($question = null, $nl = false): string
    {
        return Console::read($question, $nl);
    }

    /**
     * Write a message to standard error output stream.
     *
     * @param string $text
     * @param boolean $nl True (default) to append a new line at the end of the output string.
     *
     * @return int
     */
    public function stderr(string $text = '', $nl = true): int
    {
        return Console::write($text, $nl, [
            'steam' => $this->errorStream,
        ]);
    }

    /***************************************************************************
     * Getter/Setter
     ***************************************************************************/

    /**
     * @return Style
     */
    public function getStyle(): Style
    {
        if (!$this->style) {
            $this->style = Style::instance();
        }

        return $this->style;
    }


    /**
     * Method to get property ErrorStream
     *
     * @return resource|null
     */
    public function getOutputStream()
    {
        return $this->outputStream;
    }

    /**
     * Method to set property outputStream
     *
     * @param $outStream
     *
     * @return $this
     */
    public function setOutputStream($outStream): self
    {
        $this->outputStream = $outStream;

        return $this;
    }

    /**
     * Method to get property ErrorStream
     *
     * @return resource|null
     */
    public function getErrorStream()
    {
        return $this->errorStream;
    }

    /**
     * Method to set property errorStream
     *
     * @param $errorStream
     *
     * @return $this
     */
    public function setErrorStream($errorStream): self
    {
        $this->errorStream = $errorStream;

        return $this;
    }

    /**
     * @inheritdoc
     * @see Show::writeln()
     */
    public function writeln($text, $quit = false, array $opts = []): int
    {
        return Console::write($text, true, $quit, $opts);
    }
    /**
     * @inheritdoc
     * @see Show::writef()
     */
    public function writef(string $format, ...$args): int
    {

        return Console::write(sprintf($format, ...$args));
    }
}