<?php
/**
 * File containing the IORepositoryResolverTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterConfiguration;
use eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\Core\IO\Values\MissingBinaryFile;
use Liip\ImagineBundle\Model\Binary;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

class IORepositoryResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ioService;

    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    private $requestContext;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var IORepositoryResolver
     */
    private $imageResolver;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterConfiguration
     */
    private $filterConfiguration;

    protected function setUp()
    {
        parent::setUp();
        $this->ioService = $this->getMock( 'eZ\Publish\Core\IO\IOServiceInterface' );
        $this->requestContext = new RequestContext();
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $this->filterConfiguration = new FilterConfiguration();
        $this->filterConfiguration->setConfigResolver( $this->configResolver );
        $this->imageResolver = new IORepositoryResolver( $this->ioService, $this->requestContext, $this->filterConfiguration );
    }

    /**
     * @dataProvider getFilePathProvider
     */
    public function testGetFilePath( $path, $filter, $expected )
    {
        $this->assertSame( $expected, $this->imageResolver->getFilePath( $path, $filter ) );
    }

    public function getFilePathProvider()
    {
        return array(
            array( 'Tardis/bigger/in-the-inside/RiverSong.jpg', 'thumbnail', 'Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg' ),
            array( 'Tardis/bigger/in-the-inside/RiverSong', 'foo', 'Tardis/bigger/in-the-inside/RiverSong_foo' ),
            array( 'CultOfScaro/Dalek-fisherman.png', 'so_ridiculous', 'CultOfScaro/Dalek-fisherman_so_ridiculous.png' ),
            array( 'CultOfScaro/Dalek-fisherman', 'so_ridiculous', 'CultOfScaro/Dalek-fisherman_so_ridiculous' ),
        );
    }

    public function testIsStoredImageExists()
    {
        $filter = 'thumbnail';
        $path = 'Tardis/bigger/in-the-inside/RiverSong.jpg';
        $aliasPath = 'Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg';

        $this->ioService
            ->expects( $this->once() )
            ->method( 'exists' )
            ->with( $aliasPath )
            ->will( $this->returnValue( true ) );

        $this->assertTrue( $this->imageResolver->isStored( $path, $filter ) );
    }

    public function testIsStoredImageDoesntExist()
    {
        $filter = 'thumbnail';
        $path = 'Tardis/bigger/in-the-inside/RiverSong.jpg';
        $aliasPath = 'Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg';

        $this->ioService
            ->expects( $this->once() )
            ->method( 'exists' )
            ->with( $aliasPath )
            ->will( $this->returnValue( false ) );

        $this->assertFalse( $this->imageResolver->isStored( $path, $filter ) );
    }

    /**
     * @dataProvider resolveProvider
     */
    public function testResolve( $path, $filter, $requestUrl, $expected )
    {
        if ( $requestUrl )
        {
            $this->requestContext->fromRequest( Request::create( $requestUrl ) );
        }

        $storageDir = '/var/doctorwho/storage/images';
        $this->ioService
            ->expects( $this->once() )
            ->method( 'loadBinaryFile' )
            ->with( $path )
            ->will( $this->returnValue( new BinaryFile( array( 'uri' => "$storageDir/$path" ) ) ) );

        $result = $this->imageResolver->resolve( $path, $filter );
        $this->assertSame( $expected, $result );
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException
     */
    public function testResolveMissing()
    {
        $path = 'foo/something.jpg';
        $this->ioService
            ->expects( $this->once() )
            ->method( 'loadBinaryFile' )
            ->with( $path )
            ->will( $this->returnValue( new MissingBinaryFile() ) );

        $this->imageResolver->resolve( $path, 'some_filter' );
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException
     */
    public function testResolveNotFound()
    {
        $path = 'foo/something.jpg';
        $this->ioService
            ->expects( $this->once() )
            ->method( 'loadBinaryFile' )
            ->with( $path )
            ->will( $this->throwException( new NotFoundException( 'foo', 'bar' ) ) );

        $this->imageResolver->resolve( $path, 'some_filter' );
    }

    public function resolveProvider()
    {
        return array(
            array(
                'Tardis/bigger/in-the-inside/RiverSong.jpg',
                IORepositoryResolver::VARIATION_ORIGINAL, null,
                'http://localhost/var/doctorwho/storage/images/Tardis/bigger/in-the-inside/RiverSong.jpg'
            ),
            array(
                'Tardis/bigger/in-the-inside/RiverSong.jpg',
                'thumbnail', null,
                'http://localhost/var/doctorwho/storage/images/Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg'
            ),
            array(
                'Tardis/bigger/in-the-inside/RiverSong.jpg',
                'thumbnail', 'http://localhost',
                'http://localhost/var/doctorwho/storage/images/Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg'
            ),
            array(
                'CultOfScaro/Dalek-fisherman.png',
                'so_ridiculous', 'http://doctor.who:7890',
                'http://doctor.who:7890/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman_so_ridiculous.png'
            ),
            array(
                'CultOfScaro/Dalek-fisherman.png',
                'so_ridiculous', 'https://doctor.who',
                'https://doctor.who/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman_so_ridiculous.png'
            ),
            array(
                'CultOfScaro/Dalek-fisherman.png',
                'so_ridiculous', 'https://doctor.who:1234',
                'https://doctor.who:1234/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman_so_ridiculous.png'
            ),
            array(
                'CultOfScaro/Dalek-fisherman.png',
                IORepositoryResolver::VARIATION_ORIGINAL, 'https://doctor.who:1234',
                'https://doctor.who:1234/var/doctorwho/storage/images/CultOfScaro/Dalek-fisherman.png'
            ),
        );
    }

    public function testStore()
    {
        $filter = 'thumbnail';
        $path = 'Tardis/bigger/in-the-inside/RiverSong.jpg';
        $aliasPath = 'Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg';
        $binary = new Binary( 'foo content', 'some/mime-type' );

        $createStruct = new BinaryFileCreateStruct();
        $this->ioService
            ->expects( $this->once() )
            ->method( 'newBinaryCreateStructFromLocalFile' )
            ->will( $this->returnValue( $createStruct ) );

        $expectedStruct = clone $createStruct;
        $expectedStruct->id = $aliasPath;
        $this->ioService
            ->expects( $this->once() )
            ->method( 'createBinaryFile' )
            ->with( $this->equalTo( $expectedStruct ) );

        $this->imageResolver->store( $binary, $path, $filter );
    }

    public function testRemoveEmptyFilters()
    {
        $originalPath = 'foo/bar/test.jpg';
        $filters = array( 'filter1' => true, 'filter2' => true, 'chaud_cacao' => true );

        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'image_variations' )
            ->will( $this->returnValue( $filters ) );

        $fileToDelete = 'foo/bar/test_chaud_cacao.jpg';
        $this->ioService
            ->expects( $this->exactly( count( $filters ) ) )
            ->method( 'exists' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'foo/bar/test_filter1.jpg', false ),
                        array( 'foo/bar/test_filter2.jpg', false ),
                        array( $fileToDelete, true ),
                    )
                )
            );

        $binaryFile = new BinaryFile( array( 'id' => $fileToDelete ) );
        $this->ioService
            ->expects( $this->once() )
            ->method( 'loadBinaryFile' )
            ->with( $fileToDelete )
            ->will( $this->returnValue( $binaryFile ) );
        $this->ioService
            ->expects( $this->once() )
            ->method( 'deleteBinaryFile' )
            ->with( $binaryFile );

        $this->imageResolver->remove( array( $originalPath ), array() );
    }

    public function testRemoveWithFilters()
    {
        $originalPath = 'foo/bar/test.jpg';
        $filters = array( 'filter1', 'filter2', 'chaud_cacao' );

        $this->configResolver
            ->expects( $this->never() )
            ->method( 'getParameter' )
            ->with( 'image_variations' )
            ->will( $this->returnValue( array() ) );

        $fileToDelete = 'foo/bar/test_chaud_cacao.jpg';
        $this->ioService
            ->expects( $this->exactly( count( $filters ) ) )
            ->method( 'exists' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'foo/bar/test_filter1.jpg', false ),
                        array( 'foo/bar/test_filter2.jpg', false ),
                        array( $fileToDelete, true ),
                    )
                )
            );

        $binaryFile = new BinaryFile( array( 'id' => $fileToDelete ) );
        $this->ioService
            ->expects( $this->once() )
            ->method( 'loadBinaryFile' )
            ->with( $fileToDelete )
            ->will( $this->returnValue( $binaryFile ) );
        $this->ioService
            ->expects( $this->once() )
            ->method( 'deleteBinaryFile' )
            ->with( $binaryFile );

        $this->imageResolver->remove( array( $originalPath ), $filters );
    }
}
