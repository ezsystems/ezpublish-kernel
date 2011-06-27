<?php

use ezp\Content\Repository as ContentRepository;

$locationId = 60;
$targetLocationId = 40;
$locationService = ContentRepository::get()->getLocationService();

try
{
    $location = $locationService->load( $locationId );
    $target = $locationService->load( $targetLocationId );
    $locationService->copy( $location, $target );
}
catch ( ezp\Content\PermissionException $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}



?>
