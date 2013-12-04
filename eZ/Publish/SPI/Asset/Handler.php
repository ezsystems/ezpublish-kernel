<?php

namespace eZ\Publish\SPI\Asset;

interface Handler
{
    /**
     * Generates the (default) variants for $inputFile
     *
     * @param string $inputFile
     * @return Variant[] The generated variants
     */
    public function generateVariants($inputFile);

    /**
     * Generates the variant with $variantIdentifier for $inputFile
     *
     * @param string $inputFile
     * @param string $variantIdentifier
     * @return Variant The generated variant data
     */
    public function generateVariant($inputFile, $variantIdentifier);
}
