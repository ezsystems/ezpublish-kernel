<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Asset\AssetType class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Asset;

/**
 * Class AssetType
 *
 * @property_read string $identifier
 * 
 * @package eZ\Publish\API\Repository\Values\Asset
 */
abstract class AssetType
{

    /**
     * Asset type (see SPI configuration)
     *
     * @var string
     */
    protected $identifier;

    /**
     * Returns all supported variant identifiers.
     *
     * @return string[]
     */
    abstract public function getSupportedVariants();

    /**
     * Returns if variant $identifier is supported by the asset.
     *
     * @param string $identifier
     * @return bool
     */
    abstract public function isVariantSupported( $identifier );
}
