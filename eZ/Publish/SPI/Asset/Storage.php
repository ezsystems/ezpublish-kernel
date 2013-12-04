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
     * Store a file from $localPath and return the assigned, private URI
     *
     * @param string $localPath
     * @return string Storage URI
     */
    public function storeFromLocal( $localPath );

    /**
     * Updates the given $storageUri using $blob
     *
     * @param string $storageUri
     * @param string $blob
     */
    public function update( $storageUri, $blob );

    /**
     * Updates the given $storageUri from the $localPath
     *
     * @param string $storageUri
     * @param string $localPath
     */
    public function updateFromLocal( $storageUri, $localPath );

    /**
     * Load the blob stored at $uri
     *
     * @param string $storageUri
     * @return string The loaded blob
     */
    public function load( $storageUri );

    /**
     * Stores the blob found in $storageUri in $localPath
     *
     * @param string $storageUri
     * @param string $localPath
     */
    public function loadToLocal( $storageUri, $localPath );

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
