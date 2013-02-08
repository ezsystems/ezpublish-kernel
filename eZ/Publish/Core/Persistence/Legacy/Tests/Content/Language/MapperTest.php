<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\MapperTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;

/**
 * Test case for Mapper
 */
class MapperTest extends TestCase
{
    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper::createLanguageFromCreateStruct
     *
     * @return void
     */
    public function testCreateLanguageFromCreateStruct()
    {
        $mapper = new Mapper();

        $createStruct = $this->getCreateStructFixture();

        $result = $mapper->createLanguageFromCreateStruct( $createStruct );

        $this->assertStructsEqual(
            $this->getLanguageFixture(),
            $result,
            array( 'languageCode', 'name', 'isEnabled' )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper::extractLanguagesFromRows
     *
     * @return void
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
            array( 'disabled' => '0', 'id' => '2', 'locale' => 'eng-US', 'name' => 'English (American)' ),
            array( 'disabled' => '0', 'id' => '4', 'locale' => 'eng-GB', 'name' => 'English (United Kingdom)' )
        );
    }

    /**
     * Returns reference for the extraction from rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    protected function getExtractReference()
    {
        $langUs = new Language();
        $langUs->id = 2;
        $langUs->languageCode = 'eng-US';
        $langUs->name = 'English (American)';
        $langUs->isEnabled = true;

        $langGb = new Language();
        $langGb->id = 4;
        $langGb->languageCode = 'eng-GB';
        $langGb->name = 'English (United Kingdom)';
        $langGb->isEnabled = true;

        return array( 'eng-US' => $langUs, 'eng-GB' => $langGb );
    }

    /**
     * Returns a Language CreateStruct fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language\CreateStruct
     */
    protected function getCreateStructFixture()
    {
        $struct = new CreateStruct();

        $struct->languageCode = 'de-DE';
        $struct->name = 'Deutsch (Deutschland)';
        $struct->isEnabled = true;

        return $struct;
    }

    /**
     * Returns a Language fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    protected function getLanguageFixture()
    {
        $struct = new Language();

        $struct->languageCode = 'de-DE';
        $struct->name = 'Deutsch (Deutschland)';
        $struct->isEnabled = true;

        return $struct;
    }
}
