<?php

/**
 * File containing the LocationMatcherFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Matcher;

use eZ\Bundle\EzPublishCoreBundle\Matcher\LocationMatcherFactory;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LocationMatcherFactoryTest extends BaseMatcherFactoryTest
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
                    [
                        [$matcherServiceIdentifier, true],
                    ]
                )
            );
        $container
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            $matcherServiceIdentifier,
                            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                            $this->createMock(ViewMatcherInterface::class),
                        ],
                    ]
                )
            );

        $matcherFactory = new LocationMatcherFactory($resolverMock, $this->createMock(Repository::class));
        $matcherFactory->setContainer($container);
        $matcherFactory->match($this->getLocationMock(), 'full');
    }

    public function testSetSiteAccessNull()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $resolverMock = $this->createMock(ConfigResolverInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $resolverMock
            ->expects($this->once())
            ->method('getParameter')
            ->with('location_view')
            ->will(
                $this->returnValue(
                    [
                        'full' => [
                            'matchRule' => [
                                'template' => 'my_template.html.twig',
                                'match' => [
                                    $matcherServiceIdentifier => 'someValue',
                                ],
                            ],
                        ],
                    ]
                )
            );
        $matcherFactory = new LocationMatcherFactory($resolverMock, $this->createMock(Repository::class));
        $matcherFactory->setContainer($container);
        $matcherFactory->setSiteAccess();
    }

    public function testSetSiteAccess()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $resolverMock = $this->createMock(ConfigResolverInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $siteAccessName = 'siteaccess_name';
        $updatedMatchConfig = [
            'full' => [
                'matchRule2' => [
                    'template' => 'my_other_template.html.twig',
                    'match' => [
                        'foo' => ['bar'],
                    ],
                ],
            ],
        ];
        $resolverMock
            ->expects($this->atLeastOnce())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'location_view', null, null,
                            [
                                'full' => [
                                    'matchRule' => [
                                        'template' => 'my_template.html.twig',
                                        'match' => [
                                            $matcherServiceIdentifier => 'someValue',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        ['location_view', 'ezsettings', $siteAccessName, $updatedMatchConfig],
                    ]
                )
            );
        $matcherFactory = new LocationMatcherFactory($resolverMock, $this->createMock(Repository::class));
        $matcherFactory->setContainer($container);
        $matcherFactory->setSiteAccess(new SiteAccess($siteAccessName));

        $refObj = new \ReflectionObject($matcherFactory);
        $refProp = $refObj->getProperty('matchConfig');
        $refProp->setAccessible(true);
        $this->assertSame($updatedMatchConfig, $refProp->getValue($matcherFactory));
    }
}
