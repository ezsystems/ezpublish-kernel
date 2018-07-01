<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * @property-read int $code
 * @property-read string $message
 * @property-read string $description
 * @property-read mixed $trace
 * @property-read string $file
 * @property-read int $line
 */
class ErrorMessage extends ValueObject
{
    protected $code;

    protected $message;

    protected $description;

    protected $details;

    protected $trace;

    protected $file;

    protected $line;
}
