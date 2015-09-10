<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values;

use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * @property-read SearchHit[] $searchHits
 */
class ViewResult extends ValueObject
{
    /** @var SearchHit[] */
    protected $searchHits;
}
