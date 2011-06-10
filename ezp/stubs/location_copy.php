<?php

use ezp\Content\Repository as ContentRepository;

$locationId = 60;
$targetLocationId = 40;
$treeService = ContentRepository::get()->getSubtreeService();

try
{
    $location = $treeService->load( $locationId );
    $target = $treeService->load( $targetLocationId );
    $treeService->copy( $location, $target );
}
catch ( ezp\Content\PermissionException $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}



?>
