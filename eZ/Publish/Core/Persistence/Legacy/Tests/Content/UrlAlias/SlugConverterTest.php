<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAlias\SlugConverterTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAlias;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;

/**
 * Test case for URL slug converter.
 */
class SlugConverterTest extends TestCase
{
    /**
     * Test for the convert() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter::convert
     */
    public function testConvert()
    {
        self::markTestIncomplete( "Not implemented: " . __METHOD__ );
    }

    /**
     * Test for the getUniqueCounterValue() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter::getUniqueCounterValue
     */
    public function testGetUniqueCounterValue()
    {
        self::markTestIncomplete( "Not implemented: " . __METHOD__ );
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
