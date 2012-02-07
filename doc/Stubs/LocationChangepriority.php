<?php
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$locationService = $sc->getRepository()->getLocationService();

$locationId = 60;

try
{
    $location = $locationService->load( $locationId );
    $location->priority = 20;
    $locationService->update( $location );
}
catch ( ezp\Content\ValidationException $e )
{
    echo "An error occurred while updating the location: {$e->getMessage()}";
    exit;
}
