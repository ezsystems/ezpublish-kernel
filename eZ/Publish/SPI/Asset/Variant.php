<?php

namespace eZ\Publish\SPI\Asset;

class Variant
{
    /**
     * The URI where the variant is stored
     *
     * @var string
     */
    public $storageUri;

    /**
     * The web accessible URI of the variant
     *
     * @var string
     */
    public $webUri;

    /**
     * Map of meta data for this variant
     *
     * @var array
     */
    public $metaData;
}
