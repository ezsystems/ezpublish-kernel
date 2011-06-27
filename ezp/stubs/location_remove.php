<?php

use ezp\Content\Repository as ContentRepository;

$locationId = 60;
$locationService = ContentRepository::get()->getLocationService();

try
{
    $location = $locationService->load( $locationId );
    $locationService->delete( $location );
}
catch ( ezp\Content\PermissionException $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}



?>
