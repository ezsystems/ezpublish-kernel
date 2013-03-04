<?php
/**
 * File containing the Item class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\Parts;

/**
 * @property-read mixed $contentId Related content Id.
 * @property-read mixed $locationId Related location Id.
 * @property-read \DateTime $publicationDate Date when the item has been published.
 * @property-read \DateTime $visibilityDate Date when the item has been made visible.
 * @property-read \DateTime|null $hiddenDate Date when the item must be hidden.
 * @property-read \DateTime|null $rotationUntilDate Date until this item can be made in rotation with other items of the same block.
 * @property-read mixed $movedTo
 * @property-read string $action Action to be executed. Can be either "add", "modify" or "remove" (see \eZ\Publish\Core\FieldType\Page\Parts\Base for ACTION_* constants)
 */
class Item extends Base
{
    /**
     * @var mixed
     */
    protected $contentId;

    /**
     * @var mixed
     */
    protected $locationId;

    /**
     * @var \DateTime
     */
    protected $publicationDate;

    /**
     * @var \DateTime
     */
    protected $visibilityDate;

    /**
     * @var \DateTime|null
     */
    protected $hiddenDate;

    /**
     * @var \DateTime|null
     */
    protected $rotationUntilDate;

    /**
     * @var mixed
     */
    protected $movedTo;

    /**
     * @see \eZ\Publish\Core\FieldType\Page\Parts\Base for ACTION_* constants
     *
     * @var string
     */
    protected $action;
}
