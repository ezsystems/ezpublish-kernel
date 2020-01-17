<?php

/**
 * File containing the AliasGeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine;

use eZ\Bundle\EzPublishCoreBundle\Imagine\AliasGenerator;
use eZ\Bundle\EzPublishCoreBundle\Imagine\Variation\ImagineAwareAliasGenerator;
use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;
use eZ\Publish\SPI\FieldType\Value as FieldTypeValue;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\Values\ImageVariation;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AliasGeneratorTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Liip\ImagineBundle\Binary\Loader\LoaderInterface */
    private $dataLoader;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Liip\ImagineBundle\Imagine\Filter\FilterManager */
    private $filterManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface */
    private $ioResolver;

    /** @var \Liip\ImagineBundle\Imagine\Filter\FilterConfiguration */
    private $filterConfiguration;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface */
    private $logger;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Imagine\Image\ImagineInterface */
    private $imagine;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\AliasGenerator */
    private $aliasGenerator;

    /** @var \eZ\Publish\SPI\Variation\VariationHandler */
    private $decoratedAliasGenerator;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Imagine\Image\BoxInterface */
    private $box;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Imagine\Image\ImageInterface */
    private $image;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\IO\IOServiceInterface */
    private $ioService;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator */
    private $variationPathGenerator;

    protected function setUp()
    {
        parent::setUp();
        $this->dataLoader = $this->createMock(LoaderInterface::class);
        $this->filterManager = $this
            ->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ioResolver = $this->createMock(ResolverInterface::class);
        $this->filterConfiguration = new FilterConfiguration();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->imagine = $this->createMock(ImagineInterface::class);
        $this->box = $this->createMock(BoxInterface::class);
        $this->image = $this->createMock(ImageInterface::class);
        $this->ioService = $this->createMock(IOServiceInterface::class);
        $this->variationPathGenerator = $this->createMock(VariationPathGenerator::class);
        $this->aliasGenerator = new AliasGenerator(
            $this->dataLoader,
            $this->filterManager,
            $this->ioResolver,
            $this->filterConfiguration,
            $this->logger
        );
        $this->decoratedAliasGenerator = new ImagineAwareAliasGenerator(
            $this->aliasGenerator,
            $this->variationPathGenerator,
            $this->ioService,
            $this->imagine
        );
    }

    /**
     * @dataProvider supportsValueProvider
     * @param \eZ\Publish\SPI\FieldType\Value $value
     * @param bool $isSupported
     */
    public function testSupportsValue($value, $isSupported)
    {
        $this->assertSame($isSupported, $this->aliasGenerator->supportsValue($value));
    }

    /**
     * Data provider for testSupportsValue.
     *
     * @see testSupportsValue
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function supportsValueProvider()
    {
        return [
            [$this->createMock(FieldTypeValue::class), false],
            [new TextLineValue(), false],
            [new ImageValue(), true],
            [$this->createMock(ImageValue::class), true],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetVariationWrongValue()
    {
        $field = new Field(['value' => $this->createMock(FieldTypeValue::class)]);
        $this->aliasGenerator->getVariation($field, new VersionInfo(), 'foo');
    }

    /**
     * Test obtaining Image Variation that hasn't been stored yet.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testGetVariationNotStored()
    {
        $originalPath = 'foo/bar/image.jpg';
        $variationName = 'my_variation';
        $this->filterConfiguration->set($variationName, []);
        $imageId = '123-45';
        $imageWidth = 300;
        $imageHeight = 300;
        $expectedUrl = "http://localhost/foo/bar/image_$variationName.jpg";

        $this->ioResolver
            ->expects($this->once())
            ->method('isStored')
            ->with($originalPath, $variationName)
            ->will($this->returnValue(false));

        $this->logger
            ->expects($this->once())
            ->method('debug');

        $binary = $this->createMock(BinaryInterface::class);
        $this->dataLoader
            ->expects($this->once())
            ->method('find')
            ->with($originalPath)
            ->will($this->returnValue($binary));
        $this->filterManager
            ->expects($this->once())
            ->method('applyFilter')
            ->with($binary, $variationName)
            ->will($this->returnValue($binary));
        $this->ioResolver
            ->expects($this->once())
            ->method('store')
            ->with($binary, $originalPath, $variationName);

        $this->assertImageVariationIsCorrect(
            $expectedUrl,
            $variationName,
            $imageId,
            $originalPath,
            $imageWidth,
            $imageHeight
        );
    }

    public function testGetVariationOriginal()
    {
        $originalPath = 'foo/bar/image.jpg';
        $variationName = 'original';
        $imageId = '123-45';
        $imageWidth = 300;
        $imageHeight = 300;
        // original images already contain proper width and height
        $imageValue = new ImageValue(
            [
                'id' => $originalPath,
                'imageId' => $imageId,
                'width' => $imageWidth,
                'height' => $imageHeight,
            ]
        );
        $field = new Field(['value' => $imageValue]);
        $expectedUrl = 'http://localhost/foo/bar/image.jpg';

        $this->ioResolver
            ->expects($this->never())
            ->method('isStored')
            ->with($originalPath, $variationName)
            ->will($this->returnValue(false));

        $this->logger
            ->expects($this->once())
            ->method('debug');

        $this->ioResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($originalPath, $variationName)
            ->will($this->returnValue($expectedUrl));

        $expected = new ImageVariation(
            [
                'name' => $variationName,
                'fileName' => 'image.jpg',
                'dirPath' => 'http://localhost/foo/bar',
                'uri' => $expectedUrl,
                'imageId' => $imageId,
                'height' => $imageHeight,
                'width' => $imageWidth,
            ]
        );
        $this->assertEquals($expected, $this->decoratedAliasGenerator->getVariation($field, new VersionInfo(), $variationName));
    }

    /**
     * Test obtaining Image Variation that hasn't been stored yet and has multiple references.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testGetVariationNotStoredHavingReferences()
    {
        $originalPath = 'foo/bar/image.jpg';
        $variationName = 'my_variation';
        $reference1 = 'reference1';
        $reference2 = 'reference2';
        $configVariation = ['reference' => $reference1];
        $configReference1 = ['reference' => $reference2];
        $configReference2 = [];
        $this->filterConfiguration->set($variationName, $configVariation);
        $this->filterConfiguration->set($reference1, $configReference1);
        $this->filterConfiguration->set($reference2, $configReference2);
        $imageId = '123-45';
        $imageWidth = 300;
        $imageHeight = 300;
        $expectedUrl = "http://localhost/foo/bar/image_$variationName.jpg";

        $this->ioResolver
            ->expects($this->once())
            ->method('isStored')
            ->with($originalPath, $variationName)
            ->will($this->returnValue(false));

        $this->logger
            ->expects($this->once())
            ->method('debug');

        $binary = $this->createMock(BinaryInterface::class);
        $this->dataLoader
            ->expects($this->once())
            ->method('find')
            ->with($originalPath)
            ->will($this->returnValue($binary));

        // Filter manager is supposed to be called 3 times to generate references, and then passed variation.
        $this->filterManager
            ->expects($this->at(0))
            ->method('applyFilter')
            ->with($binary, $reference2)
            ->will($this->returnValue($binary));
        $this->filterManager
            ->expects($this->at(1))
            ->method('applyFilter')
            ->with($binary, $reference1)
            ->will($this->returnValue($binary));
        $this->filterManager
            ->expects($this->at(2))
            ->method('applyFilter')
            ->with($binary, $variationName)
            ->will($this->returnValue($binary));

        $this->ioResolver
            ->expects($this->once())
            ->method('store')
            ->with($binary, $originalPath, $variationName);

        $this->assertImageVariationIsCorrect(
            $expectedUrl,
            $variationName,
            $imageId,
            $originalPath,
            $imageWidth,
            $imageHeight
        );
    }

    /**
     * Test obtaining Image Variation that has been stored already.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testGetVariationAlreadyStored()
    {
        $originalPath = 'foo/bar/image.jpg';
        $variationName = 'my_variation';
        $imageId = '123-45';
        $imageWidth = 300;
        $imageHeight = 300;
        $expectedUrl = "http://localhost/foo/bar/image_$variationName.jpg";

        $this->ioResolver
            ->expects($this->once())
            ->method('isStored')
            ->with($originalPath, $variationName)
            ->will($this->returnValue(true));

        $this->logger
            ->expects($this->once())
            ->method('debug');

        $this->dataLoader
            ->expects($this->never())
            ->method('find');
        $this->filterManager
            ->expects($this->never())
            ->method('applyFilter');
        $this->ioResolver
            ->expects($this->never())
            ->method('store');

        $this->assertImageVariationIsCorrect(
            $expectedUrl,
            $variationName,
            $imageId,
            $originalPath,
            $imageWidth,
            $imageHeight
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\MVC\Exception\SourceImageNotFoundException
     */
    public function testGetVariationOriginalNotFound()
    {
        $this->dataLoader
            ->expects($this->once())
            ->method('find')
            ->will($this->throwException(new NotLoadableException()));

        $field = new Field(['value' => new ImageValue()]);
        $this->aliasGenerator->getVariation($field, new VersionInfo(), 'foo');
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidVariationException
     */
    public function testGetVariationInvalidVariation()
    {
        $originalPath = 'foo/bar/image.jpg';
        $variationName = 'my_variation';
        $imageId = '123-45';
        $imageValue = new ImageValue(['id' => $originalPath, 'imageId' => $imageId]);
        $field = new Field(['value' => $imageValue]);

        $this->ioResolver
            ->expects($this->once())
            ->method('isStored')
            ->with($originalPath, $variationName)
            ->will($this->returnValue(true));

        $this->logger
            ->expects($this->once())
            ->method('debug');

        $this->dataLoader
            ->expects($this->never())
            ->method('find');
        $this->filterManager
            ->expects($this->never())
            ->method('applyFilter');
        $this->ioResolver
            ->expects($this->never())
            ->method('store');

        $this->ioResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($originalPath, $variationName)
            ->will($this->throwException(new NotResolvableException()));

        $this->aliasGenerator->getVariation($field, new VersionInfo(), $variationName);
    }

    /**
     * Prepare required Imagine-related mocks and assert that the Image Variation is as expected.
     *
     * @param string $expectedUrl
     * @param string $variationName
     * @param string $imageId
     * @param string $originalPath
     * @param int $imageWidth
     * @param int $imageHeight
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    protected function assertImageVariationIsCorrect(
        $expectedUrl,
        $variationName,
        $imageId,
        $originalPath,
        $imageWidth,
        $imageHeight
    ) {
        $imageValue = new ImageValue(['id' => $originalPath, 'imageId' => $imageId]);
        $field = new Field(['value' => $imageValue]);

        $binaryFile = new \eZ\Publish\Core\IO\Values\BinaryFile(
            [
                'uri' => "_aliases/{$variationName}/foo/bar/image.jpg",
            ]
        );

        $this->ioResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($originalPath, $variationName)
            ->will($this->returnValue($expectedUrl));

        $this->variationPathGenerator
            ->expects($this->once())
            ->method('getVariationPath')
            ->with($originalPath, $variationName)
            ->willReturn($binaryFile->uri);

        $this->ioService
            ->expects($this->once())
            ->method('loadBinaryFile')
            ->withAnyParameters()
            ->willReturn($binaryFile);

        $this->ioService
            ->expects($this->once())
            ->method('getFileContents')
            ->with($binaryFile)
            ->willReturn('file contents mock');

        $this->imagine
            ->expects($this->once())
            ->method('load')
            ->with('file contents mock')
            ->will($this->returnValue($this->image));
        $this->image
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($this->box));

        $this->box
            ->expects($this->once())
            ->method('getWidth')
            ->will($this->returnValue($imageWidth));
        $this->box
            ->expects($this->once())
            ->method('getHeight')
            ->will($this->returnValue($imageHeight));

        $expected = new ImageVariation(
            [
                'name' => $variationName,
                'fileName' => "image_$variationName.jpg",
                'dirPath' => 'http://localhost/foo/bar',
                'uri' => $expectedUrl,
                'imageId' => $imageId,
                'height' => $imageHeight,
                'width' => $imageWidth,
            ]
        );
        $this->assertEquals(
            $expected,
            $this->decoratedAliasGenerator->getVariation($field, new VersionInfo(), $variationName)
        );
    }
}
