<?php
use ezp\Content\Repository as ContentRepository;

$contentService = ContentRepository::get()->getContentService();
$c = $contentService->createCriteria();
$c->subtree( $parentLocation )
     ->where( $c->field["folder/name"]->eq( "My folder name" ) )
     ->limit( 5 )
     ->offset( 0 );

$collection = $contentService->find( $c );

?>