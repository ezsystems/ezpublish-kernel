<?php
use ezp\Content\Repository as ContentRepository;

$location1Id = 60;
$location2Id = 40;

$treeService = ContentRepository::get()->getSubtreeService();
try
{
    $location1 = $treeService->load( $location1Id );
    $location2 = $treeService->load( $location2Id );

    ContentRepository::get()->begin();
    $treeService->swap( $location1, $location2 );
    ContentRepository::get()->commit();
}
catch ( ezp\Content\PermissionException $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}
?>
