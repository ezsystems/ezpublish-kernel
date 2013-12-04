<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Asset\Asset class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Asset;

use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * This class represents an asset value
 *
 * @property-read \eZ\Publish\API\Repository\Values\Asset\Variant[] $variants
 */
abstract class Asset extends Content
{
    /**
     * Variant names supported by this asset
     *
     * @var string[]
     */
    protected $supportedVariants;

    /**
     * Asset type (see SPI configuration)
     *
     * @var string
     */
    protected $type;

    /**
     * Returns a list of available (already generated) variants.
     *
     * @return \eZ\Publish\API\Repository\Values\Asset\Variant[]
     */
    abstract public function getVariants();

    /**
     * Returns the variant with $identifier.
     *
     * @return \eZ\Publish\API\Repository\Values\Asset\Variant
     * @throws NotFoundException if the variant does not exist.
     * @throws BadStateException if the variant is supported but not generate
     *         yet.
     */
    abstract public function getVariant( $identifier );

    /**
     * Returns if the variant with $identifier is generated.
     *
     * @param string $identifier
     * @return bool
     */
    abstract public function hasVariant( $identifier );

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
