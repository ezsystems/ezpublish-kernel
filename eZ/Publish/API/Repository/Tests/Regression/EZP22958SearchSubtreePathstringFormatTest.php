<?php

/**
 * File containing the EZP22958SearchSubtreePathstringFormatTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * @issue EZP-21906
 */
class EZP22958SearchSubtreePathstringFormatTest extends BaseTest
{
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Tests that path string provided for subtree criterion is valid.
     *
     * @dataProvider searchContentQueryProvider
     */
    public function testSearchContentSubtree($pathString, $expectedException = null)
    {
        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }

        $query = new Query(
            array(
                'filter' => new Criterion\Subtree($pathString),
            )
        );

        $result = $this->getRepository()->getSearchService()->findContent($query);
    }

    public function searchContentQueryProvider()
    {
        return array(
            array(
                '/1/2/',
                null,
            ),
            array(
                array('/1/2/', '/1/2/4/'),
                null,
            ),
            array(
                '/1/2',
                'InvalidArgumentException',
            ),
            array(
                array('/1/2/', '/1/2/4'),
                'InvalidArgumentException',
            ),
            array(
                '/1/id0/',
                null,
            ),
            array(
                '/1/id0',
                'InvalidArgumentException',
            ),
        );
    }
}
