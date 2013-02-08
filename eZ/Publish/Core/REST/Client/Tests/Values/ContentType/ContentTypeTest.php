<?php
/**
 * File containing the ContentTypeTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Values\ContentType;

use eZ\Publish\Core\REST\Client\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Client\Values\ContentType\FieldDefinition;

class ContentTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $contentTypeServiceMock;

    public function setUp()
    {
        $this->contentTypeServiceMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Client\\ContentTypeService',
            array(),
            array(),
            '',
            false
        );
    }

    public function testGetName()
    {
        $contentType = new ContentType(
            $this->contentTypeServiceMock,
            array(
                'names' => array( 'eng-US' => 'Sindelfingen', 'eng-GB' => 'Bielefeld' )
            )
        );

        $this->assertEquals(
            'Sindelfingen',
            $contentType->getName( 'eng-US' )
        );
        $this->assertEquals(
            'Bielefeld',
            $contentType->getName( 'eng-GB' )
        );
    }

    public function testGetDescription()
    {
        $contentType = new ContentType(
            $this->contentTypeServiceMock,
            array(
                'descriptions' => array( 'eng-US' => 'Sindelfingen', 'eng-GB' => 'Bielefeld' )
            )
        );

        $this->assertEquals(
            'Sindelfingen',
            $contentType->getDescription( 'eng-US' )
        );
        $this->assertEquals(
            'Bielefeld',
            $contentType->getDescription( 'eng-GB' )
        );
    }

    public function testGetFieldDefinitions()
    {
        $contentType = new ContentType(
            $this->contentTypeServiceMock,
            array(
                'fieldDefinitionListReference' => '/content/types/23/fieldDefinitions',
            )
        );

        $contentTypeServiceMock = $this->contentTypeServiceMock;

        $contentTypeServiceMock->expects( $this->once() )
            ->method( 'loadFieldDefinitionList' )
            ->with( $this->equalTo( '/content/types/23/fieldDefinitions' ) )
            ->will( $this->returnValue( $this->getFieldDefinitionListMock() ) );

        $this->assertEquals(
            $this->getFieldDefinitions(),
            $contentType->getFieldDefinitions()
        );
    }

    public function testGetFieldDefinition()
    {
        $contentType = new ContentType(
            $this->contentTypeServiceMock,
            array(
                'fieldDefinitionListReference' => '/content/types/23/fieldDefinitions',
            )
        );

        $contentTypeServiceMock = $this->contentTypeServiceMock;

        $contentTypeServiceMock->expects( $this->once() )
            ->method( 'loadFieldDefinitionList' )
            ->with( $this->equalTo( '/content/types/23/fieldDefinitions' ) )
            ->will( $this->returnValue( $this->getFieldDefinitionListMock() ) );

        $fieldDefinitions = $this->getFieldDefinitions();

        $this->assertEquals(
            $fieldDefinitions[1],
            $contentType->getFieldDefinition( 'second-field' )
        );
    }

    public function testGetFieldDefinitionFailure()
    {
        $contentType = new ContentType(
            $this->contentTypeServiceMock,
            array(
                'fieldDefinitionListReference' => '/content/types/23/fieldDefinitions',
            )
        );

        $contentTypeServiceMock = $this->contentTypeServiceMock;

        $contentTypeServiceMock->expects( $this->once() )
            ->method( 'loadFieldDefinitionList' )
            ->with( $this->equalTo( '/content/types/23/fieldDefinitions' ) )
            ->will( $this->returnValue( $this->getFieldDefinitionListMock() ) );

        $this->assertEquals(
            null,
            $contentType->getFieldDefinition( 'non-existent' )
        );
    }

    protected function getFieldDefinitionListMock()
    {
        $mock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Client\\Values\\FieldDefinitionList',
            array(),
            array(),
            '',
            false
        );
        $mock->expects( $this->any() )
            ->method( 'getFieldDefinitions' )
            ->will( $this->returnValue( $this->getFieldDefinitions() ) );
        return $mock;
    }

    protected function getFieldDefinitions()
    {
        return array(
            new FieldDefinition(
                array(
                    'identifier' => 'first-field',
                )
            ),
            new FieldDefinition(
                array(
                    'identifier' => 'second-field',
                )
            ),
        );
    }
}
