<?php

namespace aeqdev\AParser;

require_once '../../../aeqdev/AParser.php';
require_once '../../../aeqdev/AParser/AListParser.php';

class AListParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AListParser
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new AListParser();
    }

    protected function tearDown()
    {
        $this->object->close();
    }

    public function testParseList()
    {
        $this->object->buffer = 100;
        $this->object->open('test-sitemap.xml');
//        $this->object->open('http://google.com/sitemap.xml');

        $result = $this->object->parseList(
            '<urlset',
            '<url>',
            function() {
                return [
                    'loc' => $this->object->parseBetween('<loc>', '</'),
                    'priority' => $this->object->parseBetween('<priority>', '</'),
                ];
            }
        );

        $this->assertEquals(566, count($result));
        $s = 'http://www.google.com/ads/industry/retail/tipsforsuccess/more_bang_for_your_buck.html';
        $this->assertEquals($s, $result[98]['loc']);
    }

}
