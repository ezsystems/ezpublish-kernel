<?php
use ezp\Content\Repository as ContentRepository;

$newParentLocationId = 40;
$locationId = 60;
$locationService = ContentRepository::get()->getLocationService();

try
{
    $newParentLocation = $locationService->load( $newParentLocationId );
    $location = $locationService->load( $locationId );
    $locationService->move( $location, $newParentLocation );
}
catch ( ezp\Content\PermissionException $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}
?>
