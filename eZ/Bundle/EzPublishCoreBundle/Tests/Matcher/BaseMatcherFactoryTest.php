<?php

/**
 * File containing the BaseMatcherFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Matcher;

use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\View\BlockView;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use PHPUnit_Framework_TestCase;

abstract class BaseMatcherFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $matcherServiceIdentifier
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResolverMock($matcherServiceIdentifier)
    {
        $resolverMock = $this->getMock('eZ\\Publish\\Core\\MVC\\ConfigResolverInterface');
        $resolverMock
        ->expects($this->atLeastOnce())
        ->method('getParameter')
        ->with($this->logicalOr('location_view', 'content_view', 'block_view'))
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
                    'block' => array(
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

        return $resolverMock;
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLocationMock(array $properties = array())
    {
        $view = new ContentView();
        $view->setLocation(new Location($properties));

        return $view;
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    protected function getContentInfoMock(array $properties = array())
    {
        $view = new ContentView();
        $view->setContent(new Content($properties));

        return $view;
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBlockMock(array $properties = array())
    {
        $view = new BlockView();
        $view->setBlock(new Block($properties));

        return $view;
    }
}
