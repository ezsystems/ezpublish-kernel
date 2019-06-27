<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine;

use eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver;
use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderAliasGenerator;
use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use eZ\Publish\Core\FieldType\Null\Value as NullValue;
use eZ\Publish\Core\FieldType\Value as FieldTypeValue;
use eZ\Publish\Core\FieldType\Value;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\Values\ImageVariation;
use eZ\Publish\SPI\Variation\VariationHandler;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use PHPUnit\Framework\TestCase;

class PlaceholderAliasGeneratorTest extends TestCase
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderAliasGenerator */
    private $aliasGenerator;

    /** @var \eZ\Publish\SPI\Variation\VariationHandler|\PHPUnit_Framework_MockObject_MockObject */
    private $innerAliasGenerator;

    /** @var \eZ\Publish\Core\IO\IOServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $ioService;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver|\PHPUnit_Framework_MockObject_MockObject */
    private $ioResolver;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider|\PHPUnit_Framework_MockObject_MockObject */
    private $placeholderProvider;

    /** @var array */
    private $placeholderOptions;

    protected function setUp()
    {
        $this->innerAliasGenerator = $this->createMock(VariationHandler::class);
        $this->ioService = $this->createMock(IOServiceInterface::class);
        $this->ioResolver = $this->createMock(IORepositoryResolver::class);
        $this->placeholderProvider = $this->createMock(PlaceholderProvider::class);
        $this->placeholderOptions = [
            'foo' => 'foo',
            'bar' => 'bar',
        ];

        $this->aliasGenerator = new PlaceholderAliasGenerator(
            $this->innerAliasGenerator,
            $this->ioResolver,
            $this->ioService
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetVariationWrongValue()
    {
        $field = new Field([
            'value' => $this->createMock(FieldTypeValue::class),
        ]);

        $this->aliasGenerator->setPlaceholderProvider(
            $this->placeholderProvider,
            $this->placeholderOptions
        );
        $this->aliasGenerator->getVariation($field, new VersionInfo(), 'foo');
    }

    /**
     * @dataProvider getVariationProvider
     */
    public function testGetVariationSkipsPlaceholderGeneration(Field $field, APIVersionInfo $versionInfo, string $variationName, array $parameters)
    {
        $expectedVariation = $this->createMock(ImageVariation::class);

        $this->ioResolver
            ->expects($this->never())
            ->method('resolve')
            ->with($field->value->id, IORepositoryResolver::VARIATION_ORIGINAL);

        $this->placeholderProvider
            ->expects($this->never())
            ->method('getPlaceholder')
            ->with($field->value, $this->placeholderOptions);

        $this->innerAliasGenerator
            ->expects($this->once())
            ->method('getVariation')
            ->with($field, $versionInfo, $variationName, $parameters)
            ->willReturn($expectedVariation);

        $actualVariation = $this->aliasGenerator->getVariation(
            $field, $versionInfo, $variationName, $parameters
        );

        $this->assertEquals($expectedVariation, $actualVariation);
    }

    /**
     * @dataProvider getVariationProvider
     */
    public function testGetVariationOriginalFound(Field $field, APIVersionInfo $versionInfo, string $variationName, array $parameters)
    {
        $expectedVariation = $this->createMock(ImageVariation::class);

        $this->ioResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($field->value->id, IORepositoryResolver::VARIATION_ORIGINAL);

        $this->innerAliasGenerator
            ->expects($this->once())
            ->method('getVariation')
            ->with($field, $versionInfo, $variationName, $parameters)
            ->willReturn($expectedVariation);

        $this->aliasGenerator->setPlaceholderProvider(
            $this->placeholderProvider,
            $this->placeholderOptions
        );

        $actualVariation = $this->aliasGenerator->getVariation(
            $field, $versionInfo, $variationName, $parameters
        );

        $this->assertEquals($expectedVariation, $actualVariation);
    }

    /**
     * @dataProvider getVariationProvider
     */
    public function testGetVariationOriginalNotFound(Field $field, APIVersionInfo $versionInfo, string $variationName, array $parameters)
    {
        $placeholderPath = '/tmp/placeholder.jpg';
        $binaryCreateStruct = new BinaryFileCreateStruct();
        $expectedVariation = $this->createMock(ImageVariation::class);

        $this->ioResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($field->value->id, IORepositoryResolver::VARIATION_ORIGINAL)
            ->willThrowException($this->createMock(NotResolvableException::class));

        $this->placeholderProvider
            ->expects($this->once())
            ->method('getPlaceholder')
            ->with($field->value, $this->placeholderOptions)
            ->willReturn($placeholderPath);

        $this->ioService
            ->expects($this->once())
            ->method('newBinaryCreateStructFromLocalFile')
            ->with($placeholderPath)
            ->willReturn($binaryCreateStruct);

        $this->ioService
            ->expects($this->once())
            ->method('createBinaryFile')
            ->with($binaryCreateStruct);

        $this->aliasGenerator->setPlaceholderProvider(
            $this->placeholderProvider,
            $this->placeholderOptions
        );

        $this->innerAliasGenerator
            ->expects($this->once())
            ->method('getVariation')
            ->with($field, $versionInfo, $variationName, $parameters)
            ->willReturn($expectedVariation);

        $actualVariation = $this->aliasGenerator->getVariation(
            $field, $versionInfo, $variationName, $parameters
        );

        $this->assertEquals($field->value->id, $binaryCreateStruct->id);
        $this->assertEquals($expectedVariation, $actualVariation);
    }

    /**
     * @dataProvider supportsValueProvider
     */
    public function testSupportsValue(Value $value, bool $isSupported)
    {
        $this->assertSame($isSupported, $this->aliasGenerator->supportsValue($value));
    }

    public function supportsValueProvider(): array
    {
        return [
            [new NullValue(), false],
            [new ImageValue(), true],
        ];
    }

    public function getVariationProvider(): array
    {
        $field = new Field([
            'value' => new ImageValue([
                'id' => 'images/6/8/4/0/486-10-eng-GB/photo.jpg',
            ]),
        ]);

        return [
            [$field, new VersionInfo(), 'thumbnail', []],
        ];
    }
}
