<?php
/**
 * File containing the MultipleValuedTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher;

use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;

class MultipleValuedTest extends BaseTest
{
    /**
     * @dataProvider matchingConfigProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::getValues
     */
    public function testSetMatchingConfig( $matchingConfig )
    {
        $matcher = $this->getMultipleValuedMatcherMock();
        $matcher->setMatchingConfig( $matchingConfig );
        $values = $matcher->getValues();
        $this->assertInternalType( 'array', $values );

        $matchingConfig = is_array( $matchingConfig ) ? $matchingConfig : array( $matchingConfig );
        foreach ( $matchingConfig as $val )
        {
            $this->assertContains( $val, $values );
        }
    }

    /**
     * Returns a set of matching values, either single or multiple.
     *
     * @return array
     */
    public function matchingConfigProvider()
    {
        return array(
            array(
                'singleValue',
                array( 'one', 'two', 'three' ),
                array( 123, 'nous irons au bois' ),
                456
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::getRepository
     */
    public function testInjectRepository()
    {
        $matcher = $this->getMultipleValuedMatcherMock();
        $matcher->setRepository( $this->repositoryMock );
        $this->assertSame( $this->repositoryMock, $matcher->getRepository() );
    }

    private function getMultipleValuedMatcherMock()
    {
        return $this->getMockForAbstractClass( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured\\Matcher\\MultipleValued' );
    }
}
