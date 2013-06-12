<?php
/**
 * File containing the ContentBasedMatcherFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests;

abstract class ContentBasedMatcherFactoryTest extends AbstractMatcherFactoryTest
{
    /**
     * @expectedException InvalidArgumentException
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory::getMatcher
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
                            '\\eZ\Publish\Core\MVC\Symfony\Matcher\Block\\Type' => true
                        )
                    )
                )
            )
        );
        $matcherFactory->match( $this->getMatchableValueObject(), 'full' );
    }
}
