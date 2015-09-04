<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\REST\Client\Values\ViewResult;

/**
 * @property-read string $identifier
 * @property-read \eZ\Publish\API\Repository\Values\Content\Query $query
 * @property-read ViewResult $result
 */
class View extends ValueObject
{
    /** @var string */
    protected $identifier;

    /** @var Query */
    protected $query;

    /** @var ViewResult */
    protected $result;
}
