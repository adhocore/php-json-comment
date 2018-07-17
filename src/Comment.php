<?php

namespace Ahc\Json;

/**
 * JSON comment stripper.
 *
 * @author Jitendra Adhikari <jiten.adhikary@gmail.com>
 */
class Comment
{
    /** @var bool If current char is within a string */
    protected $inStr   = false;

    /** @var int Lines of comments 0 = no comment, 1 = single line, 2 = multi lines */
    protected $comment = 0;

    /**
     * Strip comments from JSON string.
     *
     * @param string $json
     *
     * @return string The comment stripped JSON.
     */
    public function strip($json)
    {
        if (!\preg_match('%\/(\/|\*)%', $json)) {
            return $json;
        }

        list($index, $return, $char) = [-1, '', ''];

        while (isset($json[++$index])) {
            list($prev, $char) = [$char, $json[$index]];

            $charnext = $char . (isset($json[$index + 1]) ? $json[$index + 1] : '');
            if ($this->inStringOrCommentEnd($prev, $char, $charnext)) {
                $return .= $char;

                continue;
            }

            $wasSingle = 1 === $this->comment;
            if ($this->hasCommentEnded($char, $charnext) && $wasSingle) {
                $return = \rtrim($return) . $char;
            }

            $index += $charnext === '*/' ? 1 : 0;
        }

        return $return;
    }

    protected function inStringOrCommentEnd($prev, $char, $charnext)
    {
        if (0 === $this->comment && $char === '"' && $prev !== '\\') {
            $this->inStr = !$this->inStr;
        }

        if (!$this->inStr && 0 === $this->comment) {
            $this->comment = $charnext === '//' ? 1 : ($charnext === '/*' ? 2 : 0);
        }

        return $this->inStr || 0 === $this->comment;
    }

    protected function hasCommentEnded($char, $charnext)
    {
        $singleEnded = $this->comment === 1 && $char == "\n";
        $multiEnded  = $this->comment === 2 && $charnext == '*/';

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
     * @return mixed
     *
     * @throws \RuntimeException When decode fails.
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
}
