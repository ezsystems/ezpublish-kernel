<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\Facet\UserFacet class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Search\Facet;

use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * This class holds counts for content owned, created or modified by users.
 */
class UserFacet extends Facet
{
    /**
     * An array with user id as key and count of matching content objects as value.
     *
     * @var array
     */
    public $entries;
}
