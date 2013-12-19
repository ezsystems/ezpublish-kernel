<?php

namespace eZ\Publish\SPI\Asset;

interface Handler
{
    /**
     * Generates the (default) variants for $inputFile
     *
     * @param string $inputFile
     * @param string $typeHint
     * @return Variant[] The generated variants
     */
    public function generateVariants( $inputFile, $typeHint = null );

    /**
     * Generates the variant with $variantIdentifier for $inputFile
     *
     * @param string $inputFile
     * @param string $variantIdentifier
     * @param string $typeHint
     * @return Variant The generated variant data
     */
    public function generateVariant( $inputFile, $variantIdentifier, $typeHint = null );
}
