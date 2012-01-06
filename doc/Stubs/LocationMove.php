<?php
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$locationService = $sc->getRepository()->getLocationService();

$newParentLocationId = 40;
$locationId = 60;

try
{
    $newParentLocation = $locationService->load( $newParentLocationId );
    $location = $locationService->load( $locationId );
    $locationService->move( $location, $newParentLocation );
}
catch ( ezp\Base\Exception\Forbidden $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}
?>
