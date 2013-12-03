<?php

namespace eZ\Publish\SPI\Asset;

class FilterDefinition
{
    /**
     * Filter name
     *
     * @var string
     */
    public $identifier;

    /**
     * Map of parameters for the filter
     *
     * @var array
     */
    public $parameters;
}
