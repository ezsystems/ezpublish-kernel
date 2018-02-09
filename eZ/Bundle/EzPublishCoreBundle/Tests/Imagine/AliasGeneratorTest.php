<?php

/**
 * File containing the AliasGeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine;

use eZ\Bundle\EzPublishCoreBundle\Imagine\AliasGenerator;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\Value as FieldTypeValue;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\Values\ImageVariation;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AliasGeneratorTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $dataLoader;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $filterManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $ioResolver;

    /**
     * @var \Liip\ImagineBundle\Imagine\Filter\FilterConfiguration
     */
    private $filterConfiguration;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var AliasGenerator
     */
    private $aliasGenerator;

    protected function setUp()
    {
        parent::setUp();
        $this->dataLoader = $this->createMock(LoaderInterface::class);
        $this->filterManager = $this->createMock(FilterManager::class);
        $this->ioResolver = $this->createMock(ResolverInterface::class);
        $this->filterConfiguration = new FilterConfiguration();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->aliasGenerator = new AliasGenerator(
            $this->dataLoader,
            $this->filterManager,
            $this->ioResolver,
            $this->filterConfiguration,
            $this->logger
        );
    }

    /**
     * @dataProvider supportsValueProvider
     */
    public function testSupportsValue($value, $isSupported)
    {
        $this->assertSame($isSupported, $this->aliasGenerator->supportsValue($value));
    }

    public function supportsValueProvider()
    {
        return array(
            array($this->createMock(FieldTypeValue::class), false),
            array(new TextLineValue(), false),
            array(new ImageValue(), true),
            array($this->createMock(ImageValue::class), true),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetVariationWrongValue()
    {
        $field = new Field(array('value' => $this->createMock(FieldTypeValue::class)));
        $this->aliasGenerator->getVariation($field, new VersionInfo(), 'foo');
    }

    public function testGetVariationNotStored()
    {
        $originalPath = 'foo/bar/image.jpg';
        $variationName = 'my_variation';
        $this->filterConfiguration->set($variationName, array());
        $imageId = '123-45';
        $imageValue = new ImageValue(array('id' => $originalPath, 'imageId' => $imageId));
        $field = new Field(array('value' => $imageValue));
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
        $this->ioResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($originalPath, $variationName)
            ->will($this->returnValue($expectedUrl));

        $expected = new ImageVariation(
            array(
                'name' => $variationName,
                'fileName' => "image_$variationName.jpg",
                'dirPath' => 'http://localhost/foo/bar',
                'uri' => $expectedUrl,
                'imageId' => $imageId,
            )
        );
        $this->assertEquals($expected, $this->aliasGenerator->getVariation($field, new VersionInfo(), $variationName));
    }

    public function testGetVariationOriginal()
    {
        $originalPath = 'foo/bar/image.jpg';
        $variationName = 'original';
        $imageId = '123-45';
        $imageValue = new ImageValue(array('id' => $originalPath, 'imageId' => $imageId));
        $field = new Field(array('value' => $imageValue));
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
            array(
                'name' => $variationName,
                'fileName' => 'image.jpg',
                'dirPath' => 'http://localhost/foo/bar',
                'uri' => $expectedUrl,
                'imageId' => $imageId,
            )
        );
        $this->assertEquals($expected, $this->aliasGenerator->getVariation($field, new VersionInfo(), $variationName));
    }

    public function testGetVariationNotStoredHavingReferences()
    {
        $originalPath = 'foo/bar/image.jpg';
        $variationName = 'my_variation';
        $reference1 = 'reference1';
        $reference2 = 'reference2';
        $configVariation = array('reference' => $reference1);
        $configReference1 = array('reference' => $reference2);
        $configReference2 = array();
        $this->filterConfiguration->set($variationName, $configVariation);
        $this->filterConfiguration->set($reference1, $configReference1);
        $this->filterConfiguration->set($reference2, $configReference2);
        $imageId = '123-45';
        $imageValue = new ImageValue(array('id' => $originalPath, 'imageId' => $imageId));
        $field = new Field(array('value' => $imageValue));
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
        $this->ioResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($originalPath, $variationName)
            ->will($this->returnValue($expectedUrl));

        $expected = new ImageVariation(
            array(
                'name' => $variationName,
                'fileName' => "image_$variationName.jpg",
                'dirPath' => 'http://localhost/foo/bar',
                'uri' => $expectedUrl,
                'imageId' => $imageId,
            )
        );
        $this->assertEquals($expected, $this->aliasGenerator->getVariation($field, new VersionInfo(), $variationName));
    }

    public function testGetVariationAlreadyStored()
    {
        $originalPath = 'foo/bar/image.jpg';
        $variationName = 'my_variation';
        $imageId = '123-45';
        $imageValue = new ImageValue(array('id' => $originalPath, 'imageId' => $imageId));
        $field = new Field(array('value' => $imageValue));
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

        $this->ioResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($originalPath, $variationName)
            ->will($this->returnValue($expectedUrl));

        $expected = new ImageVariation(
            array(
                'name' => $variationName,
                'fileName' => "image_$variationName.jpg",
                'dirPath' => 'http://localhost/foo/bar',
                'uri' => $expectedUrl,
                'imageId' => $imageId,
            )
        );
        $this->assertEquals($expected, $this->aliasGenerator->getVariation($field, new VersionInfo(), $variationName));
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

        $field = new Field(array('value' => new ImageValue()));
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
        $imageValue = new ImageValue(array('id' => $originalPath, 'imageId' => $imageId));
        $field = new Field(array('value' => $imageValue));

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
}
