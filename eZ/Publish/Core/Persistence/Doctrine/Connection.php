<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Doctrine;

use Doctrine\DBAL\Connection as DoctrineConnection;

class Connection extends DoctrineConnection
{
    /**
     * Get sequence name bound to database table and column.
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    public function getSequenceName($table, $column)
    {
        // @todo: change to <table>_<column>_seq when merged into 7.0
        return sprintf('%s_s', $table);
    }
}
