<?php

/**
 * File containing the ContentTypeTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Values\ContentType;

use eZ\Publish\Core\REST\Client\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Client\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\REST\Client\Values\FieldDefinitionList;
use eZ\Publish\Core\REST\Client\ContentTypeService;
use PHPUnit\Framework\TestCase;

class ContentTypeTest extends TestCase
{
    protected $contentTypeServiceMock;

    public function setUp()
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
    }

    public function testGetName()
    {
        $contentType = new ContentType(
            $this->contentTypeServiceMock,
            array(
                'names' => array('eng-US' => 'Sindelfingen', 'eng-GB' => 'Bielefeld'),
            )
        );

        $this->assertEquals(
            'Sindelfingen',
            $contentType->getName('eng-US')
        );
        $this->assertEquals(
            'Bielefeld',
            $contentType->getName('eng-GB')
        );
    }

    public function testGetDescription()
    {
        $contentType = new ContentType(
            $this->contentTypeServiceMock,
            array(
                'descriptions' => array('eng-US' => 'Sindelfingen', 'eng-GB' => 'Bielefeld'),
            )
        );

        $this->assertEquals(
            'Sindelfingen',
            $contentType->getDescription('eng-US')
        );
        $this->assertEquals(
            'Bielefeld',
            $contentType->getDescription('eng-GB')
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

        $contentTypeServiceMock->expects($this->once())
            ->method('loadFieldDefinitionList')
            ->with($this->equalTo('/content/types/23/fieldDefinitions'))
            ->will($this->returnValue($this->getFieldDefinitionListMock()));

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

        $contentTypeServiceMock->expects($this->once())
            ->method('loadFieldDefinitionList')
            ->with($this->equalTo('/content/types/23/fieldDefinitions'))
            ->will($this->returnValue($this->getFieldDefinitionListMock()));

        $fieldDefinitions = $this->getFieldDefinitions();

        $this->assertEquals(
            $fieldDefinitions[1],
            $contentType->getFieldDefinition('second-field')
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

        $contentTypeServiceMock->expects($this->once())
            ->method('loadFieldDefinitionList')
            ->with($this->equalTo('/content/types/23/fieldDefinitions'))
            ->will($this->returnValue($this->getFieldDefinitionListMock()));

        $this->assertEquals(
            null,
            $contentType->getFieldDefinition('non-existent')
        );
    }

    protected function getFieldDefinitionListMock()
    {
        $mock = $this->createMock(FieldDefinitionList::class);
        $mock->expects($this->any())
            ->method('getFieldDefinitions')
            ->will($this->returnValue($this->getFieldDefinitions()));

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
