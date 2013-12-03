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
     * Variants of this asset
     *
     * @var \eZ\Publish\API\Repository\Values\Asset\Variant[]
     */
    protected $variants;

    /**
     * @return \eZ\Publish\API\Repository\Values\Asset\Variant[]
     */
    abstract public function getVariants();

    /**
     * @return \eZ\Publish\API\Repository\Values\Asset\Variant
     */
    abstract public function getVariant( $identifier );
}
