<?php

/**
 * File containing the Section class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

class Section extends ValueObject
{
    /**
     * Id of the section.
     *
     * @var int
     */
    public $id;

    /**
     * Unique identifier of the section.
     *
     * @var string
     */
    public $identifier;

    /**
     * Name of the section.
     *
     * @var string
     */
    public $name;
}
