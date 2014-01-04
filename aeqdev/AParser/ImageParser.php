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
 * Image parser.
 */
class ImageParser extends ListParser
{

    /**
     * Parse one list item.
     * Overriding this method, use
     *      parent::parseItem([ 'src' => $src, 'dest' => $dest]);
     * at the end, and do not return item.
     *
     * @return Parsed item or NULL if no item parsed.
     */
    public function parseItem($item = null)
    {
        if (!isset($item)) {
            $item = parent::parseItem();
            if (!isset($item)) {
                return;
            }
        }

        $dir = dirname($item['dest']);
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }

        $f = fopen($item['src'], 'r');
        file_put_contents($item['dest'], $f);
        fclose($f);
    }

}
