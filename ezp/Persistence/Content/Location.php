<?php
/**
 * File containing the Location class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\AbstractValueObject;

/**
 * @todo Add missing attributes (OMS), eg sort info
 */
class Location extends AbstractValueObject
{
    /**
     * Location ID.
     *
     * @var mixed Location ID.
     */
    public $id;

    /**
     * Location priority
     *
     * Position of the Location among its siblings when sorted using priority
     * sort order.
     *
     * @var int
     */
    public $priority;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var bool
     */
    public $invisible;

    /**
     * Remote ID.
     *
     * @var mixed
     */
    public $remoteId;

    /**
     * ID of the corresponding {@link Content}.
     *
     * @var mixed Content ID.
     */
    public $contentId;

    /**
     * Parent ID.
     *
     * @var mixed Location ID.
     */
    public $parentId;

    /**
     * Legacy format of the url alias.
     *
     * This field might be removed in a later version.
     *
     * @var string
     */
    public $pathIdentificationString;

    /**
     * The materialized path of the location entry.
     *
     * @var string
     */
    public $pathString;

    /**
     * Timestamp of the latest update of a content object in a sub location.
     *
     * @var int
     */
    public $modifiedSubLocation;

    /**
     * Identifier of the main location.
     *
     * If the content object in this location has multiple locations,
     * $mainLocationId will point to the main one.
     *
     * @var mixed
     */
    public $mainLocationId;

    /**
     * Depth location has in the location tree.
     *
     * @var int
     */
    public $depth;
}
?>
