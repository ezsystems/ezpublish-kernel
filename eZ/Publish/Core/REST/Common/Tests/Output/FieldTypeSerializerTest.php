<?php

/**
 * File containing the FieldTypeSerializerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\Output;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\API\Repository\FieldType as APIFieldType;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry;
use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use PHPUnit\Framework\TestCase;

/**
 * FieldTypeSerializer test.
 */
class FieldTypeSerializerTest extends TestCase
{
    protected $fieldTypeServiceMock;

    protected $fieldTypeProcessorRegistryMock;

    protected $fieldTypeProcessorMock;

    protected $contentTypeMock;

    protected $fieldTypeMock;

    protected $generatorMock;

    public function testSerializeFieldValue()
    {
        $serializer = $this->getFieldTypeSerializer();

        $this->getGeneratorMock()->expects($this->once())
            ->method('generateFieldTypeHash')
            ->with(
                $this->equalTo('fieldValue'),
                $this->equalTo([23, 42])
            );

        $this->getContentTypeMock()->expects($this->once())
            ->method('getFieldDefinition')
            ->with(
                $this->equalTo('some-field')
            )->will(
                $this->returnValue(
                    new FieldDefinition(
                        [
                            'fieldTypeIdentifier' => 'myFancyFieldType',
                        ]
                    )
                )
            );

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('myFancyFieldType'))
            ->will(
                $this->returnCallback(
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('toHash')
            ->with($this->equalTo('my-field-value'))
            ->will($this->returnValue([23, 42]));

        $serializer->serializeFieldValue(
            $this->getGeneratorMock(),
            $this->getContentTypeMock(),
            new Field(
                [
                    'fieldDefIdentifier' => 'some-field',
                    'value' => 'my-field-value',
                ]
            )
        );
    }

    public function testSerializeFieldValueWithProcessor()
    {
        $serializer = $this->getFieldTypeSerializer();

        $this->getGeneratorMock()->expects($this->once())
            ->method('generateFieldTypeHash')
            ->with(
                $this->equalTo('fieldValue'),
                $this->equalTo(['post-processed'])
            );

        $this->getContentTypeMock()->expects($this->once())
            ->method('getFieldDefinition')
            ->with(
                $this->equalTo('some-field')
            )->will(
                $this->returnValue(
                    new FieldDefinition(
                        [
                            'fieldTypeIdentifier' => 'myFancyFieldType',
                        ]
                    )
                )
            );

        $processorMock = $this->getFieldTypeProcessorMock();
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('hasProcessor')
            ->with('myFancyFieldType')
            ->will($this->returnValue(true));
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('getProcessor')
            ->with('myFancyFieldType')
            ->will(
                $this->returnCallback(
                    function () use ($processorMock) {
                        return $processorMock;
                    }
                )
            );
        $processorMock->expects($this->once())
            ->method('postProcessValueHash')
            ->with($this->equalTo([23, 42]))
            ->will($this->returnValue(['post-processed']));

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('myFancyFieldType'))
            ->will(
                $this->returnCallback(
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('getFieldTypeIdentifier')
            ->will($this->returnValue('myFancyFieldType'));
        $fieldTypeMock->expects($this->once())
            ->method('toHash')
            ->with($this->equalTo('my-field-value'))
            ->will($this->returnValue([23, 42]));

        $serializer->serializeFieldValue(
            $this->getGeneratorMock(),
            $this->getContentTypeMock(),
            new Field(
                [
                    'fieldDefIdentifier' => 'some-field',
                    'value' => 'my-field-value',
                ]
            )
        );
    }

    public function testSerializeFieldDefaultValue()
    {
        $serializer = $this->getFieldTypeSerializer();

        $this->getGeneratorMock()->expects($this->once())
            ->method('generateFieldTypeHash')
            ->with(
                $this->equalTo('defaultValue'),
                $this->equalTo([23, 42])
            );

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('myFancyFieldType'))
            ->will(
                $this->returnCallback(
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('toHash')
            ->with($this->equalTo('my-field-value'))
            ->will($this->returnValue([23, 42]));

        $serializer->serializeFieldDefaultValue(
            $this->getGeneratorMock(),
            'myFancyFieldType',
            'my-field-value'
        );
    }

    public function testSerializeFieldSettings()
    {
        $serializer = $this->getFieldTypeSerializer();

        $this->getGeneratorMock()->expects($this->once())
            ->method('generateFieldTypeHash')
            ->with(
                $this->equalTo('fieldSettings'),
                $this->equalTo(['foo' => 'bar'])
            );

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('myFancyFieldType'))
            ->will(
                $this->returnCallback(
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('fieldSettingsToHash')
            ->with($this->equalTo('my-field-settings'))
            ->will($this->returnValue(['foo' => 'bar']));

        $serializer->serializeFieldSettings(
            $this->getGeneratorMock(),
            'myFancyFieldType',
            'my-field-settings'
        );
    }

    public function testSerializeFieldSettingsWithPostProcessing()
    {
        $serializer = $this->getFieldTypeSerializer();
        $fieldTypeMock = $this->getFieldTypeMock();

        $processorMock = $this->getFieldTypeProcessorMock();
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('hasProcessor')
            ->with('myFancyFieldType')
            ->will($this->returnValue(true));
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('getProcessor')
            ->with('myFancyFieldType')
            ->will(
                $this->returnCallback(
                    function () use ($processorMock) {
                        return $processorMock;
                    }
                )
            );
        $processorMock->expects($this->once())
            ->method('postProcessFieldSettingsHash')
            ->with($this->equalTo(['foo' => 'bar']))
            ->will($this->returnValue(['post-processed']));

        $this->getGeneratorMock()->expects($this->once())
            ->method('generateFieldTypeHash')
            ->with(
                $this->equalTo('fieldSettings'),
                $this->equalTo(['post-processed'])
            );

        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('myFancyFieldType'))
            ->will(
                $this->returnCallback(
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('fieldSettingsToHash')
            ->with($this->equalTo('my-field-settings'))
            ->will($this->returnValue(['foo' => 'bar']));

        $serializer->serializeFieldSettings(
            $this->getGeneratorMock(),
            'myFancyFieldType',
            'my-field-settings'
        );
    }

    public function testSerializeValidatorConfiguration()
    {
        $serializer = $this->getFieldTypeSerializer();

        $this->getGeneratorMock()->expects($this->once())
            ->method('generateFieldTypeHash')
            ->with(
                $this->equalTo('validatorConfiguration'),
                $this->equalTo(['bar' => 'foo'])
            );

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('myFancyFieldType'))
            ->will(
                $this->returnCallback(
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('validatorConfigurationToHash')
            ->with($this->equalTo('validator-config'))
            ->will($this->returnValue(['bar' => 'foo']));

        $serializer->serializeValidatorConfiguration(
            $this->getGeneratorMock(),
            'myFancyFieldType',
            'validator-config'
        );
    }

    public function testSerializeValidatorConfigurationWithPostProcessing()
    {
        $serializer = $this->getFieldTypeSerializer();
        $fieldTypeMock = $this->getFieldTypeMock();

        $processorMock = $this->getFieldTypeProcessorMock();
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('hasProcessor')
            ->with('myFancyFieldType')
            ->will($this->returnValue(true));
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('getProcessor')
            ->with('myFancyFieldType')
            ->will(
                $this->returnCallback(
                    function () use ($processorMock) {
                        return $processorMock;
                    }
                )
            );
        $processorMock->expects($this->once())
            ->method('postProcessValidatorConfigurationHash')
            ->with($this->equalTo(['bar' => 'foo']))
            ->will($this->returnValue(['post-processed']));

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('myFancyFieldType'))
            ->will(
                $this->returnCallback(
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $this->getGeneratorMock()->expects($this->once())
            ->method('generateFieldTypeHash')
            ->with(
                $this->equalTo('validatorConfiguration'),
                $this->equalTo(['post-processed'])
            );

        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with($this->equalTo('myFancyFieldType'))
            ->will(
                $this->returnCallback(
                    function () use ($fieldTypeMock) {
                        return $fieldTypeMock;
                    }
                )
            );

        $fieldTypeMock->expects($this->once())
            ->method('validatorConfigurationToHash')
            ->with($this->equalTo('validator-config'))
            ->will($this->returnValue(['bar' => 'foo']));

        $serializer->serializeValidatorConfiguration(
            $this->getGeneratorMock(),
            'myFancyFieldType',
            'validator-config'
        );
    }

    protected function getFieldTypeSerializer()
    {
        return new Common\Output\FieldTypeSerializer(
            $this->getFieldTypeServiceMock(),
            $this->getFieldTypeProcessorRegistryMock()
        );
    }

    protected function getFieldTypeServiceMock()
    {
        if (!isset($this->fieldTypeServiceMock)) {
            $this->fieldTypeServiceMock = $this->createMock(FieldTypeService::class);
        }

        return $this->fieldTypeServiceMock;
    }

    protected function getFieldTypeProcessorRegistryMock()
    {
        if (!isset($this->fieldTypeProcessorRegistryMock)) {
            $this->fieldTypeProcessorRegistryMock = $this->createMock(FieldTypeProcessorRegistry::class);
        }

        return $this->fieldTypeProcessorRegistryMock;
    }

    protected function getFieldTypeProcessorMock()
    {
        if (!isset($this->fieldTypeProcessorMock)) {
            $this->fieldTypeProcessorMock = $this->createMock(FieldTypeProcessor::class);
        }

        return $this->fieldTypeProcessorMock;
    }

    protected function getContentTypeMock()
    {
        if (!isset($this->contentTypeMock)) {
            $this->contentTypeMock = $this->createMock(APIContentType::class);
        }

        return $this->contentTypeMock;
    }

    protected function getFieldTypeMock()
    {
        if (!isset($this->fieldTypeMock)) {
            $this->fieldTypeMock = $this->createMock(APIFieldType::class);
        }

        return $this->fieldTypeMock;
    }

    protected function getGeneratorMock()
    {
        if (!isset($this->generatorMock)) {
            $this->generatorMock = $this->createMock(Generator::class);
        }

        return $this->generatorMock;
    }
}
