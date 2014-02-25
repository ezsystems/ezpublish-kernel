<?php

namespace eZ\Publish\SPI\Asset;

interface Handler
{
    /**
     * determine asset type for $inputFile
     *
     * TODO may be part of a separate handler
     *
     * @param string $inputFile
     * @param string $typeHint
     * @return \eZ\Publish\SPI\Asset\AssetType
     */
    public function getAssetType( $inputFile, $typeHint = null );

    /**
     * Generates the default variants for $inputFile
     *
     * generates all variants which have the always available flag set in the variant definition
     *
     * @param string $inputFile
     * @param string $typeHint
     * @return \eZ\Publish\SPI\Asset\Variant[] The generated variants
     */
    public function generateVariants( $inputFile, AssetType $assetType );

    /**
     * Generates the variant with $variantIdentifier for $inputFile
     *
     * @param string $inputFile
     * @param \eZ\Publish\SPI\Asset\AssetType $assetType
     * @param string $variantIdentifier
     * @return \eZ\Publish\SPI\Asset\Variant The generated variant data
     */
    public function generateVariant( $inputFile, AssetType $assetType, $variantIdentifier );
}
