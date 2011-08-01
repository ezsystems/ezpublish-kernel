<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\ContentLocatorTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\Content;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\LegacyStorage\Content,
    ezp\Persistence\Content\Criterion,
    ezp\Persistence;

/**
 * Test case for ContentLocator
 */
class ContentLocatorTest extends TestCase
{
    protected static $setUp = false;

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    /**
     * Only set up once for these read only tests on a large fixture
     *
     * Skipping the reset-up, since setting up for these tests takes quite some
     * time, which is not required to spent, since we are only reading from the
     * database anyways.
     *
     * @return void
     */
    public function setUp()
    {
        if ( !self::$setUp )
        {
            parent::setUp();
            $this->insertDatabaseFixture( __DIR__ . '/ContentLocator/_fixtures/full_dump.php' );
            self::$setUp = true;
        }
    }

    protected function getContentLocator()
    {
        return new Content\ContentLocator(
            new Content\ContentLocatorGateway\EzcDatabase(
                $this->getDatabaseHandler()
            )
        );
    }

    public function testContentIdFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\ContentId(
                null,
                Criterion\Operator::IN,
                array( 1, 4, 10 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10 ),
            array_map(
                function ( $content )
                {
                    return $content->id;
                },
                $result
            )
        );
    }
}
