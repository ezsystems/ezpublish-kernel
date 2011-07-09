<?php
use ezp\Base\Repository;

$contentService = Repository::get()->getContentService();
$c = $contentService->createCriteria();
$c->where( // andCondition() is implicit
    $c->location->isChildOf( $parentLocation ), // Direct children
    // $c->location->subTree( $parentLocation ), // Recursive
    $c->type( "folder" ),
    $c->field->eq( "show_children", true ),
    $c->meta->gte( "published", new DateTime( "yesterday" ) ),
    $c->logic->orCondition(
        $c->field->eq( "name", "My folder name" ),
        $c->field->eq( "name", "Another name" )
    )
)
->limit( 5 )
->offset( 0 )
->sortBy(
    new SortByMetaClause( 'published' ),
    new SortByFieldClause( 'title' )
);

$collection = $contentService->find( $c );
?>
