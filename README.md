# AParser

Simple text parser. Any text format. Low memory usage (~ 2 buffers) for large files.

## Usage

### Basic

    $parser = new AListParser();
    $parser->open('http://google.com/sitemap.xml');
    $parser->parseList(
        '<urlset',
        '<url>',
        function() use ($parser) {
            printf(
                "loc: %s\npriority: %s\n\n",
                $parser->parseBetween('<loc>', '</'),
                $parser->parseBetween('<priority>', '</')
            );
        }
    );

To store results in array (large result array can cause high memory usage):

    $parser = new AListParser();
    $parser->open('http://google.com/sitemap.xml');
    $result = $parser->parseList(
        '<urlset',
        '<url>',
        function() use ($parser) {
            return [
                'loc' => $parser->parseBetween('<loc>', '</'),
                'priority' => $parser->parseBetween('<priority>', '</'),
            ];
        }
    );

Except method `parseBetween` there are two methods `seekTo` and `parseTo`.

Method `parseTo` returns string from current position to specified string, but can't parse string longer than 1 buffer length.

Method `seekTo` moves file pointer to specified string, and can seek over any amount of buffers, using less than 2 buffers memory.

Method `parseBetween` uses these methods: seeks to first argument and parses to second argument.

### Extending

It may be much flexible to extend class `AListParser` or `AParser` with you own:

    class MyParser extends AListParser
    {
        public $buffer = 4096;
        public $encoding = 'UTF-8';

        public $beginOfList = '<urlset';
        public $beginOfItem = '<url>';

        public function parseItem()
        {
            printf(
                "loc: %s\npriority: %s\n\n",
                $this->parseBetween('<loc>', '</'),
                $this->parseBetween('<priority>', '</')
            );
        }
    }

    $myParser = new MyParser();
    $myParser->open('http://google.com/sitemap.xml');
    $myParser->parseList();

## License

Copyright © 2014 Krylosov Maksim <Aequiternus@gmail.com>

This Source Code Form is subject to the terms of the Mozilla Public
License, v. 2.0. If a copy of the MPL was not distributed with this
file, You can obtain one at http://mozilla.org/MPL/2.0/.
