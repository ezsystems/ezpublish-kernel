<?php

/**
 * File containing the BlockMatcherFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests;

class BlockMatcherFactoryTest extends AbstractMatcherFactoryTest
{
    protected $matcherFactoryClass = 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\BlockMatcherFactory';

    /**
     * Returns a valid ValueObject (supported by current MatcherFactory), that will match the test rules.
     * i.e. Should return eZ\Publish\API\Repository\Values\Content\Location for LocationMatcherFactory.
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    protected function getMatchableValueObject()
    {
        return $this->getBlockView(array('id' => 456));
    }

    /**
     * Returns a valid ValueObject (supported by current MatcherFactory), that won't match the test rules.
     * i.e. Should return eZ\Publish\API\Repository\Values\Content\Location for LocationMatcherFactory.
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    protected function getNonMatchableValueObject()
    {
        return $this->getBlockView(array('id' => 123456789));
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\BlockMatcherFactory::getMatcher
     */
    public function testMatchNonContentBasedMatcher()
    {
        $matcherFactory = new $this->matcherFactoryClass(
            $this->getRepositoryMock(),
            array(
                'full' => array(
                    'test' => array(
                        'template' => 'foo.html.twig',
                        'match' => array(
                            '\\eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\Id\\Location' => true,
                        ),
                    ),
                ),
            )
        );
        $matcherFactory->match($this->getMatchableValueObject(), 'full');
    }

    /**
     * Returns the matcher class to use in test configuration.
     * Must be relative to the matcher's ::MATCHER_RELATIVE_NAMESPACE constant.
     * i.e.: Id\\Location.
     *
     * @return string
     */
    protected function getMatcherClass()
    {
        return 'Id\\Block';
    }
}
