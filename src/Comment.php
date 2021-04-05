<?php

/*
 * This file is part of the PHP-JSON-COMMENT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Json;

/**
 * JSON comment and trailing comma stripper.
 *
 * @author Jitendra Adhikari <jiten.adhikary@gmail.com>
 */
class Comment
{
    /** @var int The current index being scanned */
    protected $index = -1;

    /** @var bool If current char is within a string */
    protected $inStr = false;

    /** @var int Lines of comments 0 = no comment, 1 = single line, 2 = multi lines */
    protected $comment = 0;

    /** @var int Holds the backtace position of a possibly trailing comma */
    protected $commaPos = -1;

    /**
     * Strip comments from JSON string.
     *
     * @param string $json
     *
     * @return string The comment stripped JSON.
     */
    public function strip($json)
    {
        if (!\preg_match('%\/(\/|\*)%', $json) && !\preg_match('/,\s*(\}|\])/', $json)) {
            return $json;
        }

        $this->reset();

        return $this->doStrip($json);
    }

    protected function reset()
    {
        $this->index   = -1;
        $this->inStr   = false;
        $this->comment = 0;
    }

    protected function doStrip($json)
    {
        $return = '';

        while (isset($json[++$this->index])) {
            list($prev, $char, $next) = $this->getSegments($json);

            $return = $this->checkTrail($char, $return);

            if ($this->inStringOrCommentEnd($prev, $char, $char . $next)) {
                $return .= $char;

                continue;
            }

            $wasSingle = 1 === $this->comment;
            if ($this->hasCommentEnded($char, $char . $next) && $wasSingle) {
                $return = \rtrim($return) . $char;
            }

            $this->index += $char . $next === '*/' ? 1 : 0;
        }

        return $return;
    }

    protected function getSegments($json)
    {
        return [
            isset($json[$this->index - 1]) ? $json[$this->index - 1] : '',
            $json[$this->index],
            isset($json[$this->index + 1]) ? $json[$this->index + 1] : '',
        ];
    }

    protected function checkTrail($char, $json)
    {
        if ($char === ',' || $this->commaPos === -1) {
            $this->commaPos = $this->commaPos + ($char === ',' ?  1 : 0);

            return $json;
        }

        if (\ctype_digit($char) || \strpbrk($char, '"tfn{[')) {
            $this->commaPos = -1;
        } elseif ($char === ']' || $char === '}') {
            $pos  = \strlen($json) - $this->commaPos - 1;
            $json = \substr($json, 0, $pos) . \ltrim(\substr($json, $pos), ',');

            $this->commaPos = -1;
        } else {
            $this->commaPos += 1;
        }

        return $json;
    }

    protected function inStringOrCommentEnd($prev, $char, $next)
    {
        return $this->inString($char, $prev) || $this->inCommentEnd($next);
    }

    protected function inString($char, $prev)
    {
        if (0 === $this->comment && $char === '"' && $prev !== '\\') {
            $this->inStr = !$this->inStr;
        }

        return $this->inStr;
    }

    protected function inCommentEnd($next)
    {
        if (!$this->inStr && 0 === $this->comment) {
            $this->comment = $next === '//' ? 1 : ($next === '/*' ? 2 : 0);
        }

        return 0 === $this->comment;
    }

    protected function hasCommentEnded($char, $next)
    {
        $singleEnded = $this->comment === 1 && $char == "\n";
        $multiEnded  = $this->comment === 2 && $next == '*/';

        if ($singleEnded || $multiEnded) {
            $this->comment = 0;

            return true;
        }

        return false;
    }

    /**
     * Strip comments and decode JSON string.
     *
     * @param string    $json
     * @param bool|bool $assoc
     * @param int|int   $depth
     * @param int|int   $options
     *
     * @see http://php.net/json_decode [JSON decode native function]
     *
     * @throws \RuntimeException When decode fails.
     *
     * @return mixed
     */
    public function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        $decoded = \json_decode($this->strip($json), $assoc, $depth, $options);

        if (\JSON_ERROR_NONE !== $err = \json_last_error()) {
            $msg = 'JSON decode failed';

            if (\function_exists('json_last_error_msg')) {
                $msg .= ': ' . \json_last_error_msg();
            }

            throw new \RuntimeException($msg, $err);
        }

        return $decoded;
    }

    /**
     * Static alias of decode().
     */
    public static function parse($json, $assoc = false, $depth = 512, $options = 0)
    {
        static $parser;

        if (!$parser) {
            $parser = new static;
        }

        return $parser->decode($json, $assoc, $depth, $options);
    }
}
