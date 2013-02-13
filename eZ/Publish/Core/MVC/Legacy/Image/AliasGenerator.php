<?php
/**
 * File containing the image AliasGenerator class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Image;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\Variation\VariationHandler;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\Values\ImageVariation;
use eZ\Publish\API\Repository\Exceptions\InvalidVariationException;
use eZContentObjectAttribute;
use eZImageAliasHandler;
use Closure;

class AliasGenerator implements VariationHandler
{
    /**
     * @var \Closure
     */
    private $kernelClosure;

    /**
     * @var \eZImageAliasHandler[]
     */
    private $aliasHandlers;

    /**
     * Image variant objects, indexed by <fieldId>-<versionNo>-<variantName>.
     * Storing them avoids to run the legacy kernel each time if there are similar images variations required.
     *
     * @var \eZ\Publish\SPI\Variation\Values\ImageVariation[]
     */
    private $variations;

    public function __construct( Closure $legacyKernelClosure )
    {
        $this->kernelClosure = $legacyKernelClosure;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        $kernelClosure = $this->kernelClosure;
        return $kernelClosure();
    }

    /**
     * Returns an image variant object.
     * Variation creation will be done through the legacy eZImageAliasHandler, using the legacy kernel.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $variationName
     * @param array $parameters
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidVariationException
     *
     * @return \eZ\Publish\SPI\Variation\Values\ImageVariation
     */
    public function getVariation( Field $field, VersionInfo $versionInfo, $variationName, array $parameters = array() )
    {
        $variationIdentifier = "$field->id-$versionInfo->versionNo-$variationName";
        if ( isset( $this->variations[$variationIdentifier] ) )
            return $this->variations[$variationIdentifier];

        // Assigning by reference to be able to modify those arrays within the closure (due to PHP 5.3 limitation with access to $this)
        $allAliasHandlers = &$this->aliasHandlers;
        $allVariations = &$this->variations;

        return $this->getLegacyKernel()->runCallback(
            function () use ( $field, $versionInfo, $variationName, &$allAliasHandlers, &$allVariations, $variationIdentifier )
            {
                $aliasHandlerIdentifier = "$field->id-$versionInfo->versionNo";
                if ( !isset( $allAliasHandlers[$aliasHandlerIdentifier] ) )
                {
                    $allAliasHandlers[$aliasHandlerIdentifier] = new eZImageAliasHandler(
                        eZContentObjectAttribute::fetch( $field->id, $versionInfo->versionNo )
                    );
                }

                /** @var $imageAliasHandler \eZImageAliasHandler */
                $imageAliasHandler = $allAliasHandlers[$aliasHandlerIdentifier];
                $aliasArray = $imageAliasHandler->imageAlias( $variationName );
                if ( $aliasArray === null )
                    throw new InvalidVariationException( $variationName, 'image' );

                $allVariations[$variationIdentifier] = new ImageVariation(
                    array(
                        'name'         => $variationName,
                        'fileName'     => $aliasArray['filename'],
                        'dirPath'      => $aliasArray['dirpath'],
                        'fileSize'     => isset( $aliasArray['filesize'] ) ? $aliasArray['filesize'] : 0,
                        'mimeType'     => $aliasArray['mime_type'],
                        'lastModified' => new \DateTime( '@' . $aliasArray['timestamp'] ),
                        'uri'          => $aliasArray['url'],
                        'width'        => $aliasArray['width'],
                        'height'       => $aliasArray['height'],
                    )
                );

                return $allVariations[$variationIdentifier];
            },
            false
        );
    }
}
