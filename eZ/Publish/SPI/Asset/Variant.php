<?php

namespace eZ\Publish\SPI\Asset;

class Variant
{
    /**
     * The local file generated as the variant.
     *
     * @var string
     */
    public $outputFile;

    /**
     * Map of meta data for this variant
     *
     * @var array
     */
    public $metaData;
}
