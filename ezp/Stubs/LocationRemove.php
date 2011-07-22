<?php

use ezp\Base\ServiceContainer;

$sc = new ServiceContainer();
$locationService = $sc->getRepository()->getLocationService();

$locationId = 60;

try
{
    $location = $locationService->load( $locationId );
    $locationService->delete( $location );
}
catch ( ezp\Base\Exception\Forbidden $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}

?>
