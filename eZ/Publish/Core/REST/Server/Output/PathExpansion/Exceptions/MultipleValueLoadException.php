<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\PathExpansion\Exceptions;

use Exception;

/**
 * Thrown when an embedded value was already loaded.
 */
class MultipleValueLoadException extends Exception
{
    public function __construct()
    {
        parent::__construct('Value was already loaded');
    }
}
