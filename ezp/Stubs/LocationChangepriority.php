<?php
use ezp\Content\Repository as ContentRepository;

$locationId = 60;
$locationService = ContentRepository::get()->getLocationService();

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
