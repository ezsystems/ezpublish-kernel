<?php

namespace eZ\Publish\SPI\Asset;

interface Handler
{
    /**
     * Applies $variantDefinition to $inputFile
     *
     * @param string $inputFile
     * @return string The generated variant file
     */
    public function createVariant($inputFile, VariantDefinition $variantDefinition);
}
