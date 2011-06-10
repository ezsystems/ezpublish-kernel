<?php
use ezp\Content\Repository as ContentRepository;

$locationId = 60;
$treeService = ContentRepository::get()->getSubtreeService();

try
{
    $location = $treeService->load( $locationId );
    $location->priority = 20;
    $treeService->update( $location );
}
catch ( ezp\Content\PermissionException $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}
?>
