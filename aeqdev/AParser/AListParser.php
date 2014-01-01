<?php

/**
 * http://aeqdev.com/tools/php/aparser/
 * v 1.0
 *
 * Copyright © 2013 Krylosov Maksim <Aequiternus@gmail.com>
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace aeqdev\AParser;

/**
 * Simple list parser.
 */
class AListParser extends \aeqdev\AParser
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
     * @var type
     */
    public $parseItem;

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
        if (!isset($parseItem)) {
            $parseItem = isset($this->parseItem) ? $this->parseItem : [$this, 'parseItem'];
        }

        $r = [];

        if (!empty($beginOfList)) {
            $this->seekTo($beginOfList, $buffer);
        }

        while (false !== $this->seekTo($beginOfItem, $buffer)) {
            $item = $parseItem();
            if (isset($item)) {
                $r [] = $item;
            }
        }

        return $r;
    }

}
