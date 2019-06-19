<?php

/**
 * File containing the FieldTypeParserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\Input;

use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\ContentService;
use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\FieldTypeService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry;
use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\SPI\FieldType\FieldType;
use PHPUnit\Framework\TestCase;

/**
 * FieldTypeParser test class.
 */
class FieldTypeParserTest extends TestCase
{
    protected $contentServiceMock;

    protected $contentTypeServiceMock;

    protected $fieldTypeServiceMock;

    protected $contentTypeMock;

    protected $fieldTypeMock;

    protected $fieldTypeProcessorRegistryMock;

    protected $fieldTypeProcessorMock;

    public function setUp()
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $this->fieldTypeServiceMock = $this->createMock(FieldTypeService::class);
        $this->contentTypeMock = $this->createMock(ContentType::class);
        $this->fieldTypeMock = $this->createMock(FieldType::class);
        $this->fieldTypeProcessorRegistryMock = $this->createMock(FieldTypeProcessorRegistry::class);
        $this->fieldTypeProcessorMock = $this->createMock(FieldTypeProcessor::class);
    }

    public function testParseFieldValue()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->contentServiceMock->expects($this->once())
            ->method('loadContentInfo')
            ->with('23')
            ->will(
                $this->returnValue(
                    new ContentInfo(['contentTypeId' => '42'])
                )
            );

        $contentTypeMock = $this->contentTypeMock;
        $this->contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with('42')
            ->will(
                $this->returnCallback(
                    // Avoid PHPUnit cloning
                    function () use ($contentTypeMock) {
                        return $contentTypeMock;
                    }
                )
            );

        $contentTypeMock->expects($this->once())
            ->method('getFieldDefinition')
            ->with($this->equalTo('my-field-definition'))
            ->will(
                $this->returnValue(
                    new FieldDefinition(
                        [
                            'fieldTypeIdentifier' => 'some-fancy-field-type',
                        ]
                    )
                )
            );

        $this->fieldTypeProcessorRegistryMock->expects($this->once())
            ->method('hasProcessor')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will($this->returnValue(false));

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will(
                $this->returnCallback(
                    // Avoid PHPUnit cloning
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('fromHash')
            ->with($this->equalTo([1, 2, 3]))
            ->will($this->returnValue(['foo', 'bar']));

        $this->assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseFieldValue(
                '23',
                'my-field-definition',
                [1, 2, 3]
            )
        );
    }

    public function testParseValue()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->fieldTypeProcessorRegistryMock->expects($this->once())
            ->method('hasProcessor')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will($this->returnValue(false));

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will(
                $this->returnCallback(
                    // Avoid PHPUnit cloning
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('fromHash')
            ->with($this->equalTo([1, 2, 3]))
            ->will($this->returnValue(['foo', 'bar']));

        $this->assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseValue(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    public function testParseValueWithPreProcessing()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->fieldTypeProcessorRegistryMock->expects($this->once())
            ->method('hasProcessor')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will($this->returnValue(true));

        $processor = $this->fieldTypeProcessorMock;
        $this->fieldTypeProcessorRegistryMock->expects($this->once())
            ->method('getProcessor')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will(
                $this->returnCallback(
                    function () use ($processor) {
                        return $processor;
                    }
                )
            );

        $processor->expects($this->once())
            ->method('preProcessValueHash')
            ->with([1, 2, 3])
            ->will($this->returnValue([4, 5, 6]));

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will(
                $this->returnCallback(
                    // Avoid PHPUnit cloning
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('fromHash')
            ->with($this->equalTo([4, 5, 6]))
            ->will($this->returnValue(['foo', 'bar']));

        $this->assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseValue(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    public function testParseFieldSettings()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will(
                $this->returnCallback(
                    // Avoid PHPUnit cloning
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('fieldSettingsFromHash')
            ->with($this->equalTo([1, 2, 3]))
            ->will($this->returnValue(['foo', 'bar']));

        $this->assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseFieldSettings(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    public function testParseFieldSettingsWithPreProcessing()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->fieldTypeProcessorRegistryMock->expects($this->once())
            ->method('hasProcessor')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will($this->returnValue(true));

        $processor = $this->fieldTypeProcessorMock;
        $this->fieldTypeProcessorRegistryMock->expects($this->once())
            ->method('getProcessor')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will(
                $this->returnCallback(
                    function () use ($processor) {
                        return $processor;
                    }
                )
            );

        $processor->expects($this->once())
            ->method('preProcessFieldSettingsHash')
            ->with([1, 2, 3])
            ->will($this->returnValue([4, 5, 6]));

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will(
                $this->returnCallback(
                    // Avoid PHPUnit cloning
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('fieldSettingsFromHash')
            ->with($this->equalTo([4, 5, 6]))
            ->will($this->returnValue(['foo', 'bar']));

        $this->assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseFieldSettings(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    public function testParseValidatorConfiguration()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will(
                $this->returnCallback(
                    // Avoid PHPUnit cloning
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('validatorConfigurationFromHash')
            ->with($this->equalTo([1, 2, 3]))
            ->will($this->returnValue(['foo', 'bar']));

        $this->assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseValidatorConfiguration(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    public function testParseValidatorConfigurationWithPreProcessing()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->fieldTypeProcessorRegistryMock->expects($this->once())
            ->method('hasProcessor')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will($this->returnValue(true));

        $processor = $this->fieldTypeProcessorMock;
        $this->fieldTypeProcessorRegistryMock->expects($this->once())
            ->method('getProcessor')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will(
                $this->returnCallback(
                    function () use ($processor) {
                        return $processor;
                    }
                )
            );

        $processor->expects($this->once())
            ->method('preProcessValidatorConfigurationHash')
            ->with([1, 2, 3])
            ->will($this->returnValue([4, 5, 6]));

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('some-fancy-field-type'))
            ->will(
                $this->returnCallback(
                    // Avoid PHPUnit cloning
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('validatorConfigurationFromHash')
            ->with($this->equalTo([4, 5, 6]))
            ->will($this->returnValue(['foo', 'bar']));

        $this->assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseValidatorConfiguration(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    protected function getFieldTypeParser()
    {
        return new FieldTypeParser(
            $this->contentServiceMock,
            $this->contentTypeServiceMock,
            $this->fieldTypeServiceMock,
            $this->fieldTypeProcessorRegistryMock
        );
    }
}
