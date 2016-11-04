<?php

/**
 * File containing the URLAliasList class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * URLAlias list view model.
 */
class URLAliasList extends RestValue
{
    /**
     * URL aliases.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\URLAlias[]
     */
    public $urlAliases;

    /**
     * Path used to load the list of URL aliases.
     *
     * @var string
     */
    public $path;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias[] $urlAliases
     * @param string $path
     */
    public function __construct(array $urlAliases, $path)
    {
        $this->urlAliases = $urlAliases;
        $this->path = $path;
    }
}
