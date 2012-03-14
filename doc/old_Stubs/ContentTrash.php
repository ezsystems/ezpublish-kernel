<?php
/**
 * Move a content to trash
 */
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$locationService = $sc->getRepository()->getLocationService();
$trashService = $sc->getRepository()->getTrashService();
$location = $locationService->load( 60 );

echo "Now Trashing subtree\n";
// TrashService::trash() returns a TrashedLocation object, based on original Location object
$trashedLocation = $trashService->trash( $location );
echo "Now location and associated content is no longer publicly available, so as for location children\n";

echo "Restoring location from trash under original parent\n";
$restoredLocation = $trashService->untrash( $trashedLocation );
// Possible to restore under another location :
// $restoredLocation = $trashService->untrash( $trashedLocation, $newParentLocation );

echo "Listing elements in the trash\n";
$qb = new ezp\Content\Query\Builder();
$qb->addCriteria(
    $qb->contentTypeId->eq( 'blog_post' ),
    $qb->field->eq( 'author', 'community@ez.no' )
)->addSortClause(
    $qb->sort->dateCreated( Query::SORT_DESC )
)->setOffset( 0 )->setLimit( 15 );
$trashList = $trashService->getList( $qb->getQuery() );

echo "Emptying trash\n";
$trashService->emptyTrash();
// Possible to remove only one element in the trash:
// $trashService->emptyOne( $trashedLocation );
