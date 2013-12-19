<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Asset\Variant class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\Asset;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * this class represents an asset variant
 * @package eZ\Publish\SPI\Persistence\Asset
 */
class Variant extends ValueObject
{
    /**
     * the unique id of the variant
     *
     * @var int|string
     */
    public $id;

    /**
     * the id of the asset asset this variant belongs to
     * @var int|string
     */
    public $assetId;

    /**
     * the identifier of the variant e.g. "thumb"
     *
     * @var string
     */
    public $variantIdentifier;

    /**
     * The URI where the variant is stored
     *
     * @var string
     */
    public $storageUri;

    /**
     * The web accessible URI of the variant
     *
     * @var string
     */
    public $webUri;

    /**
     * Map of meta data for this variant
     *
     * @var array
     */
    public $metaData;
}
