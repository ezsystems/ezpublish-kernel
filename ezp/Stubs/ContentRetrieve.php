<?php
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$contentService = $sc->getRepository()->getContentService();

$c = $contentService->createCriteria();
$c
    ->where(
        // andCondition() is implicit
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

$c = new CompoundCriterion(
    LogicOperator::L_AND,
    new LocationListCriterion( $parentLocation ),
    new ContentTypeCriterion( 'folder' ),
    new FieldCriterion( Operator::EQ, 'show_children', true ),
    new MetaDataCriterion( Operator::GT, 'published', new DateTime( 'yesterday' ) ),
    new CompoundCriterion(
        LogicOperator::L_OR,
        new FieldCriterion( LogicOperator::EQ, 'name', 'My folder name' ),
        new FieldCriterion( LogicOperator::EQ, 'name', 'Another name' )
    )
);
?>
