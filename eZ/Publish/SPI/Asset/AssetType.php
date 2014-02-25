<?php
/**
 * File containing the eZ\Publish\SPI\Asset\AssetType class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Asset;

/**
 * This class represents an asset type containing variant definitions
 *
 * @package eZ\Publish\SPI\Asset
 */
class AssetType
{

    /**
     * Asset type (see SPI configuration)
     *
     * @var string
     */
    protected $identifier;

    /**
     *
     * @var \eZ\Publish\SPI\Asset\VariantDefinition[]
     */
    protected $variantDefinitions;
}
