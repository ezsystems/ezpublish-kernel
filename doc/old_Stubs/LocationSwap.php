<?php
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$locationService = $sc->getRepository()->getLocationService();

$location1Id = 60;
$location2Id = 40;

try
{
    $location1 = $locationService->load( $location1Id );
    $location2 = $locationService->load( $location2Id );

    $locationService->swap( $location1, $location2 );
}
catch (ezp\Base\Exception\Forbidden $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}
