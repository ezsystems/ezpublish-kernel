<?php
use ezp\Base\ServiceContainer;

$sc = new ServiceContainer();
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
?>
