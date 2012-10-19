<?php
/**
 * File containing the image AliasGenerator class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\FieldType\Image;

use eZ\Publish\Core\MVC\ConfigResolverInterface,
    eZ\Publish\SPI\VariantService,
    eZ\Publish\API\Repository\Values\Content\Field,
    eZ\Publish\API\Repository\Values\Content\VersionInfo,
    eZ\Publish\API\Repository\Values\File\ImageVariant,
    eZ\Publish\API\Repository\Exceptions\InvalidVariantException,
    eZContentObjectAttribute,
    eZImageAliasHandler;

class AliasGenerator implements VariantService
{
    /**
     * @var \Closure
     */
    private $kernelClosure;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZImageAliasHandler[]
     */
    private $aliasHandlers;

    /**
     * Image variant objects, indexed by <fieldId>-<versionNo>-<variantName>.
     * Storing them avoids to run the legacy kernel each time if there are similar images variations required.
     *
     * @var \eZ\Publish\API\Repository\Values\File\ImageVariant[]
     */
    private $variants;

    public function __construct( \Closure $legacyKernelClosure, ConfigResolverInterface $configResolver )
    {
        $this->kernelClosure = $legacyKernelClosure;
        $this->configResolver = $configResolver;
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
     * Variant creation will be done through the legacy eZImageAliasHandler, using the legacy kernel.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $variantName
     * @return \eZ\Publish\API\Repository\Values\File\ImageVariant
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidVariantException
     */
    public function getVariant( Field $field, VersionInfo $versionInfo, $variantName )
    {
        $variantIdentifier = "$field->id-$versionInfo->versionNo-$variantName";
        if ( isset( $this->variants[$variantIdentifier] ) )
            return $this->variants[$variantIdentifier];

        $configResolver = $this->configResolver;
        // Assigning by reference to be able to modify those arrays within the closure (due to PHP 5.3 limitation with access to $this)
        $allAliasHandlers = &$this->aliasHandlers;
        $allVariants = &$this->variants;

        return $this->getLegacyKernel()->runCallback(
            function () use ( $field, $versionInfo, $variantName, $configResolver, $allAliasHandlers, $allVariants, $variantIdentifier )
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
                $aliasArray = $imageAliasHandler->imageAlias( $variantName );
                if ( $aliasArray === null )
                    throw new InvalidVariantException( $variantName, 'image' );

                $allVariants[$variantIdentifier] = new ImageVariant(
                    array(
                         'name'         => $variantName,
                         'fileName'     => $aliasArray['filename'],
                         'dirPath'      => $aliasArray['dirpath'],
                         'fileSize'     => $aliasArray['filesize'],
                         'mimeType'     => $aliasArray['mime_type'],
                         'lastModified' => new \DateTime( '@' . $aliasArray['timestamp'] ),
                         'uri'          => $aliasArray['url'],
                         'width'        => $aliasArray['width'],
                         'height'       => $aliasArray['height'],
                    )
                );

                return $allVariants[$variantIdentifier];
            },
            false
        );
    }
}
