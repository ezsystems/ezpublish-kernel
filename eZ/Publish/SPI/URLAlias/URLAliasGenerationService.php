<?php

namespace eZ\Publish\SPI\URLAlias;

/**
 * In implementation of this service triggers generation of URL aliases.
 */
interface URLAliasGenerationService
{
    /**
     * Triggers generation of URL aliases when a new content version is
     * published.
     *
     * @param string $contentId
     * @param int $versionNo
     */
    public function onContentVersionPublished( $contentId, $versionNo );

    /**
     * Triggers removal of URL aliases when a content is deleted.
     *
     * @param string $contentId
     */
    public function onContentDeleted( $contentId );

    /**
     * Triggers update of URL aliases when a location was moved.
     *
     * @param string $locationId
     * @param string $oldParentId
     * @param string $newParentId
     */
    public function onLocationMoved( $locationId, $oldParentId, $newParentId );

    /**
     * Triggers creation of URL aliases when a location was copied.
     *
     * @param string $copiedLocationId
     */
    public function onLocationCopied( $originalLocationId, $copiedLocationId );

    /**
     * Triggers removal of URL aliases when a location was deleted.
     *
     * @param string $deletedLocationId
     */
    public function onLocationDeleted( $deletedLocationId );
}
