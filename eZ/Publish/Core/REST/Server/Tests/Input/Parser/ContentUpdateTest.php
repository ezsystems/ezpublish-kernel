<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\ContentUpdate;
use eZ\Publish\Core\REST\Common\Values\SectionIncludingContentMetadataUpdateStruct;

class ContentUpdateTest extends BaseTest
{
    /**
     * Test the parser for ContentUpdate
     *
     * @return \eZ\Publish\Core\REST\Common\Values\SectionIncludingContentMetadataUpdateStruct
     */
    public function testParseValid()
    {
        $inputArray = $this->getValidInputData();

        $contentUpdateParser = $this->getContentUpdate();
        $result = $contentUpdateParser->parse(
            $inputArray,
            $this->getParsingDispatcherMock()
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\REST\\Common\\Values\\SectionIncludingContentMetadataUpdateStruct',
            $result
        );

        return $result;
    }

    /**
     * Test for valid section ID value in result
     *
     * @param \eZ\Publish\Core\REST\Common\Values\SectionIncludingContentMetadataUpdateStruct $result
     * @depends testParseValid
     */
    public function testParserResultSection( SectionIncludingContentMetadataUpdateStruct $result )
    {
        $this->assertEquals(
            '23',
            $result->sectionId
        );
    }

    /**
     * Test for valid owner ID value in result
     *
     * @param \eZ\Publish\Core\REST\Common\Values\SectionIncludingContentMetadataUpdateStruct $result
     * @depends testParseValid
     */
    public function testParserResultOwner( SectionIncludingContentMetadataUpdateStruct $result )
    {
        $this->assertEquals(
            '42',
            $result->ownerId
        );
    }

    /**
     * Test for null section ID value in result
     * @depends testParseValid
     */
    public function testParseValidSectionNull()
    {
        $inputArray = $this->getValidInputData();
        $inputArray['Section'] = null;

        $contentUpdateParser = $this->getContentUpdate();
        $result = $contentUpdateParser->parse(
            $inputArray,
            $this->getParsingDispatcherMock()
        );

        $this->assertNull(
            $result->sectionId
        );
    }

    /**
     * Test for null owner ID value in result
     * @depends testParseValid
     */
    public function testParseValidOwnerNull()
    {
        $inputArray = $this->getValidInputData();
        $inputArray['Owner'] = null;

        $contentUpdateParser = $this->getContentUpdate();
        $result = $contentUpdateParser->parse(
            $inputArray,
            $this->getParsingDispatcherMock()
        );

        $this->assertNull(
            $result->ownerId
        );
    }

    /**
     * Test for missing Section element throws exception
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing <Section> element in <ContentUpdate>.
     */
    public function testParseFailureMissingSection()
    {
        $inputArray = $this->getValidInputData();
        unset( $inputArray['Section'] );

        $contentUpdateParser = $this->getContentUpdate();

        $contentUpdateParser->parse(
            $inputArray,
            $this->getParsingDispatcherMock()
        );
    }

    /**
     * Test for invalid Section _href attribute throws exception
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid format for <Section> reference in <ContentUpdate>.
     */
    public function testParseFailureInvalidSectionHref()
    {
        $inputArray = $this->getValidInputData();
        $inputArray['Section']['_href'] = '/invalid/section/uri';

        $contentUpdateParser = $this->getContentUpdate();

        $contentUpdateParser->parse(
            $inputArray,
            $this->getParsingDispatcherMock()
        );
    }

    /**
     * Test for missing Owner element throws exception
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing <Owner> element in <ContentUpdate>.
     */
    public function testParseFailureMissingOwner()
    {
        $inputArray = $this->getValidInputData();
        unset( $inputArray['Owner'] );

        $contentUpdateParser = $this->getContentUpdate();

        $contentUpdateParser->parse(
            $inputArray,
            $this->getParsingDispatcherMock()
        );
    }

    /**
     * Test for invalid Owner _href attribute throws exception
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid format for <Owner> reference in <ContentUpdate>.
     */
    public function testParseFailureInvalidOwnerHref()
    {
        $inputArray = $this->getValidInputData();
        $inputArray['Owner']['_href'] = '/invalid/owner/uri';

        $contentUpdateParser = $this->getContentUpdate();

        $contentUpdateParser->parse(
            $inputArray,
            $this->getParsingDispatcherMock()
        );
    }

    /**
     * Returns the ContentUpdate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ContentUpdate
     */
    protected function getContentUpdate()
    {
        return new ContentUpdate( $this->getUrlHandler() );
    }

    /**
     * Returns an array of valid input data for the parser.
     *
     * @return array
     */
    protected function getValidInputData()
    {
        return array(
            'Section' => array(
                '_href' => '/content/sections/23',
            ),
            'Owner' => array(
                '_href' => '/user/users/42',
            ),
            // TODO: Missing properties + tests according to examples
        );
    }
}
