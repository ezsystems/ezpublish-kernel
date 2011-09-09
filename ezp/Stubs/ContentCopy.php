<?php
use ezp\Base\Service\Container;

$sc = new Container();
$repository = $sc->getRepository();
$contentService = $repository->getContentService();
$locationService = $repository->getLocationService();

$contentId = 60;
$parentLocationId = 2;

try
{
    $content = $contentService->load( $contentId );
    // Copies all versions
    $copy = $contentService->copy( $content );
    // Copies only version 3
    $copyInVersion3 = $contentService->copy( $content, $content->versions[3] );

    $parentLocation = $locationService->load( $parentLocationId );
    $location = $copy->addParent( $parentLocation );
    $location = $locationService->create( $location );
}
catch ( ezp\Base\Exception\Forbidden $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}
?>
