<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Language\MapperTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Language;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\Language\Mapper,
    ezp\Persistence\Content\Language,
    ezp\Persistence\Content\Language\CreateStruct;

/**
 * Test case for Mapper
 */
class MapperTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Language\Mapper::createLanguageFromCreateStruct
     */
    public function testCreateLanguageFromCreateStruct()
    {
        $mapper = new Mapper();

        $createStruct = $this->getCreateStructFixture();

        $result = $mapper->createLanguageFromCreateStruct( $createStruct );

        $this->assertStructsEqual(
            $this->getLanguageFixture(),
            $result,
            array( 'locale', 'name', 'isEnabled' )
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Language\Mapper::extractLanguagesFromRows
     */
    public function testExtractLanguagesFromRows()
    {
        $mapper = new Mapper();

        $rows = $this->getRowsFixture();

        $result = $mapper->extractLanguagesFromRows( $rows );

        $this->assertEquals(
            $this->getExtractReference(),
            $result
        );
    }

    /**
     * Returns a result rows fixture
     *
     * @return string[][]
     */
    protected function getRowsFixture()
    {
        return array(
            array( 'disabled'=>'0', 'id'=>'2', 'locale'=>'eng-US', 'name'=>'English (American)' ),
            array( 'disabled'=>'0', 'id'=>'4', 'locale'=>'eng-GB', 'name'=>'English (United Kingdom)' )
        );
    }

    /**
     * Returns reference for the extraction from rows
     *
     * @return \ezp\Persistence\Content\Language[]
     */
    protected function getExtractReference()
    {
        $langUs = new Language();
        $langUs->id = 2;
        $langUs->locale = 'eng-US';
        $langUs->name = 'English (American)';
        $langUs->isEnabled = true;

        $langGb = new Language();
        $langGb->id = 4;
        $langGb->locale = 'eng-GB';
        $langGb->name = 'English (United Kingdom)';
        $langGb->isEnabled = true;

        return array( $langUs, $langGb );
    }

    /**
     * Returns a Language CreateStruct fixture
     *
     * @return ezp\Persistence\Content\Language\CreateStruct
     */
    protected function getCreateStructFixture()
    {
        $struct = new CreateStruct();

        $struct->locale = 'de-DE';
        $struct->name = 'Deutsch (Deutschland)';
        $struct->isEnabled = true;

        return $struct;
    }

    /**
     * Returns a Language fixture
     *
     * @return ezp\Persistence\Content\Language
     */
    protected function getLanguageFixture()
    {
        $struct = new Language();

        $struct->locale = 'de-DE';
        $struct->name = 'Deutsch (Deutschland)';
        $struct->isEnabled = true;

        return $struct;
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
