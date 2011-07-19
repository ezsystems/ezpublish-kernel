<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegcyStorage\Content\Type\ContentTypeGateway;
use ezp\Persistence\Tests\LegacyStorage\TestCase;

use ezp\Persistence\Tests\LegcyStorage\Content\Type\ContentTypeGateway,
    ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase;

use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition;

/**
 * Test case for ContentTypeGateway.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::__construct
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
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::loadTypeData
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::selectColumns
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::createTableColumnAlias
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::qualifiedIdentifier
     */
    public function testLoadTypeData()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/load_type.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $rows = $gateway->loadTypeData( 1, 0 );

        $this->assertEquals(
            5,
            count( $rows )
        );
        $this->assertEquals(
            45,
            count( $rows[0] )
        );

        /*
         * Store mapper fixture
         *
        file_put_contents(
            dirname( __DIR__ ) . '/_fixtures/map_load_type.php',
            "<?php\n\nreturn " . var_export( $rows, true ) . ";\n"
        );
         */
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::insertType
     */
    public function testInsertType()
    {
        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $type = $this->getTypeFixture();

        $gateway->insertType( $type );

        $this->assertQueryResult(
            array( array( 1 ) ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( 'COUNT(*)' )
                ->from( 'ezcontentclass' ),
            'Type not inserted'
        );
        $this->assertQueryResult(
            array(
                array(
                    // "random" sample
                    'serialized_name_list' => 'a:2:{s:16:"always-available";s:6:"eng-US";s:6:"eng-US";s:6:"Folder";}',
                    'created'              => '1024392098',
                    'modifier_id'          => '14',
                    'remote_id'            => 'a3d405b81be900468eb153d774f4f0d2',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'serialized_name_list',
                    'created',
                    'modifier_id',
                    'remote_id'
                )->from( 'ezcontentclass' ),
            'Inserted Type data incorrect'
        );
    }

    /**
     * Returns a Type fixture.
     *
     * @return Type
     */
    protected function getTypeFixture()
    {
        $type = new Type();

        $type->version = 0;
        $type->name    = array(
            'always-available' => 'eng-US',
            'eng-US'           => 'Folder',
        );
        $type->description = array(
            0                  => '',
            'always-available' => false,
        );
        $type->identifier        = 'folder';
        $type->created           = 1024392098;
        $type->modified          = 1082454875;
        $type->creatorId         = 14;
        $type->modifierId        = 14;
        $type->remoteId          = 'a3d405b81be900468eb153d774f4f0d2';
        $type->urlAliasSchema    = '';
        $type->nameSchema        = '<short_name|name>';
        $type->isContainer       = true;
        $type->initialLanguageId = 2;

        return $type;
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
