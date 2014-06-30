<?php
/**
 * This file is part of the eZ Publish package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\Base\Exceptions\NotFound;

use eZ\Publish\Core\Base\Exceptions\Httpable;
use Exception;
use RuntimeException;

/**
 * FieldType Not Found Exception
 */
class FieldTypeNotFoundException extends RuntimeException implements Httpable
{
    /**
     * Creates a FieldType Not Found exception with info on how to fix
     *
     * @param string $fieldType
     * @param \Exception|null $previous
     */
    public function __construct( $fieldType, Exception $previous = null )
    {
        parent::__construct(
            "FieldType '{$fieldType}' not found, needs to be implemented or configured to use "
            . "FieldType\\Null\\Type (%ezpublish.fieldType.eznull.class%)",
            self::INTERNAL_ERROR,
            $previous
        );
    }
}
