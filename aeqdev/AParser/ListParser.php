<?php

/**
 * http://aeqdev.com/tools/php/aparser/
 * v 1.1
 *
 * Copyright © 2014 Krylosov Maksim <Aequiternus@gmail.com>
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace aeqdev\AParser;

/**
 * Simple list parser.
 */
class ListParser extends \aeqdev\AParser
{

    /**
     * String, list starts from.
     *
     * @var string
     */
    public $beginOfList;
    /**
     * String item starts from.
     *
     * @var string
     */
    public $beginOfItem;
    /**
     * Parse item callback.
     * Define this callback or override parseItem method.
     *
     * @var callback
     */
    public $parseItem;
    /**
     * Print downloading process.
     *
     * @var bool
     */
    public $print;

    /**
     * Parse one list item.
     *
     * @return Parsed item or NULL if no item parsed.
     */
    public function parseItem()
    {
        if (!is_callable($this->parseItem)) {
            throw new \Exception('Define parseItem callback or override parseItem method');
        }
        return call_user_func($this->parseItem);
    }

    /**
     * Parse list.
     *
     * @param string $beginOfList String, list starts from.
     * @param string $beginOfItem String item starts from.
     * @param string $parseItem Parse item callback.
     *                          Define this callback or set parseItem property or override parseItem method.
     * @param int $buffer Buffer length.
     * @return array Result items array.
     */
    public function parseList($beginOfList = null, $beginOfItem = null, $parseItem = null, $buffer = null)
    {
        if (!isset($beginOfList)) {
            $beginOfList = $this->beginOfList;
        }
        if (!isset($beginOfItem)) {
            $beginOfItem = $this->beginOfItem;
        }
        if (isset($parseItem)) {
            $oldParseItem = $this->parseItem;
            $this->parseItem = $parseItem;
        }

        $r = [];

        if (!empty($beginOfList)) {
            $this->seekTo($beginOfList, $buffer);
        }

        while (false !== $this->seekTo($beginOfItem, $buffer)) {
            $item = $this->parseItem();
            if (isset($item)) {
                $r [] = $item;
            }
        }

        if (isset($parseItem)) {
            $this->parseItem = $oldParseItem;
        }

        return $r;
    }

    /**
     * Parse files.
     *
     * @param string $fileList List of file names or urls to parse.
     * @param string $beginOfList String, list starts from.
     * @param string $beginOfItem String item starts from.
     * @param string $parseItem Parse item callback.
     *                          Define this callback or set parseItem property or override parseItem method.
     *                          Should return array [ 'src' => $src, 'dest' => $dest].
     * @param int $buffer Buffer length.
     * @return array Result items array.
     */
    public function parseFiles($fileList, $beginOfList = null, $beginOfItem = null, $parseItem = null, $buffer = null)
    {
        $r = [];

        $fileList = preg_split('`\s+`', trim($fileList));
        $count = count($fileList);
        for ($i = 0; $i < $count; $i++) {
            $fileName = trim($fileList[$i]);
            if (!empty($fileName)) {
                if ($this->print) {
                    printf("%02d/%02d: %s\n", $i + 1, $count, $fileName);
                }
                $this->open($fileName);
                $list = $this->parseList($beginOfList, $beginOfItem, $parseItem, $buffer);
                if (!empty($list)) {
                    $r [] = $list;
                }
            }
        }

        return $r;
    }

}
