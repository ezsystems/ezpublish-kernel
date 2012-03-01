<?php
/**
 * File contains: Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Configuration\Parser\Ini;
use eZ\Publish\Core\Base\Configuration,
    PHPUnit_Framework_TestCase;

/**
 * Abstract test case for Parser\Ini class
 */
abstract class Base extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Base\Configuration\Parser\Ini $parser
     */
    protected $parser;

    /**
     * Setup parser test
     */
    public function setUp()
    {
        parent::setUp();
        $this->parser = $this->getParser();
    }

    /**
     * Setup parser with settings
     *
     * @abstract
     * @return \eZ\Publish\Core\Base\Configuration\Parser\Ini
     */
    abstract protected function getParser();

    /**
     * Test that ending hash boom is stripped out
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parseFilePhp
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parseFileEzc
     */
    public function testHashBoom()
    {
        $iniString = '
[test]
HashBoomer=enabled##!';
        $expects = array( 'test' => array( 'HashBoomer' => 'enabled' ) );
        $result = $this->parser->parse( 'DoesNotExist.ini', $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'ini parser did not strip hash boom'
        );
    }

    /**
     * Test that types in ini is properly parsed to native php types
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parsePhpPostFilter
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parseFilePhp
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parseFileEzc
     */
    public function testTypes()
    {
        $iniString = '
[test]
Int=1
Float=5.4
Decimal=5,4
BoolTrue=true
BoolFalse=false
BoolEnabled=enabled
BoolDisabled=disabled
String=Test';
        $expects = array(
            'test' => array(
                'Int' => 1,
                'Float' => 5.4,
                'Decimal' => '5,4',
                'BoolTrue' => true,
                'BoolFalse' => false,
                'BoolEnabled' => 'enabled',
                'BoolDisabled' => 'disabled',
                'String' => 'Test',
            )
        );
        $result = $this->parser->parse( 'DoesNotExist.ini', $iniString );
        $this->assertSame(
            $expects,
            $result,
            'ini parser did not cast type properly'
        );
    }

    /**
     * Test that types in ini is properly parsed to native php types in arrays
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parseFilePhp
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parseFileEzc
     */
    public function testArrayTypes()
    {
        $iniString = '
[test]
Mixed[]=true
Mixed[]=false
Mixed[]=string
Mixed[]=44
Mixed[]=4.4
Mixed[]=4,4';
        $expects = array( 'test' => array( 'Mixed' => array( true, false, 'string', 44, 4.4, '4,4' ) ) );
        $result = $this->parser->parse( 'DoesNotExist.ini', $iniString );
        $this->assertSame(
            $expects,
            $result,
            'ini parser did not cast type properly'
        );
    }

    /**
     * Test that empty arrays are returned
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parseFilePhp
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parseFileEzc
     */
    public function testEmptyArray()
    {
        $iniString = '
[test]
empty-array[]';
        $expects = array( 'test' => array( 'empty-array' => array( Configuration::TEMP_INI_UNSET_VAR ) ) );
        $result = $this->parser->parse( 'DoesNotExist.ini', $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'ini parser did not return empty array'
        );
    }

    /**
     * Test that complex hash structures with symbol use in key and value are parsed
     *
     * Also tests two dimensional arrays
     *
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parseFilePhp
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parseFileEzc
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parserPhpDimensionArraySupport
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parsePhpPostFilter
     */
    public function testComplexHash()
    {
        $iniString = '
[test]
conditions[ezp\\system\\Filter_Get::dev]=uri\\0:content\\uri\\1:^v\\auth:?php\\params:%php
conditions[$user_object->check]=ezp/system/router\\ezp\\system\\Filter_Get::dev
conditions[]=uri\\0:§£$content
conditions[][]=subOne
conditions[][]=subTwo
conditions[two][two]=subThree
conditions[two][two2]=subFour
conditions[two][two]=subFive
conditions[][][]=subSix
conditions[][][]=subSeven
conditions[three][three][three]=subEight
conditions[three][three][three3]=subNine
conditions[three][three][three]=subTen
conditions[events][pre_request][]=outputFn
conditions[routes][__ROOT__][item][uri]=
conditions[routes][content][item][uri]=content/some/
conditions[routes][content][item][params][id]=\d+
conditions[routes][content][item][controller]=%contentItem-controller::doList

[contentItem-controller]
class=eZ\Publish\Core\ContentItemController

[-controller]
public=true
';
        $expects = array(
            'test' => array(
                'conditions' => array(
                    'ezp\\system\\Filter_Get::dev' => 'uri\\0:content\\uri\\1:^v\\auth:?php\\params:%php',
                    '$user_object->check' => 'ezp/system/router\\ezp\\system\\Filter_Get::dev',
                    'uri\\0:§£$content',
                    array( 'subOne' ),
                    array( 'subTwo' ),
                    'two' => array( 'two' => 'subFive', 'two2' => 'subFour' ),
                    array( array( 'subSix' ) ),
                    array( array( 'subSeven' ) ),
                    'three' => array( 'three' => array( 'three' => 'subTen', 'three3' => 'subNine' ) ),
                    'events' => array( 'pre_request' => array( 'outputFn' ) ),
                    'routes' => array( '__ROOT__' => array( 'item' => array( 'uri' => '' ) ),
                                       'content' => array( 'item' => array( 'uri' => 'content/some/',
                                                                             'params' => array( 'id' => '\d+' ),
                                                                             'controller' => '%contentItem-controller::doList',
                    ) ) ),
                )
            ),
            'contentItem-controller' => array( 'class' => 'eZ\Publish\Core\ContentItemController' ),
            '-controller' => array( 'public' => true ),
        );

        $result = $this->parser->parse( 'DoesNotExist.ini', $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'ini parser did not parse complex hash'
        );
    }

    /**
     * Test that arrays contain clearing hint to Configuration class
     * @covers \eZ\Publish\Core\Base\Configuration\Parser\Ini::parserClearArraySupport
     */
    public function testArrayClearing()
    {
        $iniString = '
[test]
sub[]=hi
sub[]
two[one][]=hi
two[one][]
';
        $expects = array(
            'test' => array(
                'sub' => array( 'hi', Configuration::TEMP_INI_UNSET_VAR ),
                'two' => array( 'one' => array( 'hi', Configuration::TEMP_INI_UNSET_VAR ) ),
             )
        );

        $result = $this->parser->parse( 'DoesNotExist.ini', $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'ini parser did not properly clear array'
        );
    }
}
