<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\FieldType;

/**
 * Base class for FieldType external storage gateways.
 */
abstract class StorageGateway
{
    /**
     * Get sequence name bound to database table and column.
     *
     * Note: must be compatible with:
     * @see \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler\PostgresConnectionHandler::quoteIdentifier
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    protected function getSequenceName($table, $column)
    {
        // @todo: change to <table>_<column>_seq when merged into 7.0
        return sprintf('%s_s', $table);
    }
}
