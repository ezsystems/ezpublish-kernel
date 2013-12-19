<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Asset\Variant\UpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\Asset\Variant;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * this class is used to update a variant
 *
 * @package eZ\Publish\SPI\Persistence\Asset\Variant
 */
class UpdateStruct extends ValueObject
{

    /**
     * if set the URI of the variant is changed
     *
     * @var string
     */
    public $storageUri;

    /**
     * if set the web URI of the variant is changed
     *
     * @var string
     */
    public $webUri;

    /**
     * if set the metadata map of the variant is changed
     *
     * @var array
     */
    public $metaData;
}
