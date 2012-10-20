<?php
/**
 * File containing the VariationHandlerInterface interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI;

use eZ\Publish\API\Repository\Values\Content\Field,
    eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * Interface for Variant services.
 * A variant service allows to generate variation from a given content field/version info
 * (i.e. image aliases, variations of a document - doc, pdf...)
 */
interface VariationHandlerInterface
{
    /**
     * Returns a Variant object for $field's $variantName.
     * This method is responsible to create the variant if needed.
     * Variants might be applicable for images (aliases), documents...
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $variantName
     * @return \eZ\Publish\API\Repository\Values\File\Variant
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidVariantException
     */
    public function getVariation( Field $field, VersionInfo $versionInfo, $variantName );
}
