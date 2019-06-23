<?php

/**
 * File containing the MultipleValuedTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\Matcher;

use eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\BaseTest;
use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued;

class MultipleValuedTest extends BaseTest
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

        $matchingConfig = is_array($matchingConfig) ? $matchingConfig : [$matchingConfig];
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
        return [
            [
                'singleValue',
                ['one', 'two', 'three'],
                [123, 'nous irons au bois'],
                456,
            ],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::getRepository
     */
    public function testInjectRepository()
    {
        $matcher = $this->getMultipleValuedMatcherMock();
        $matcher->setRepository($this->repositoryMock);
        $this->assertSame($this->repositoryMock, $matcher->getRepository());
    }

    private function getMultipleValuedMatcherMock()
    {
        return $this->getMockForAbstractClass(MultipleValued::class);
    }
}
