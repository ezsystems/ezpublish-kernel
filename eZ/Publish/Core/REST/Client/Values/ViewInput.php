<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

class ViewInput extends ValueObject
{
    public $identifier;

    public $contentQuery;

    public $locationQuery;
}
