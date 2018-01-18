<?php

/**
 * File containing the BlockMatcherFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Matcher;

use eZ\Bundle\EzPublishCoreBundle\Matcher\BlockMatcherFactory;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\Block\MatcherInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockMatcherFactoryTest extends BaseMatcherFactoryTest
{
    public function testGetMatcherForLocation()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $resolverMock = $this->getResolverMock($matcherServiceIdentifier);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->atLeastOnce())
            ->method('has')
            ->will(
                $this->returnValueMap(
                    array(
                        array($matcherServiceIdentifier, true),
                    )
                )
            );
        $container
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    array(
                        array($matcherServiceIdentifier, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->createMock(MatcherInterface::class)),
                    )
                )
            );

        $matcherFactory = new BlockMatcherFactory($resolverMock, $this->createMock(Repository::class));
        $matcherFactory->setContainer($container);
        $matcherFactory->match($this->getBlockMock());
    }

    public function testSetSiteAccessNull()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $resolverMock = $this->createMock(ConfigResolverInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $resolverMock
            ->expects($this->once())
            ->method('getParameter')
            ->with('block_view')
            ->will(
                $this->returnValue(
                    array(
                        'full' => array(
                            'matchRule' => array(
                                'template' => 'my_template.html.twig',
                                'match' => array(
                                    $matcherServiceIdentifier => 'someValue',
                                ),
                            ),
                        ),
                    )
                )
            );
        $matcherFactory = new BlockMatcherFactory($resolverMock, $this->createMock(Repository::class));
        $matcherFactory->setContainer($container);
        $matcherFactory->setSiteAccess();
    }

    public function testSetSiteAccess()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $resolverMock = $this->createMock(ConfigResolverInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $siteAccessName = 'siteaccess_name';
        $updatedMatchConfig = array(
            'full' => array(
                'matchRule2' => array(
                    'template' => 'my_other_template.html.twig',
                    'match' => array(
                        'foo' => array('bar'),
                    ),
                ),
            ),
        );
        $resolverMock
            ->expects($this->atLeastOnce())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    array(
                        array(
                            'block_view', null, null,
                            array(
                                'full' => array(
                                    'matchRule' => array(
                                        'template' => 'my_template.html.twig',
                                        'match' => array(
                                            $matcherServiceIdentifier => 'someValue',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        array('block_view', 'ezsettings', $siteAccessName, $updatedMatchConfig),
                    )
                )
            );
        $matcherFactory = new BlockMatcherFactory($resolverMock, $this->createMock(Repository::class));
        $matcherFactory->setContainer($container);
        $matcherFactory->setSiteAccess(new SiteAccess($siteAccessName));

        $refObj = new \ReflectionObject($matcherFactory);
        $refProp = $refObj->getProperty('matchConfig');
        $refProp->setAccessible(true);
        $this->assertSame($updatedMatchConfig, $refProp->getValue($matcherFactory));
    }
}
