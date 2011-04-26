<?php
namespace ezp\Content\Persistence\API;

interface ContentRelationHandlerInterface extends BaseContentInterface
{
    /**
     * Extracts relations from content
     * @param BaseContentHandler $content
     * @return array( BaseContentRelation )
     */
    public function extractRelations( BaseContentHandler $content );
}