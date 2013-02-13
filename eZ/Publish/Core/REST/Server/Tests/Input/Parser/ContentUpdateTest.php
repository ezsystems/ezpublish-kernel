<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\ContentUpdate as ContentUpdateParser;
use eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct;
use DateTime;

class ContentUpdateTest extends BaseTest
{
    /**
     * Tests the ContentUpdate parser
     *
     * @return \eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct
     */
    public function testParseValid()
    {
        $inputArray = $this->getValidInputData();

        $contentUpdateParser = $this->getContentUpdate();
        $result = $contentUpdateParser->parse(
            $inputArray,
            $this->getParsingDispatcherMock()
        );

        self::assertEquals(
            $this->getContentUpdateStruct(),
            $result
        );

        return $result;
    }

    /**
     * Test for valid owner ID value in result
     *
     * @param \eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct $result
     *
     * @depends testParseValid
     */
    public function testParserResultOwner( RestContentMetadataUpdateStruct $result )
    {
        $this->assertEquals(
            '42',
            $result->ownerId
        );
    }

    /**
     * Tests that invalid _href attribute throw the appropriate exception
     *
     * @dataProvider providerForTestParseFailureInvalidHref
     */
    public function testParseFailureInvalidHref( $element, $exceptionMessage )
    {
        $inputArray = $this->getValidInputData();
        $inputArray[$element]['_href'] = '/invalid/section/uri';

        $contentUpdateParser = $this->getContentUpdate();

        try
        {
            $contentUpdateParser->parse(
                $inputArray,
                $this->getParsingDispatcherMock()
            );
        }
        catch ( \eZ\Publish\Core\REST\Common\Exceptions\Parser $e )
        {
            if ( $e->getMessage() != $exceptionMessage )
            {
                self::fail( "Failed asserting that exception message '" . $e->getMessage() . "' contains '$exceptionMessage'." );
            }
            $exceptionThrown = true;
        }

        if ( !isset( $exceptionThrown ) )
        {
            self::fail( "Failed asserting that exception of type \"\\eZ\\Publish\\Core\\REST\\Common\\Exceptions\\Parser\" is thrown." );
        }
    }

    public function providerForTestParseFailureInvalidHref()
    {
        return array(
            array( 'Section', 'Invalid format for <Section> reference in <ContentUpdate>.' ),
            array( 'MainLocation', "Invalid format for <MainLocation> reference in <ContentUpdate>." ),
            array( 'Owner', "Invalid format for <Owner> reference in <ContentUpdate>." )
        );
    }

    /**
     * Tests that invalid dates will fail at parsing
     * @dataProvider providerForTestParseFailureInvalidDate
     */
    public function testParseFailureInvalidDate( $element, $exceptionMessage )
    {
        $inputArray = $this->getValidInputData();
        $inputArray[$element] = 42;

        $contentUpdateParser = $this->getContentUpdate();

        try
        {
            $contentUpdateParser->parse(
                $inputArray,
                $this->getParsingDispatcherMock()
            );
        }
        catch ( \eZ\Publish\Core\REST\Common\Exceptions\Parser $e )
        {
            if ( $e->getMessage() != $exceptionMessage )
            {
                self::fail( "Failed asserting that exception message '" . $e->getMessage() . "' contains '$exceptionMessage'." );
            }
            $exceptionThrown = true;
        }

        if ( !isset( $exceptionThrown ) )
        {
            self::fail( "Failed asserting that exception of type \"\\eZ\\Publish\\Core\\REST\\Common\\Exceptions\\Parser\" is thrown." );
        }
    }

    public function providerForTestParseFailureInvalidDate()
    {
        return array(
            array( 'publishDate', "Invalid format for <publishDate> in <ContentUpdate>" ),
            array( 'modificationDate', "Invalid format for <modificationDate> in <ContentUpdate>" )
        );
    }

    /**
     * Returns the ContentUpdate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ContentUpdate
     */
    protected function getContentUpdate()
    {
        return new ContentUpdateParser( $this->getUrlHandler() );
    }

    /**
     * Returns a valid RestContentMetadataUpdateStruct that matches the structure from getValidInputData()
     *
     * @return \eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct
     */
    protected function getContentUpdateStruct()
    {
        return new RestContentMetadataUpdateStruct(
            array(
                'mainLanguageCode' => 'eng-GB',
                'sectionId'        => '23',
                'mainLocationId'   => '/1/2/55',
                'ownerId'          => '42',
                'alwaysAvailable'  => false,
                'remoteId'         => '7e7afb135e50490a281dafc0aafb6dac',
                'modificationDate' => new DateTime( '19/Sept/2012:14:05:00 +0200' ),
                'publishedDate'    => new DateTime( '19/Sept/2012:14:05:00 +0200' )
            )
        );
    }

    /**
     * Returns an array of valid input data for the parser.
     *
     * @return array
     */
    protected function getValidInputData()
    {
        return array(
            'mainLanguageCode' => 'eng-GB',
            'Section'          => array( '_href' => '/content/sections/23' ),
            'MainLocation'     => array( '_href' => '/content/locations/1/2/55' ),
            'Owner'            => array( '_href' => '/user/users/42' ),
            'alwaysAvailable'  => 'false',
            'remoteId'         => '7e7afb135e50490a281dafc0aafb6dac',
            'modificationDate' => '19/Sept/2012:14:05:00 +0200',
            'publishDate'    => '19/Sept/2012:14:05:00 +0200'
        );
    }
}
