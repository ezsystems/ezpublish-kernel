<?php

/**
 * File containing the ContentBasedMatcherFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\View\BlockView;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use PHPUnit_Framework_TestCase;

abstract class AbstractMatcherFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Returns a valid ValueObject (supported by current MatcherFactory), that will match the test rules.
     * i.e. Should return eZ\Publish\API\Repository\Values\Content\Location for LocationMatcherFactory.
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    abstract protected function getMatchableValueObject();

    /**
     * Returns a valid ValueObject (supported by current MatcherFactory), that won't match the test rules.
     * i.e. Should return eZ\Publish\API\Repository\Values\Content\Location for LocationMatcherFactory.
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    abstract protected function getNonMatchableValueObject();

    /**
     * Returns the matcher class to use in test configuration.
     * Must be relative to the matcher's ::MATCHER_RELATIVE_NAMESPACE constant.
     * i.e.: Id\\Location.
     *
     * @return string
     */
    abstract protected function getMatcherClass();

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     */
    public function testMatchFailNoViewType()
    {
        $matcherFactory = new $this->matcherFactoryClass($this->getRepositoryMock(), array());
        $this->assertNull($matcherFactory->match($this->getContentView(), 'full'));
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::getMatcher
     */
    public function testMatchInvalidMatcher()
    {
        $matcherFactory = new $this->matcherFactoryClass(
            $this->getRepositoryMock(),
            array(
                'full' => array(
                    'test' => array(
                        'template' => 'foo.html.twig',
                        'match' => array(
                            'NonExistingMatcher' => true,
                        ),
                    ),
                ),
            )
        );
        $matcherFactory->match($this->getMatchableValueObject(), 'full');
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::getMatcher
     */
    public function testMatch()
    {
        $expectedConfigHash = array(
            'template' => 'foo.html.twig',
            'match' => array(
                $this->getMatcherClass() => 456,
            ),
        );
        $matcherFactory = new $this->matcherFactoryClass(
            $this->getRepositoryMock(),
            array(
                'full' => array(
                    'not_matching' => array(
                        'template' => 'bar.html.twig',
                        'match' => array(
                            $this->getMatcherClass() => 123,
                        ),
                    ),
                    'test' => $expectedConfigHash,
                ),
            )
        );
        $configHash = $matcherFactory->match($this->getMatchableValueObject());
        $this->assertArrayHasKey('matcher', $configHash);
        $this->assertInstanceOf(
            constant("$this->matcherFactoryClass::MATCHER_RELATIVE_NAMESPACE") . '\\' . $this->getMatcherClass(),
            $configHash['matcher']
        );
        // Calling a 2nd time to check if the result has been properly cached in memory
        $this->assertSame(
            $configHash,
            $matcherFactory->match(
                $this->getMatchableValueObject(),
                'full'
            )
        );

        unset($configHash['matcher']);
        $this->assertSame($expectedConfigHash, $configHash);
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::getMatcher
     */
    public function testMatchFail()
    {
        $matcherFactory = new $this->matcherFactoryClass(
            $this->getRepositoryMock(),
            array(
                'full' => array(
                    'not_matching' => array(
                        'template' => 'bar.html.twig',
                        'match' => array(
                            $this->getMatcherClass() => 123,
                        ),
                    ),
                    'test' => array(
                        'template' => 'foo.html.twig',
                        'match' => array(
                            $this->getMatcherClass() => 456,
                        ),
                    ),
                ),
            )
        );
        $this->assertNull(
            $matcherFactory->match(
                $this->getNonMatchableValueObject(),
                'full'
            )
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\Core\\Repository\\Repository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    protected function getContentView(array $contentInfoProperties = [], array $locationProperties = [])
    {
        $view = new ContentView();
        $view->setContent(
            new Content(
                [
                    'versionInfo' => new VersionInfo(
                        [
                            'contentInfo' => new ContentInfo($contentInfoProperties),
                        ]
                    ),
                ]
            )
        );
        $view->setLocation(new Location($locationProperties));

        return $view;
    }

    /**
     * @param array $blockProperties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\Symfony\View\BlockView
     */
    protected function getBlockView(array $blockProperties = array())
    {
        $view = new BlockView();
        $view->setViewType('full');
        $view->setBlock(new Block($blockProperties));

        return $view;
    }
}
