<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\Type\ContentTypeHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegcyStorage\Content\Type\ContentTypeGateway;
use ezp\Persistence\Tests\LegcyStorage\Content\Type\ContentTypeGateway,
    ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase;
use ezp\Persistence\Content\Type\ContentTypeCreateStruct,
    ezp\Persistence\Content\Type\FieldDefinition;

/**
 * Test case for ContentTypeHandler.
 */
class EzcDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup
     *
     * Checks pre-conditions (Apache Zeta Components and ext/pdo_sqlite).
     *
     * @return void
     */
    public function setUp()
    {
        if ( !class_exists( 'ezcBase' ) )
        {
            $this->markTestSkipped( 'Missing Apache Zeta Components.' );
        }
        if ( !\ezcBaseFeatures::hasExtensionSupport( 'pdo_sqlite' ) )
        {
            $this->markTestSkipped( 'Missing ext/pdo_sqlite support.' );
        }
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::__construct
     */
    public function testCtor()
    {
        $handlerMock = $this->getDbHandlerMock();
        $gateway = new EzcDatabase( $handlerMock );

        $this->assertAttributeSame(
            $handlerMock,
            'db',
            $gateway
        );
    }

    /**
     * Returns an ezcDbHandler mock.
     *
     * {@link ezcDbHandler} cannot be mocked, since it extends PDO. Therefore,
     * we return a real instance on basis of an SQLite memory DB here.
     *
     * @return \ezcDbHandler
     */
    protected function getDbHandlerMock()
    {
        return \ezcDbFactory::create( 'sqlite://:memory:' );
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
