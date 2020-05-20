<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search\FieldType;

use eZ\Publish\SPI\Search\FieldType;

/**
 * Remote ID list document field.
 */
class MultipleRemoteIdentifierField extends FieldType
{
    /**
     * Search engine field type corresponding to remote ID list. The same MultipleIdentifierField due to BC.
     *
     * @see \eZ\Publish\SPI\Search\FieldType\MultipleIdentifierField
     *
     * @var string
     */
    protected $type = 'ez_mid';
}
