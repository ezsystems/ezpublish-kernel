<?php

/**
 * File containing the IORepositoryResolverTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterConfiguration;
use eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver;
use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\Core\IO\Values\MissingBinaryFile;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\Variation\VariationPurger;
use Liip\ImagineBundle\Model\Binary;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

class IORepositoryResolverTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $ioService;

    /** @var \Symfony\Component\Routing\RequestContext */
    private $requestContext;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var IORepositoryResolver */
    private $imageResolver;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterConfiguration */
    private $filterConfiguration;

    /** @var \eZ\Publish\SPI\Variation\VariationPurger|\PHPUnit\Framework\MockObject\MockObject */
    protected $variationPurger;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator|\PHPUnit\Framework\MockObject\MockObject */
    protected $variationPathGenerator;

    protected function setUp()
    {
        parent::setUp();
        $this->ioService = $this->createMock(IOServiceInterface::class);
        $this->requestContext = new RequestContext();
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->filterConfiguration = new FilterConfiguration();
        $this->filterConfiguration->setConfigResolver($this->configResolver);
        $this->variationPurger = $this->createMock(VariationPurger::class);
        $this->variationPathGenerator = $this->createMock(VariationPathGenerator::class);
        $this->imageResolver = new IORepositoryResolver(
            $this->ioService,
            $this->requestContext,
            $this->filterConfiguration,
            $this->variationPurger,
            $this->variationPathGenerator
        );
    }

    /**
     * @dataProvider getFilePathProvider
     */
    public function testGetFilePath($path, $filter, $expected)
    {
        $this->variationPathGenerator
            ->expects($this->once())
            ->method('getVariationPath')
            ->with($path, $filter)
            ->willReturn($expected);
        $this->assertSame($expected, $this->imageResolver->getFilePath($path, $filter));
    }

    public function getFilePathProvider()
    {
        return [
            ['Tardis/bigger/in-the-inside/RiverSong.jpg', 'thumbnail', 'Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg'],
            ['Tardis/bigger/in-the-inside/RiverSong', 'foo', 'Tardis/bigger/in-the-inside/RiverSong_foo'],
            ['CultOfScaro/Dalek-fisherman.png', 'so_ridiculous', 'CultOfScaro/Dalek-fisherman_so_ridiculous.png'],
            ['CultOfScaro/Dalek-fisherman', 'so_ridiculous', 'CultOfScaro/Dalek-fisherman_so_ridiculous'],
        ];
    }

    public function testIsStoredImageExists()
    {
        $filter = 'thumbnail';
        $path = 'Tardis/bigger/in-the-inside/RiverSong.jpg';
        $aliasPath = 'Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg';

        $this->variationPathGenerator
            ->expects($this->once())
            ->method('getVariationPath')
            ->with($path, $filter)
            ->willReturn($aliasPath);

        $this->ioService
            ->expects($this->once())
            ->method('exists')
            ->with($aliasPath)
            ->will($this->returnValue(true));

        $this->assertTrue($this->imageResolver->isStored($path, $filter));
    }

    public function testIsStoredImageDoesntExist()
    {
        $filter = 'thumbnail';
        $path = 'Tardis/bigger/in-the-inside/RiverSong.jpg';
        $aliasPath = 'Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg';

        $this->variationPathGenerator
            ->expects($this->once())
            ->method('getVariationPath')
            ->with($path, $filter)
            ->willReturn($aliasPath);

        $this->ioService
            ->expects($this->once())
            ->method('exists')
            ->with($aliasPath)
            ->will($this->returnValue(false));

        $this->assertFalse($this->imageResolver->isStored($path, $filter));
    }

    /**
     * @dataProvider resolveProvider
     */
    public function testResolve($path, $filter, $variationPath, $requestUrl, $expected)
    {
        if ($requestUrl) {
            $this->requestContext->fromRequest(Request::create($requestUrl));
        }

        $this->ioService
            ->expects($this->any())
            ->method('loadBinaryFile')
            ->will($this->returnValue(new BinaryFile(['uri' => $variationPath])));

        $this->variationPathGenerator
            ->expects($this->any())
            ->method('getVariationPath')
            ->willReturn($variationPath);

        $result = $this->imageResolver->resolve($path, $filter);
        $this->assertSame($expected, $result);
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException
     */
    public function testResolveMissing()
    {
        $path = 'foo/something.jpg';
        $this->ioService
            ->expects($this->once())
            ->method('loadBinaryFile')
            ->with($path)
            ->will($this->returnValue(new MissingBinaryFile()));

        $this->imageResolver->resolve($path, 'some_filter');
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException
     */
    public function testResolveNotFound()
    {
        $path = 'foo/something.jpg';
        $this->ioService
            ->expects($this->once())
            ->method('loadBinaryFile')
            ->with($path)
            ->will($this->throwException(new NotFoundException('foo', 'bar')));

        $this->imageResolver->resolve($path, 'some_filter');
    }

    public function resolveProvider()
    {
        return [
            [
                'Tardis/bigger/in-the-inside/RiverSong.jpg',
                IORepositoryResolver::VARIATION_ORIGINAL,
                '/var/doctorwho/storage/images/Tardis/bigger/in-the-inside/RiverSong.jpg',
                null,
                'http://localhost/var/doctorwho/storage/images/Tardis/bigger/in-the-inside/RiverSong.jpg',
            ],
            [
                'Tardis/bigger/in-the-inside/RiverSong.jpg',
                'thumbnail',
                '/var/doctorwho/storage/images/Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg',
                null,
                'http://localhost/var/doctorwho/storage/images/Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg',
            ],
            [
                'Tardis/bigger/in-the-inside/RiverSong.jpg',
                'thumbnail',
                '/var/doctorwho/storage/images/Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg',
                'http://localhost',
                'http://localhost/var/doctorwho/storage/images/Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg',
            ],
            [
                'CultOfScaro/Dalek-fisherman.png',
                'so_ridiculous',
                '/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman_so_ridiculous.png',
                'http://doctor.who:7890',
                'http://doctor.who:7890/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman_so_ridiculous.png',
            ],
            [
                'CultOfScaro/Dalek-fisherman.png',
                'so_ridiculous',
                '/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman_so_ridiculous.png',
                'https://doctor.who',
                'https://doctor.who/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman_so_ridiculous.png',
            ],
            [
                'CultOfScaro/Dalek-fisherman.png',
                'so_ridiculous',
                '/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman_so_ridiculous.png',
                'https://doctor.who:1234',
                'https://doctor.who:1234/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman_so_ridiculous.png',
            ],
            [
                'CultOfScaro/Dalek-fisherman.png',
                IORepositoryResolver::VARIATION_ORIGINAL,
                '/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman.png',
                'https://doctor.who:1234',
                'https://doctor.who:1234/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman.png',
            ],
        ];
    }

    public function testStore()
    {
        $filter = 'thumbnail';
        $path = 'Tardis/bigger/in-the-inside/RiverSong.jpg';
        $aliasPath = 'Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg';
        $binary = new Binary('foo content', 'some/mime-type');

        $createStruct = new BinaryFileCreateStruct();
        $this->ioService
            ->expects($this->once())
            ->method('newBinaryCreateStructFromLocalFile')
            ->will($this->returnValue($createStruct));

        $this->ioService
            ->expects($this->once())
            ->method('createBinaryFile');

        $this->imageResolver->store($binary, $path, $filter);
    }

    public function testRemoveEmptyFilters()
    {
        $originalPath = 'foo/bar/test.jpg';
        $filters = ['filter1' => true, 'filter2' => true, 'chaud_cacao' => true];

        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('image_variations')
            ->will($this->returnValue($filters));

        $this->variationPathGenerator
            ->expects($this->exactly(count($filters)))
            ->method('getVariationPath')
            ->will(
                $this->returnValueMap(
                    [
                        ['foo/bar/test.jpg', 'filter1', 'foo/bar/test_filter1.jpg '],
                        ['foo/bar/test.jpg', 'filter2', 'foo/bar/test_filter2.jpg '],
                        ['foo/bar/test.jpg', 'chaud_cacao', 'foo/bar/test_chaud_cacao.jpg'],
                    ]
                )
            );

        $fileToDelete = 'foo/bar/test_chaud_cacao.jpg';
        $this->ioService
            ->expects($this->exactly(count($filters)))
            ->method('exists')
            ->will(
                $this->returnValueMap(
                    [
                        ['foo/bar/test_filter1.jpg', false],
                        ['foo/bar/test_filter2.jpg', false],
                        [$fileToDelete, true],
                    ]
                )
            );

        $binaryFile = new BinaryFile(['id' => $fileToDelete]);
        $this->ioService
            ->expects($this->once())
            ->method('loadBinaryFile')
            ->with($fileToDelete)
            ->will($this->returnValue($binaryFile));

        $this->ioService
            ->expects($this->once())
            ->method('deleteBinaryFile')
            ->with($binaryFile);

        $this->imageResolver->remove([$originalPath], []);
    }

    public function testRemoveWithFilters()
    {
        $originalPath = 'foo/bar/test.jpg';
        $filters = ['filter1', 'filter2', 'chaud_cacao'];

        $this->configResolver
            ->expects($this->never())
            ->method('getParameter')
            ->with('image_variations')
            ->will($this->returnValue([]));

        $this->variationPathGenerator
            ->expects($this->exactly(count($filters)))
            ->method('getVariationPath')
            ->will(
                $this->returnValueMap(
                    [
                        ['foo/bar/test.jpg', 'filter1', 'foo/bar/test_filter1.jpg '],
                        ['foo/bar/test.jpg', 'filter2', 'foo/bar/test_filter2.jpg '],
                        ['foo/bar/test.jpg', 'chaud_cacao', 'foo/bar/test_chaud_cacao.jpg'],
                    ]
                )
            );

        $fileToDelete = 'foo/bar/test_chaud_cacao.jpg';
        $this->ioService
            ->expects($this->exactly(count($filters)))
            ->method('exists')
            ->will(
                $this->returnValueMap(
                    [
                        ['foo/bar/test_filter1.jpg', false],
                        ['foo/bar/test_filter2.jpg', false],
                        [$fileToDelete, true],
                    ]
                )
            );

        $binaryFile = new BinaryFile(['id' => $fileToDelete]);
        $this->ioService
            ->expects($this->once())
            ->method('loadBinaryFile')
            ->with($fileToDelete)
            ->will($this->returnValue($binaryFile));

        $this->ioService
            ->expects($this->once())
            ->method('deleteBinaryFile')
            ->with($binaryFile);

        $this->imageResolver->remove([$originalPath], $filters);
    }
}
