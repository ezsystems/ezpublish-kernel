<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\SectionCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a section.
 */
class SectionCreateStruct extends ValueObject
{
    /**
     * Unique string identifier of the section.
     *
     * @required
     *
     * @var string
     */
    public $identifier;

    /**
     * Name of the section.
     *
     * @required
     *
     * @var string
     */
    public $name;
}
