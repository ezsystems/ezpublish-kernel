<?php

use ezp\Base\ServiceContainer;

$sc = new ServiceContainer();
$locationService = $sc->getRepository()->getLocationService();

$locationId = 60;
$targetLocationId = 40;

try
{
    $location = $locationService->load( $locationId );
    $target = $locationService->load( $targetLocationId );
    $locationService->copy( $location, $target );
}
catch ( ezp\Base\Exception\Forbidden $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}

?>
