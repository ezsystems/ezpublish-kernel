<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAliasMapperTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAlias;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase,
    eZ\Publish\SPI\Persistence\Content\UrlAlias,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;

/**
 * Test case for UrlAliasMapper
 */
class UrlAliasMapperTest extends LanguageAwareTestCase
{
    protected $fixture = array(
        0 => array(
            "action" => "eznode:314",
            "parent" => "1",
            "text_md5" => "f97c5d29941bfb1b2fdab0874906ab82",
            "raw_path_data" => array(
                0 => array(
                    array(
                        "lang_mask" => 2,
                        "text" => "root_us"
                    ),
                    array(
                        "lang_mask" => 4,
                        "text" => "root_gb"
                    )
                ),
                1 => array(
                    array(
                        "lang_mask" => 4,
                        "text" => "one"
                    )
                )
            ),
            "lang_mask" => 5,
            "is_original" => "1",
            "is_alias" => "1",
            "alias_redirects" => "0",
        ),
        1 => array(
            "action" => "eznode:314",
            "parent" => "2",
            "text_md5" => "b8a9f715dbb64fd5c56e7783c6820a61",
            "raw_path_data" => array(
                0 => array(
                    array(
                        "lang_mask" => 3,
                        "text" => "two"
                    ),
                ),
            ),
            "lang_mask" => 3,
            "is_original" => "0",
            "is_alias" => "0",
            "alias_redirects" => "1",
        ),
        2 => array(
            "action" => "module:content/search",
            "parent" => "0",
            "text_md5" => "35d6d33467aae9a2e3dccb4b6b027878",
            "raw_path_data" => array(
                0 => array(
                    array(
                        "lang_mask" => 6,
                        "text" => "three"
                    ),
                ),
            ),
            "lang_mask" => 6,
            "is_original" => "1",
            "is_alias" => "1",
            "alias_redirects" => "1",
        ),
        3 => array(
            "action" => "nop:",
            "parent" => "3",
            "text_md5" => "8cbad96aced40b3838dd9f07f6ef5772",
            "raw_path_data" => array(
                0 => array(
                    array(
                        "lang_mask" => 1,
                        "text" => "four"
                    ),
                ),
            ),
            "lang_mask" => 1,
            "is_original" => "0",
            "is_alias" => "0",
            "alias_redirects" => "1",
        ),
    );

    protected function getExpectation()
    {
        return array(
            0 => new UrlAlias(
                array(
                    "id" => "1-f97c5d29941bfb1b2fdab0874906ab82",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 314,
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "eng-US" => "root_us",
                                "eng-GB" => "root_gb",
                            ),
                        ),
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "eng-GB" => "one",
                            ),
                        ),
                    ),
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => true,
                    "forward" => false
                )
            ),
            1 => new UrlAlias(
                array(
                    "id" => "2-b8a9f715dbb64fd5c56e7783c6820a61",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 314,
                    "pathData" => array(
                        array(
                            "always-available" => true,
                            "translations" => array(
                                "eng-US" => "two",
                            ),
                        ),
                    ),
                    "languageCodes" => array( "eng-US" ),
                    "alwaysAvailable" => true,
                    "isHistory" => true,
                    "isCustom" => false,
                    "forward" => false
                )
            ),
            2 => new UrlAlias(
                array(
                    "id" => "0-35d6d33467aae9a2e3dccb4b6b027878",
                    "type" => UrlAlias::RESOURCE,
                    "destination" => "content/search",
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "eng-US" => "three",
                                "eng-GB" => "three",
                            ),
                        ),
                    ),
                    "languageCodes" => array( "eng-US", "eng-GB" ),
                    "alwaysAvailable" => false,
                    "isHistory" => false,
                    "isCustom" => true,
                    "forward" => true
                )
            ),
            3 => new UrlAlias(
                array(
                    "id" => "3-8cbad96aced40b3838dd9f07f6ef5772",
                    "type" => UrlAlias::VIRTUAL,
                    "destination" => null,
                    "pathData" => array(
                        array(
                            "always-available" => true,
                            "translations" => array(
                                "always-available" => "four",
                            ),
                        ),
                    ),
                    "languageCodes" => array(),
                    "alwaysAvailable" => true,
                    "isHistory" => true,
                    "isCustom" => false,
                    "forward" => false
                )
            ),
        );
    }

    public function providerForTestExtractUrlAliasFromData()
    {
        return array( array( 0 ), array( 1 ), array( 2 ), array( 3 ) );
    }

    /**
     * Test for the extractUrlAliasFromData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::extractUrlAliasFromData
     * @dataProvider providerForTestExtractUrlAliasFromData
     */
    public function testExtractUrlAliasFromData( $index )
    {
        $mapper = $this->getMapper();

        $urlAlias = $mapper->extractUrlAliasFromData( $this->fixture[$index] );
        $expectation = $this->getExpectation();

        self::assertEquals(
            $expectation[$index],
            $urlAlias
        );
    }

    /**
     * Test for the extractUrlAliasListFromData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::extractUrlAliasListFromData
     * @depends testExtractUrlAliasFromData
     */
    public function testExtractUrlAliasListFromData()
    {
        $mapper = $this->getMapper();

        self::assertEquals(
            $this->getExpectation(),
            $mapper->extractUrlAliasListFromData( $this->fixture )
        );
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper
     */
    protected function getMapper()
    {
        $languageHandler = $this->getLanguageHandler();
        $languageMaskGenerator = new LanguageMaskGenerator( $languageHandler );
        return new Mapper( $languageMaskGenerator );
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
