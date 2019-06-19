<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Server\Input\Parser\ContentUpdate as ContentUpdateParser;
use eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct;
use eZ\Publish\Core\REST\Common\Exceptions\Parser;
use DateTime;

class ContentUpdateTest extends BaseTest
{
    /**
     * Tests the ContentUpdate parser.
     *
     * @return \eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct
     */
    public function testParseValid()
    {
        $inputArray = $this->getValidInputData();

        $contentUpdateParser = $this->getParser();
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
     * Test for valid owner ID value in result.
     *
     * @param \eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct $result
     *
     * @depends testParseValid
     */
    public function testParserResultOwner(RestContentMetadataUpdateStruct $result)
    {
        $this->assertEquals(
            '42',
            $result->ownerId
        );
    }

    /**
     * Tests that invalid _href attribute throw the appropriate exception.
     *
     * @dataProvider providerForTestParseFailureInvalidHref
     */
    public function testParseFailureInvalidHref($element, $exceptionMessage)
    {
        $inputArray = $this->getValidInputData();
        $inputArray[$element]['_href'] = '/invalid/section/uri';

        $contentUpdateParser = $this->getParser();

        try {
            $contentUpdateParser->parse(
                $inputArray,
                $this->getParsingDispatcherMock()
            );
        } catch (Parser $e) {
            if ($e->getMessage() != $exceptionMessage) {
                self::fail("Failed asserting that exception message '" . $e->getMessage() . "' contains '$exceptionMessage'.");
            }
            $exceptionThrown = true;
        }

        if (!isset($exceptionThrown)) {
            self::fail('Failed asserting that exception of type "\\eZ\\Publish\\Core\\REST\\Common\\Exceptions\\Parser" is thrown.');
        }
    }

    public function providerForTestParseFailureInvalidHref()
    {
        return [
            ['Section', 'Invalid format for <Section> reference in <ContentUpdate>.'],
            ['MainLocation', 'Invalid format for <MainLocation> reference in <ContentUpdate>.'],
            ['Owner', 'Invalid format for <Owner> reference in <ContentUpdate>.'],
        ];
    }

    /**
     * Tests that invalid dates will fail at parsing.
     *
     * @dataProvider providerForTestParseFailureInvalidDate
     */
    public function testParseFailureInvalidDate($element, $exceptionMessage)
    {
        $inputArray = $this->getValidInputData();
        $inputArray[$element] = 42;

        $contentUpdateParser = $this->getParser();

        try {
            $contentUpdateParser->parse(
                $inputArray,
                $this->getParsingDispatcherMock()
            );
        } catch (Parser $e) {
            if ($e->getMessage() != $exceptionMessage) {
                self::fail("Failed asserting that exception message '" . $e->getMessage() . "' contains '$exceptionMessage'.");
            }
            $exceptionThrown = true;
        }

        if (!isset($exceptionThrown)) {
            self::fail('Failed asserting that exception of type "\\eZ\\Publish\\Core\\REST\\Common\\Exceptions\\Parser" is thrown.');
        }
    }

    public function providerForTestParseFailureInvalidDate()
    {
        return [
            ['publishDate', 'Invalid format for <publishDate> in <ContentUpdate>'],
            ['modificationDate', 'Invalid format for <modificationDate> in <ContentUpdate>'],
        ];
    }

    /**
     * Returns the ContentUpdate parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ContentUpdate
     */
    protected function internalGetParser()
    {
        return new ContentUpdateParser();
    }

    /**
     * Returns a valid RestContentMetadataUpdateStruct that matches the structure from getValidInputData().
     *
     * @return \eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct
     */
    protected function getContentUpdateStruct()
    {
        return new RestContentMetadataUpdateStruct(
            [
                'mainLanguageCode' => 'eng-GB',
                'sectionId' => 23,
                'mainLocationId' => 55,
                'ownerId' => 42,
                'alwaysAvailable' => false,
                'remoteId' => '7e7afb135e50490a281dafc0aafb6dac',
                'modificationDate' => new DateTime('19/Sept/2012:14:05:00 +0200'),
                'publishedDate' => new DateTime('19/Sept/2012:14:05:00 +0200'),
            ]
        );
    }

    /**
     * Returns an array of valid input data for the parser.
     *
     * @return array
     */
    protected function getValidInputData()
    {
        return [
            'mainLanguageCode' => 'eng-GB',
            'Section' => ['_href' => '/content/sections/23'],
            'MainLocation' => ['_href' => '/content/locations/1/2/55'],
            'Owner' => ['_href' => '/user/users/42'],
            'alwaysAvailable' => 'false',
            'remoteId' => '7e7afb135e50490a281dafc0aafb6dac',
            'modificationDate' => '19/Sept/2012:14:05:00 +0200',
            'publishDate' => '19/Sept/2012:14:05:00 +0200',
        ];
    }

    public function getParseHrefExpectationsMap()
    {
        return [
            ['/content/sections/23', 'sectionId', 23],
            ['/user/users/42', 'userId', 42],
            ['/content/locations/1/2/55', 'locationPath', '1/2/55'],

            ['/invalid/section/uri', 'sectionId', new InvalidArgumentException('Invalid format for <Section> reference in <ContentUpdate>.')],
            ['/invalid/section/uri', 'userId', new InvalidArgumentException('Invalid format for <Owner> reference in <ContentUpdate>.')],
            ['/invalid/section/uri', 'locationPath', new InvalidArgumentException('Invalid format for <MainLocation> reference in <ContentUpdate>.')],
        ];
    }
}
