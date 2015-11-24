<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

class ErrorMessage extends ValueObject
{
    protected $code;

    protected $message;

    protected $description;

    protected $trace;

    protected $file;

    protected $line;
}
