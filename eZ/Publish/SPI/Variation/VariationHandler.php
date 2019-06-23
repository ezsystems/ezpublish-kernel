<?php

/**
 * File containing the VariationHandler interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Variation;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * Interface for Variation services.
 * A variation service allows to generate variation from a given content field/version info
 * (i.e. image aliases, variations of a document - doc, pdf...).
 */
interface VariationHandler
{
    /**
     * Returns a Variation object for $field's $variationName.
     * This method is responsible to create the variation if needed.
     * Variations might be applicable for images (aliases), documents...
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $variationName
     * @param array $parameters Hash of arbitrary parameters useful to generate the variation
     *
     * @return \eZ\Publish\SPI\Variation\Values\Variation
     */
    public function getVariation(Field $field, VersionInfo $versionInfo, $variationName, array $parameters = []);
}
