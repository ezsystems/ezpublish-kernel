<?php

/**
 * File containing the LocationSearchAdapter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Pagination\Pagerfanta;

/**
 * Pagerfanta adapter for eZ Publish content search.
 * Will return results as Location objects.
 */
class LocationSearchAdapter extends LocationSearchHitAdapter
{
    /**
     * Returns a slice of the results as Location objects.
     *
     * @param int $offset The offset.
     * @param int $length The length.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function getSlice($offset, $length)
    {
        $list = [];
        foreach (parent::getSlice($offset, $length) as $hit) {
            $list[] = $hit->valueObject;
        }

        return $list;
    }
}
