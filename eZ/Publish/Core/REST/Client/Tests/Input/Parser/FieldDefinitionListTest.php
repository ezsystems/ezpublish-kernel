<?php
/**
 * File containing a ContentTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\Values;

class FieldDefinitionListTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\REST\Client\ContentService
     */
    protected $contentTypeServiceMock;

    /**
     * Tests the section parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testParse()
    {
        $fieldDefinitionListParser = $this->getParser();

        $inputArray = array(
            '_media-type' => 'application/vnd.ez.api.FieldDefinitionList+json',
            '_href' => '/content/types/1/fieldDefinitions',
            // Only "mock"
            'FieldDefinition' => array(
                0 => array(
                    '_media-type' => 'application/vnd.ez.api.FieldDefinition+json',
                    '_href' => '/content/types/1/fieldDefinitions/23',
                ),
                1 => array(
                    '_media-type' => 'application/vnd.ez.api.FieldDefinition+json',
                    '_href' => '/content/types/1/fieldDefinitions/42',
                ),
            ),
        );

        $this->contentTypeServiceMock->expects( $this->exactly( 2 ) )
            ->method( 'loadFieldDefinition' )
            ->with( $this->isType( 'string' ) );

        $result = $fieldDefinitionListParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $fieldDefinitionArray = $result->getFieldDefinitions();

        $this->assertInternalType( 'array', $fieldDefinitionArray );
    }

    /**
     * Gets the section parser
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\FieldDefinitionList
     */
    protected function getParser()
    {
        return new Input\Parser\FieldDefinitionList(
            new ParserTools(),
            $this->getContentTypeServiceMock()
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Client\ContentService
     */
    protected function getContentTypeServiceMock()
    {
        if ( !isset( $this->contentTypeServiceMock ) )
        {
            $this->contentTypeServiceMock = $this->getMock(
                'eZ\\Publish\\Core\\REST\\Client\\ContentTypeService',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->contentTypeServiceMock;
    }
}
