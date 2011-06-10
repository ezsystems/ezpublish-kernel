<?php
use ezp\Content\Repository as ContentRepository;

$newParentLocationId = 40;
$locationId = 60;
$treeService = ContentRepository::get()->getSubtreeService();

try
{
    $newParentLocation = $treeService->load( $newParentLocationId );
    $location = $treeService->load( $locationId );
    $treeService->move( $location, $newParentLocation );
}
catch ( ezp\Content\PermissionException $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}
?>
