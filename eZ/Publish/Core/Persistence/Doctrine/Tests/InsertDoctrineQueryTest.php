<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Doctrine\Tests;

class InsertDoctrineQueryTest extends TestCase
{
    public function testGenerateInsertQuery()
    {
        $insertQuery = $this->handler->createInsertQuery();

        $insertQuery->insertInto('query_test')
            ->set('val1', '?')
            ->set('val2', 'NULL');

        $this->assertEquals(
            'INSERT INTO query_test (val1, val2) VALUES (?, NULL)',
            $insertQuery->getQuery()
        );
    }
}
