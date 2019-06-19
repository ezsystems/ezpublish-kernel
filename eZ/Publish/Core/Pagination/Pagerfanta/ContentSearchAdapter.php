<?php

/**
 * File containing the ContentSearchAdapter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Pagination\Pagerfanta;

/**
 * Pagerfanta adapter for eZ Publish content search.
 * Will return results as Content objects.
 */
class ContentSearchAdapter extends ContentSearchHitAdapter
{
    /**
     * Returns a slice of the results as Content objects.
     *
     * @param int $offset The offset.
     * @param int $length The length.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
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
