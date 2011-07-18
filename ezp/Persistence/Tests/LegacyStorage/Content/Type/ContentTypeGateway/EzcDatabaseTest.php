<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\Type\ContentTypeHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegcyStorage\Content\Type\ContentTypeGateway;
use ezp\Persistence\Tests\LegacyStorage\TestCase;

use ezp\Persistence\Tests\LegcyStorage\Content\Type\ContentTypeGateway,
    ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase;

use ezp\Persistence\Content\Type\ContentTypeCreateStruct,
    ezp\Persistence\Content\Type\FieldDefinition;

/**
 * Test case for ContentTypeHandler.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::__construct
     */
    public function testCtor()
    {
        $handlerMock = $this->getDatabaseHandler();
        $gateway = new EzcDatabase( $handlerMock );

        $this->assertAttributeSame(
            $handlerMock,
            'dbHandler',
            $gateway
        );
    }

    /**
     * Inserts database fixture from $fileName.
     *
     * @param string $fileName 
     * @return void
     */
    protected function insertFixture( $fileName )
    {
        $data = require( __DIR__ . '/_fixtures/' . $fileName );
        $db   = $this->getDatabaseHandler();

        foreach ( $data as $table => $rows )
        {
            foreach ( $rows as $row )
            {
                $q = $db->createInsertQuery();
                $q->insertInto( $db->quoteIdentifier( $table ) );
                foreach ( $row as $col => $val )
                {
                    $q->set(
                        $db->quoteIdentifier( $col ),
                        $q->bindValue( $val )
                    );
                }
                $stmt = $q->prepare();
                $stmt->execute();
            }
        }
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
