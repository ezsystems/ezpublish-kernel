<?php
/**
 * Example: moving an existing content to a new location
 *
 * @package Examples
 */

$contentId = 50; // Id of the content we'll be moving
$targetParentFolderId = 100; // Id of the location we'll be moving it to

/**
 * @var ezp\PublicAPI\Interfaces\Repository
 */
$repository = null;

$contentService = $repository->getContentService();
$locationService = $repository->getLocationService();

$content = $contentService->loadContentInfo( $contentId );
$location = $locationService->loadMainLocation( $content );
$targetLocation = $locationService->loadLocation( $targetParentFolderId );

// move $content's $location to $targetLocation
try {
    $targetLocationService = $locationService->moveSubtree( $location, $targetLocation );
} catch( Exception $e ) {
    // error handling
}

?>