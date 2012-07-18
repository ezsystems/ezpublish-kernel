<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\ObjectState\MapperTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\ObjectState;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper,
    eZ\Publish\SPI\Persistence\Content\ObjectState,
    eZ\Publish\SPI\Persistence\Content\ObjectState\Group,
    eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct,
    eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Test case for Mapper
 */
class MapperTest extends TestCase
{
    /**
     * Language handler mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected $languageHandler;

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper::createObjectStateFromData
     */
    public function testCreateObjectStateFromData()
    {
        $mapper = $this->getMapper();

        $rows = $this->getObjectStateRowsFixture();

        $result = $mapper->createObjectStateFromData( $rows );

        $this->assertStructsEqual(
            $this->getObjectStateFixture(),
            $result,
            array( 'identifier', 'defaultLanguage', 'languageCodes', 'name', 'description' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper::createObjectStateListFromData
     */
    public function testCreateObjectStateListFromData()
    {
        $mapper = $this->getMapper();

        $rows = array( $this->getObjectStateRowsFixture() );

        $result = $mapper->createObjectStateListFromData( $rows );

        $this->assertStructsEqual(
            $this->getObjectStateFixture(),
            $result[0],
            array( 'identifier', 'defaultLanguage', 'languageCodes', 'name', 'description' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper::createObjectStateGroupFromData
     */
    public function testCreateObjectStateGroupFromData()
    {
        $mapper = $this->getMapper();

        $rows = $this->getObjectStateGroupRowsFixture();

        $result = $mapper->createObjectStateGroupFromData( $rows );

        $this->assertStructsEqual(
            $this->getObjectStateGroupFixture(),
            $result,
            array( 'identifier', 'defaultLanguage', 'languageCodes', 'name', 'description' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper::createObjectStateGroupListFromData
     */
    public function testCreateObjectStateGroupListFromData()
    {
        $mapper = $this->getMapper();

        $rows = array( $this->getObjectStateGroupRowsFixture() );

        $result = $mapper->createObjectStateGroupListFromData( $rows );

        $this->assertStructsEqual(
            $this->getObjectStateGroupFixture(),
            $result[0],
            array( 'identifier', 'defaultLanguage', 'languageCodes', 'name', 'description' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper::createObjectStateFromInputStruct
     */
    public function testCreateObjectStateFromInputStruct()
    {
        $mapper = $this->getMapper();

        $inputStruct = $this->getObjectStateInputStructFixture();

        $result = $mapper->createObjectStateFromInputStruct( $inputStruct );

        $this->assertStructsEqual(
            $this->getObjectStateFixture(),
            $result,
            array( 'identifier', 'defaultLanguage', 'languageCodes', 'name', 'description' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper::createObjectStateGroupFromInputStruct
     */
    public function testCreateObjectStateGroupFromInputStruct()
    {
        $mapper = $this->getMapper();

        $inputStruct = $this->getObjectStateGroupInputStructFixture();

        $result = $mapper->createObjectStateGroupFromInputStruct( $inputStruct );

        $this->assertStructsEqual(
            $this->getObjectStateGroupFixture(),
            $result,
            array( 'identifier', 'defaultLanguage', 'languageCodes', 'name', 'description' )
        );
    }

    /**
     * Returns a Mapper
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper
     */
    protected function getMapper()
    {
        return new Mapper(
            $this->getLanguageHandlerMock()
        );
    }

    /**
     * Returns a language handler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected function getLanguageHandlerMock()
    {
        if ( !isset( $this->languageHandler ) )
        {
            $innerLanguageHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );

            $this->languageHandler = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\CachingHandler',
                array( 'getById' ),
                array(
                    $innerLanguageHandler,
                    $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\Cache' )
                )
            );

            $this->languageHandler
                ->expects( $this->any() )
                ->method( 'getById' )
                ->with( '2' )
                ->will(
                    $this->returnValue(
                        new Language(
                            array(
                                'id' => 2,
                                'languageCode' => 'eng-GB',
                            )
                        )
                    )
                );
        }

        return $this->languageHandler;
    }

    /**
     * Returns an object state result rows fixture
     *
     * @return array[][]
     */
    protected function getObjectStateRowsFixture()
    {
        return array(
            array(
                'ezcobj_state_default_language_id' => 2,
                'ezcobj_state_group_id' => 2,
                'ezcobj_state_id' => 1,
                'ezcobj_state_identifier' => 'not_locked',
                'ezcobj_state_language_mask' => 3,
                'ezcobj_state_priority' => 0,
                'ezcobj_state_language_description' => '',
                'ezcobj_state_language_language_id' => 3,
                'ezcobj_state_language_name' => 'Not locked'
            )
        );
    }

    /**
     * Returns an object state group result rows fixture
     *
     * @return array[][]
     */
    protected function getObjectStateGroupRowsFixture()
    {
        return array(
            array(
                'ezcobj_state_group_default_language_id' => 2,
                'ezcobj_state_group_id' => 1,
                'ezcobj_state_group_identifier' => 'ez_lock',
                'ezcobj_state_group_language_mask' => 3,
                'ezcobj_state_group_language_description' => '',
                'ezcobj_state_group_language_language_id' => 3,
                'ezcobj_state_group_language_real_language_id' => 2,
                'ezcobj_state_group_language_name' => 'Lock'
            )
        );
    }

    /**
     * Returns an object state fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    protected function getObjectStateFixture()
    {
        $objectState = new ObjectState();
        $objectState->identifier = 'not_locked';
        $objectState->defaultLanguage = 'eng-GB';
        $objectState->languageCodes = array( 'eng-GB' );
        $objectState->name = array( 'eng-GB' => 'Not locked' );
        $objectState->description = array( 'eng-GB' => '' );

        return $objectState;
    }

    /**
     * Returns an object state group fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    protected function getObjectStateGroupFixture()
    {
        $group = new Group();
        $group->identifier = 'ez_lock';
        $group->defaultLanguage = 'eng-GB';
        $group->languageCodes = array( 'eng-GB' );
        $group->name = array( 'eng-GB' => 'Lock' );
        $group->description = array( 'eng-GB' => '' );

        return $group;
    }

    /**
     * Returns the InputStruct fixture for creating object states
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct
     */
    protected function getObjectStateInputStructFixture()
    {
        $inputStruct = new InputStruct();

        $inputStruct->defaultLanguage = 'eng-GB';
        $inputStruct->identifier = 'not_locked';
        $inputStruct->name = array( 'eng-GB' => 'Not locked' );
        $inputStruct->description = array( 'eng-GB' => '' );

        return $inputStruct;
    }

    /**
     * Returns the InputStruct fixture for creating object state groups
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct
     */
    protected function getObjectStateGroupInputStructFixture()
    {
        $inputStruct = new InputStruct();

        $inputStruct->defaultLanguage = 'eng-GB';
        $inputStruct->identifier = 'ez_lock';
        $inputStruct->name = array( 'eng-GB' => 'Lock' );
        $inputStruct->description = array( 'eng-GB' => '' );

        return $inputStruct;
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
