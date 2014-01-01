<?php

/**
 * http://aeqdev.com/tools/php/aparser/
 * v 1.0
 *
 * Copyright © 2014 Krylosov Maksim <Aequiternus@gmail.com>
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace aeqdev;

/**
 * Simple text parser.
 * Can parse large files with low memory usage (~ 2 buffers).
 */
class AParser
{

    /**
     * Default buffer length.
     * Parser will not parse string geather than specified buffer length (but can seek).
     * Parser may use memory equals to 2 buffers length.
     * @var int
     */
    public $buffer = 8192;
    /**
     * Default encoding.
     * If no encoding specified, encoding will be detected from http headers.
     * @var string
     */
    public $encoding;
    /**
     * Default stream context.
     * See http://www.php.net/manual/function.stream-context-create.php
     * @var array
     */
    public $context;

    private $file;
    private $buff;
    private $enc;

    /**
     * Open file to parse.
     * Context will be merged with default context.
     * If no encoding specified (class property or argument), encoding will be detected from http headers.
     *
     * @param string $filename File name.
     * @param array $context Stream context.
     * @param string $encoding File encoding.
     * @return bool True on success or false on failure.
     */
    public function open($filename, $context = null, $encoding = null)
    {
        if (isset($this->file)) {
            $this->close();
        }

        $context = array_merge((array)$this->context, (array)$context);
        if (empty($context)) {
            $this->file = fopen($filename, 'rb');
        } else {
            $this->file = fopen($filename, 'rb', null, stream_context_create($context));
        }

        if (false === $this->file) {
            return false;
        }

        if (isset($encoding)) {
            $this->enc = $encoding;
        } else if (isset($this->encoding)) {
            $this->enc = $this->encoding;
        } else if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                $parts = explode(':', $header);
                if (strtolower(trim($parts[0])) == 'content-type') {
                    $parts = explode('=', $parts[1]);
                    $this->enc = trim($parts[1]);
                    break;
                }
            }
        }

        return true;
    }

    /**
     * Close file.
     */
    public function close()
    {
        if (isset($this->file)) {
            fclose($this->file);
            $this->file = null;
            $this->enc = null;
        }
    }

    /**
     * Parse to specified string.
     * Will not parse string geather than buffer length.
     *
     * @param string $string String to find.
     * @param bool $hold Set TRUE to hold parser in current position.
     * @param int $buffer Buffer length.
     * @return string|bool Parsed string from current position to specified string, or FALSE on failure.
     */
    public function parseTo($string, $hold = false, $buffer = null)
    {
        if (!isset($buffer)) {
            $buffer = $this->buffer;
        }
        if (isset($this->enc)) {
            $string = mb_convert_encoding($string, $this->enc);
        }

        $r = '';
        $l = strlen($string);

        if (false === ($p = stripos($this->buff, $string))) {
            if (!($b = fread($this->file, $buffer))) {
                return false;
            }
            $o = max(0, strlen($this->buff) - $l);
            $this->buff .= $b;
            $p = stripos($this->buff, $string, $o);
            if (false === $p) {
                return false;
            }
        }

        $r .= substr($this->buff, 0, $p);

        if (!$hold) {
            $this->buff = substr($this->buff, $p + $l);
        }

        return $r;
    }

    /**
     * Seek to specified string.
     *
     * @param string $string String to find.
     * @param int $buffer Buffer length.
     * @return bool True if string found, false otherwise.
     */
    public function seekTo($string, $buffer = null)
    {
        if (!isset($buffer)) {
            $buffer = $this->buffer;
        }
        if (isset($this->enc)) {
            $string = mb_convert_encoding($string, $this->enc);
        }

        $l = strlen($string);

        while (false === ($p = stripos($this->buff, $string))) {
            if (!($b = fread($this->file, $buffer))) {
                return false;
            }
            $this->buff = substr($this->buff, max(0, strlen($this->buff) - $l)) . $b;
        }

        $this->buff = substr($this->buff, $p + $l);

        return true;
    }

    /**
     * Seek to $from string and parse to $to string.
     *
     * @param string $from Seek to this string.
     * @param string $to Parse to this string.
     * @param int $buffer Buffer length.
     * @return string|boolean Parsed string or FALSE on failure.
     */
    public function parseBetween($from, $to, $holdOnFail = false, $buffer = null)
    {
        if (false === $this->parseTo($from, $holdOnFail, $buffer)) {
            return false;
        }
        return $this->parseTo($to, null, $buffer);
    }

}
