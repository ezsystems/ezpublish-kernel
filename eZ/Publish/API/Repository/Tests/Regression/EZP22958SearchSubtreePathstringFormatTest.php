<?php

/**
 * File containing the EZP22958SearchSubtreePathstringFormatTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Issue EZP-21906.
 */
class EZP22958SearchSubtreePathstringFormatTest extends BaseTest
{
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Tests that invalid path string provided for subtree criterion result in exception.
     *
     * @dataProvider searchContentQueryWithInvalidDataProvider
     * @expectedException \InvalidArgumentException
     */
    public function testSearchContentSubtreeShouldThrowException($pathString)
    {
        $query = new Query(
            [
                'filter' => new Criterion\Subtree($pathString),
            ]
        );

        $result = $this->getRepository()->getSearchService()->findContent($query);
    }

    /**
     * Tests that path string provided for subtree criterion is valid.
     *
     * @dataProvider searchContentQueryProvider
     */
    public function testSearchContentSubtree($pathString)
    {
        $query = new Query(
            [
                'filter' => new Criterion\Subtree($pathString),
            ]
        );

        $result = $this->getRepository()->getSearchService()->findContent($query);
    }

    public function searchContentQueryProvider()
    {
        return [
            [
                '/1/2/',
            ],
            [
                ['/1/2/', '/1/2/4/'],
            ],
            [
                '/1/id0/',
            ],
        ];
    }

    public function searchContentQueryWithInvalidDataProvider()
    {
        return [
            [
                '/1/2',
            ],
            [
                ['/1/2/', '/1/2/4'],
            ],
            [
                '/1/id0',
            ],
        ];
    }
}
