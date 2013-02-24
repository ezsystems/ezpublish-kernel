<?php
/**
 * File containing a ContentTypeGroupTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\Values;

class ContentTypeGroupTest extends BaseTest
{
    /**
     * Tests the ContentTypeGroup parser
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function testParse()
    {
        $contentTypeGroupParser = $this->getParser();

        $inputArray = array(
            '_media-type' => 'application/vnd.ez.api.ContentTypeGroup+json',
            '_href' => '/content/typegroups/1',
            'id' => 1,
            'identifier' => 'folder',
            'created' => '2002-06-18T11:21:38+02:00',
            'modified' => '2004-04-20T11:54:35+02:00',
            'Creator' => array(
                '_media-type' => 'application/vnd.ez.api.User+json',
                '_href' => '/user/users/10',
            ),
            'Modifier' => array(
                '_media-type' => 'application/vnd.ez.api.User+json',
                '_href' => '/user/users/14',
            ),
        );

        $result = $contentTypeGroupParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * @dataProvider provideExpectedContentTypeGroupProperties
     * @depends testParse
     */
    public function testParsedProperties( $propertyName, $expectedValue, $parsedContentTypeGroup )
    {
        $this->assertEquals(
            $expectedValue,
            $parsedContentTypeGroup->$propertyName,
            "Property \${$propertyName} parsed incorrectly."
        );
    }

    public function provideExpectedContentTypeGroupProperties()
    {
        return array(
            array(
                'id',
                '/content/typegroups/1',
            ),
            array(
                'identifier',
                'folder',
            ),
            array(
                'creationDate',
                new \DateTime( '2002-06-18T11:21:38+02:00' ),
            ),
            array(
                'modificationDate',
                new \DateTime( '2004-04-20T11:54:35+02:00' ),
            ),
            array(
                'creatorId',
                '/user/users/10',
            ),
            array(
                'modifierId',
                '/user/users/14',
            ),
        );
    }

    /**
     * Gets the ContentTypeGroup parser
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\ContentTypeGroup
     */
    protected function getParser()
    {
        return new Input\Parser\ContentTypeGroup(
            new ParserTools()
        );
    }
}
