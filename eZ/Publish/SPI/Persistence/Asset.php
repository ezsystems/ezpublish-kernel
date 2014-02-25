<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Asset class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence;

/**
 * this class represents an asset
 *
 * @package eZ\Publish\SPI\Persistence
 */
class Asset extends ValueObject
{

    /**
     * the unique id of the asset
     *
     * @var int|string
     */
    public $id;

    /**
     * the generated variants of the asset. The keys of the array are variant identifiers
     *
     * @var \eZ\Publish\SPI\Persistence\Asset\Variant[]
     */
    public $variants;
}
