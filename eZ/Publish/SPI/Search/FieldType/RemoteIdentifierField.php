<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search\FieldType;

use eZ\Publish\SPI\Search\FieldType;

/**
 * Remote ID document field.
 */
final class RemoteIdentifierField extends FieldType
{
    /**
     * Search engine field type corresponding to remote ID. The same as IdentifierField due to BC.
     *
     * @see \eZ\Publish\SPI\Search\FieldType\IdentifierField
     *
     * @var string
     */
    protected $type = 'ez_id';
}
