<?php
namespace ezp\Content\Persistence\Storage\Legacy;
use ezp\Content\Persistence\API as Persistence;

class ContentRelationHandler implements Persistence\ContentRelationHandlerInterface
{
    public function __construct()
    {

    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentRelationHandlerInterface::extractRelations()
     */
    public function extractRelations( Persistence\BaseContent $content )
    {
        $aRelations = $content->contentObject->relatedContentObjectList();
        $aHydratedRelations = array();
        if ( !empty( $aRelations ) )
        {
            foreach ( $aRelations as $relation )
            {
                $contentRelation = new ContentRelation( $content );
                $contentRelation->hydrate( $relation );
                $aHydratedRelations[] = $contentRelation;
            }
        }

        return $aHydratedRelations;
    }
}
