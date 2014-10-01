<?php
/**
 * File containing the IOFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishDFSBundle\Bundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishDFSBundle\ApiLoader\DFSFactory;
use ReflectionObject;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\SPI\IO\MimeTypeDetector
     */
    private $mimeDetector;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var DFSFactory
     */
    private $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $this->factory = new DFSFactory( $this->configResolver );
    }

    public function testBuildDFSHandler()
    {
        $this->configResolver
            ->expects( $this->any() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'var_dir', null, null, '/path/to/legacy/var/test/' ),
                        array( 'storage_dir', null, null, 'storage' )
                    )
                )
            );

        $handler = $this->factory->buildDFSIOHandler(
            $this->getMock( 'eZ\Publish\Core\IO\Handler\DFS\MetadataHandler' ),
            $this->getMock( 'eZ\Publish\Core\IO\Handler\DFS\BinaryDataHandler' )
        );

        self::assertInstanceOf( 'eZ\Publish\Core\IO\Handler\DFS', $handler );

        $refObject = new ReflectionObject( $handler );
        $refStoragePrefixProperty = $refObject->getProperty( 'storagePrefix' );
        $refStoragePrefixProperty->setAccessible( true );

        self::assertEquals( '/path/to/legacy/var/test/storage', $refStoragePrefixProperty->getValue( $handler ) );
    }
}
