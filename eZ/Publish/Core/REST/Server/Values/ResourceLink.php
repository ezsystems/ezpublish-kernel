<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * A link to a REST resource.
 *
 * @property string $link
 */
class ResourceLink extends ValueObject
{
    /**
     * REST resource href.
     * Example: '/api/ezp/v2/content/objects/1'.
     *
     * @var string
     */
    protected $link;

    public function __construct($link)
    {
        $this->link = $link;
    }
}
