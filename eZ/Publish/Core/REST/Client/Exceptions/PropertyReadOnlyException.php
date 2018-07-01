<?php

/**
 * File containing the PropertyReadOnlyException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Exceptions;

use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException as APIPropertyReadOnlyException;

/**
 * This Exception is thrown on a write attempt in a read only property in a value object.
 */
class PropertyReadOnlyException extends APIPropertyReadOnlyException
{
    public function __construct($propertyName)
    {
        parent::__construct(
            sprintf('Property "%s" is read-only.', $propertyName)
        );
    }
}
