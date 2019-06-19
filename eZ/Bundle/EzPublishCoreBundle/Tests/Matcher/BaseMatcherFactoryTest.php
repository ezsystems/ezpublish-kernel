<?php

/**
 * File containing the BaseMatcherFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Matcher;

use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\View\BlockView;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use PHPUnit\Framework\TestCase;

abstract class BaseMatcherFactoryTest extends TestCase
{
    /**
     * @param string $matcherServiceIdentifier
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getResolverMock($matcherServiceIdentifier)
    {
        $resolverMock = $this->createMock(ConfigResolverInterface::class);
        $resolverMock
        ->expects($this->atLeastOnce())
        ->method('getParameter')
        ->with($this->logicalOr('location_view', 'content_view', 'block_view'))
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
                    'block' => [
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

        return $resolverMock;
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getLocationMock(array $properties = [])
    {
        $view = new ContentView();
        $view->setLocation(new Location($properties));

        return $view;
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    protected function getContentInfoMock(array $properties = [])
    {
        $view = new ContentView();
        $view->setContent(new Content($properties));

        return $view;
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getBlockMock(array $properties = [])
    {
        $view = new BlockView();
        $view->setBlock(new Block($properties));

        return $view;
    }
}
