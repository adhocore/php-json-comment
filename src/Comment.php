<?php

namespace Ahc\Json;

/**
 * JSON comment stripper.
 *
 * @author Jitendra Adhikari <jiten.adhikary@gmail.com>
 */
class Comment
{
    /**
     * Strip comments from JSON string.
     *
     * @param  string $json
     *
     * @return string The comment stripped JSON.
     */
    public function strip($json)
    {
        if (!preg_match('%\/(\/|\*)%', $json)) {
            return $json;
        }

        $index   = -1;
        $comment = $inStr = false;
        $return  = $char  = '';

        while (isset($json[++$index])) {
            $prev = $char;
            $char = $json[$index];

            if (!$comment && $char === '"' && $prev !== '\\') {
                $inStr = !$inStr;
            }

            $next = isset($json[$index + 1]) ? $json[$index + 1] : '';

            if (!$inStr && !$comment && $char . $next === '//') {
                $comment = 'single';
            } elseif (!$inStr && !$comment && $char . $next === '/*') {
                $comment = 'multi';
            }

            if ($inStr || !$comment) {
                $return .= $char;

                continue;
            }

            if (($comment === 'single' && $char == "\n") ||
                ($comment === 'multi'  && $char . $next == "*/")
            ) {
                // Cosmetic fix only!
                if ($comment === 'single') {
                    $return = rtrim($return) . $char;
                }
                $comment = false;
            }

            $index += $char . $next === '*/' ? 1 : 0;
        }

        return $return;
    }

    /**
     * Strip comments and decode JSON string.
     *
     * @param  string       $json
     * @param  bool|boolean $assoc
     * @param  int|integer  $depth
     * @param  int|integer  $options
     *
     * @see http://php.net/json_decode [JSON decode native function]
     *
     * @return mixed
     */
    public function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($this->strip($json), $assoc, $depth, $options);
    }
}
