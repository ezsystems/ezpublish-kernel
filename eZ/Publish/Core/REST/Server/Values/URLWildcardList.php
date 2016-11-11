<?php

/**
 * File containing the URLWildcardList class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * URLWildcard list view model.
 */
class URLWildcardList extends RestValue
{
    /**
     * URL wildcards.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\URLWildcard[]
     */
    public $urlWildcards;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard[] $urlWildcards
     */
    public function __construct(array $urlWildcards)
    {
        $this->urlWildcards = $urlWildcards;
    }
}
