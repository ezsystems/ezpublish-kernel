<?php

/**
 * File containing the Item class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Page\Parts;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * @property-read mixed $contentId Related content Id.
 * @property-read mixed $locationId Related location Id.
 * @property-read int $priority Priority of current item in its parent block.
 * @property-read \DateTime $publicationDate Date when the item has been published.
 * @property-read \DateTime|null $visibilityDate Date when the item has been made visible.
 * @property-read \DateTime|null $hiddenDate Date when the item must be hidden.
 * @property-read \DateTime|null $rotationUntilDate Date until this item can be made in rotation with other items of the same block.
 * @property-read mixed $movedTo
 * @property-read string $action Action to be executed. Can be either "add", "modify" or "remove" (see \eZ\Publish\Core\FieldType\Page\Parts\Base for ACTION_* constants)
 * @property-read string $blockId Id of page block current item belongs to.
 */
class Item extends ValueObject
{
    /** @var mixed */
    protected $contentId;

    /** @var mixed */
    protected $locationId;

    /** @var int */
    protected $priority;

    /** @var \DateTime */
    protected $publicationDate;

    /** @var \DateTime */
    protected $visibilityDate;

    /** @var \DateTime|null */
    protected $hiddenDate;

    /** @var \DateTime|null */
    protected $rotationUntilDate;

    /** @var mixed */
    protected $movedTo;

    /**
     * @see \eZ\Publish\Core\FieldType\Page\Parts\Base for ACTION_* constants
     *
     * @var string
     */
    protected $action;

    /** @var string */
    protected $blockId;

    /**
     * Hash of arbitrary attributes.
     *
     * @var array
     */
    public $attributes = [];

    /**
     * Returns available properties with their values as a simple hash.
     *
     * @return array
     */
    public function getState()
    {
        $hash = [];

        foreach ($this->getProperties() as $property) {
            $hash[$property] = $this->$property;
        }

        return $hash;
    }
}
