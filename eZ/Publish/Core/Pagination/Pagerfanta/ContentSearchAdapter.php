<?php
/**
 * File containing the CriterionAdapter class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
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
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content The slice.
     */
    public function getSlice( $offset, $length )
    {
        $list = array();
        foreach ( parent::getSlice( $offset, $length ) as $hit )
        {
            $list[] = $hit->valueObject;
        }

        return $list;
    }
}
