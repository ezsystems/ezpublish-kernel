<?php

/**
 * File containing the AssetFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Assetic;

use eZ\Bundle\EzPublishCoreBundle\Assetic\AssetFactory;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface;
use Symfony\Bundle\AsseticBundle\Tests\Factory\AssetFactoryTest as BaseTest;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Assetic\Asset\AssetCollectionInterface;
use Assetic\Asset\AssetInterface;
use ReflectionObject;

class AssetFactoryTest extends BaseTest
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $parser;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->parser = $this->createMock(DynamicSettingParserInterface::class);
    }

    protected function getAssetFactory()
    {
        $assetFactory = new AssetFactory(
            $this->createMock(KernelInterface::class),
            $this->createMock(ContainerInterface::class),
            $this->createMock(ParameterBagInterface::class),
            '/root/dir/'
        );
        $assetFactory->setConfigResolver($this->configResolver);
        $assetFactory->setDynamicSettingParser($this->parser);

        return $assetFactory;
    }

    public function testParseInputArray()
    {
        $assetFactory = $this->getAssetFactory();
        $fooValues = ['bar', 'baz'];
        $input = '$foo$';
        $this->parser
            ->expects($this->any())
            ->method('isDynamicSetting')
            ->will(
                $this->returnValueMap(
                    [
                        ['$foo$', true],
                        [$fooValues[0], false],
                        [$fooValues[1], false],
                    ]
                )
            );
        $this->parser
            ->expects($this->once())
            ->method('parseDynamicSetting')
            ->with($input)
            ->will($this->returnValue(['param' => 'foo', 'namespace' => null, 'scope' => null]));
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('foo', null, null)
            ->will($this->returnValue($fooValues));

        $refFactory = new ReflectionObject($assetFactory);
        $refMethod = $refFactory->getMethod('parseInput');
        $refMethod->setAccessible(true);
        $parseInputResult = $refMethod->invoke($assetFactory, $input, ['vars' => []]);

        $this->assertInstanceOf(AssetCollectionInterface::class, $parseInputResult);
        $this->assertCount(count($fooValues), $parseInputResult->all());
    }

    public function testParseInputString()
    {
        $assetFactory = $this->getAssetFactory();
        $fooValue = 'bar';
        $input = '$foo$';
        $this->parser
            ->expects($this->any())
            ->method('isDynamicSetting')
            ->will(
                $this->returnValueMap(
                    [
                        ['$foo$', true],
                        ['bar', false],
                    ]
                )
            );
        $this->parser
            ->expects($this->once())
            ->method('parseDynamicSetting')
            ->with($input)
            ->will($this->returnValue(['param' => 'foo', 'namespace' => null, 'scope' => null]));
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('foo', null, null)
            ->will($this->returnValue($fooValue));

        $refFactory = new ReflectionObject($assetFactory);
        $refMethod = $refFactory->getMethod('parseInput');
        $refMethod->setAccessible(true);
        $parseInputResult = $refMethod->invoke($assetFactory, $input, ['vars' => []]);

        $this->assertInstanceOf(AssetInterface::class, $parseInputResult);
    }
}
