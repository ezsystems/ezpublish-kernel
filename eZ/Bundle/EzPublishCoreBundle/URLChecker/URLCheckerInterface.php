<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\URLChecker;

use eZ\Publish\API\Repository\Values\URL\URLQuery;

interface URLCheckerInterface
{
    /**
     * Checks URLs returned by given query.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URLQuery $query
     */
    public function check(URLQuery $query);
}
