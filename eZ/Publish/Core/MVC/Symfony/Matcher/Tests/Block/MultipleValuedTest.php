<?php

/**
 * File containing the MultipleValuedTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\Block;

use PHPUnit_Framework_TestCase;

class MultipleValuedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider matchingConfigProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::getValues
     */
    public function testSetMatchingConfig($matchingConfig)
    {
        $matcher = $this->getMultipleValuedMatcherMock();
        $matcher->setMatchingConfig($matchingConfig);
        $values = $matcher->getValues();
        $this->assertInternalType('array', $values);

        $matchingConfig = is_array($matchingConfig) ? $matchingConfig : array($matchingConfig);
        foreach ($matchingConfig as $val) {
            $this->assertContains($val, $values);
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
                array('one', 'two', 'three'),
                array(123, 'nous irons au bois'),
                456,
            ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::getRepository
     */
    public function testInjectRepository()
    {
        $matcher = $this->getMultipleValuedMatcherMock();
        $repositoryMock = $this->getMock('eZ\\Publish\\API\\Repository\\Repository');
        $matcher->setRepository($repositoryMock);
        $this->assertSame($repositoryMock, $matcher->getRepository());
    }

    private function getMultipleValuedMatcherMock()
    {
        return $this->getMockForAbstractClass('eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\Block\\MultipleValued');
    }
}
