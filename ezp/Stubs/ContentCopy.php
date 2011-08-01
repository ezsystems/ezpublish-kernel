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

    /*
     * Duplicates content with native clone command
     * __clone() method will be called to reset appropriate metadata
     * like ID, creationDate, owner, ...
     */
    $newContent = clone $content;
    $parentLocation = $locationService->load( $parentLocationId );
    $newContent->addParent( $parentLocation );
    $contentService->create( $newContent );
}
catch ( ezp\Base\Exception\Forbidden $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}
?>
