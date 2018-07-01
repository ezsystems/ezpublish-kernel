<?php

/**
 * File containing an interface for the Zeta Database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy;

/**
 * This interface is added in Doctrine DBAL introduction for Legacy Storage
 * and exists only to prevent breakages in the external storage Gateways of
 * user implemented FieldTypes that might rely on its existence for setting
 * data storage connection.
 *
 * @see \eZ\Publish\Core\FieldType\StorageGateway::setConnection()
 * @see \eZ\Publish\Core\Persistence\Database\DatabaseHandler
 */
interface EzcDbHandler
{
}
