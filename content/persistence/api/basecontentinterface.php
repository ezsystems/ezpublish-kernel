<?php
namespace ezp\Content\Persistence\API;

interface BaseContentInterface
{
    /**
     * Hydrates current object with $storageObject data
     * $storageObject directly comes from storage engine.
     * Example with legacy implementation :
     * <code>
     * use ezp\Content\Persistence\Storage\Legacy\Content;
     *
     * $legacyContentObject = \eZContentObject::fetch( $objId );
     * $content = new Content();
     * $content->hydrate( $legacyContentObject );
     * </code>
     * @param object $storageObject Data Object coming from Storage engine
     * @return object Created object, related to handler type
     */
    public function hydrate( $storageObject );
}
?>
