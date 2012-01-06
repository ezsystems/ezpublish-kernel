<?php

use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
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
