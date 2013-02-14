<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\Facet\UserFacet class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Search\Facet;

use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * This class holds counts for content owned, created or modified by users
 *
 */
class UserFacet extends Facet
{
    /**
     * An array with user id as key and count of matching content objects as value
     *
     * @var array
     */
    public $entries;
}
