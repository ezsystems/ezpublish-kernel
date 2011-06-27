<?php
use ezp\Content\Repository as ContentRepository;

$location1Id = 60;
$location2Id = 40;

$locationService = ContentRepository::get()->getLocationService();
try
{
    $location1 = $locationService->load( $location1Id );
    $location2 = $locationService->load( $location2Id );

    ContentRepository::get()->begin();
    $locationService->swap( $location1, $location2 );
    ContentRepository::get()->commit();
}
catch ( ezp\Content\PermissionException $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}
?>
