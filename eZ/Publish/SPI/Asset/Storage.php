<?php

namespace eZ\Publish\SPI\Asset;

interface Storage
{
    /**
     * Stores $blob as a new item and returns the assigned, private URI
     *
     * @param string $blob
     * @return string Storage URI
     */
    public function store( $blob );

    /**
     * Load the blob stored at $uri
     *
     * @param string $storageUri
     * @return string The loaded blob
     */
    public function load( $storageUri );

    /**
     * Returns the unique scheme associated to this storage.
     *
     * @return string
     */
    public function getScheme();

    /**
     * Returns if the storage can handle $storageUri.
     *
     * @param string $storageUri
     * @return bool
     */
    public function canHandle( $storageUri );

    /**
     * Returns the web URI for the given $storageUri
     *
     * @param string $storageUri
     * @return string
     */
    public function getWebUri( $storageUri );
}
