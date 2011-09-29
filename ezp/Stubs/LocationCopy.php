<?php

use ezp\Base\ServiceContainer as Container;

$sc = new Container();
$locationService = $sc->getRepository()->getLocationService();

$locationId = 60;
$targetLocationId = 40;

try
{
    $location = $locationService->load( $locationId );
    $target = $locationService->load( $targetLocationId );
    $copy = $locationService->copySubtree( $location, $target );
}
catch ( ezp\Base\Exception\Forbidden $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}

?>
