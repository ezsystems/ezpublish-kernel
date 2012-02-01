<?php
/**
 * Example: moving an existing content to a new location
 *
 * @package Examples
 */

$contentId = 50; // Id of the content we'll be moving
$targetParentFolderId = 100; // Id of the location we'll be moving it to

/**
 * @var eZ\Publish\API\Repository\Repository
 */
$repository = null;

$contentService = $repository->getContentService();
$locationService = $repository->getLocationService();

$content = $contentService->loadContentInfo( $contentId );
$location = $locationService->loadMainLocation( $content );
$targetParentLocation = $locationService->loadLocation( $targetParentFolderId );

// move $content's $location to $targetLocation
try
{
    $locationService->moveSubtree( $location, $targetParentLocation );
}
catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
{
    $currentUser = $repository->getCurrentUser();
    echo "Current user " . $currentUser->login .
        " isn't allowed to move content " . $content->name .
        " to " . $targetParentLocation->pathString;
}

echo "Content " . $content->name .
    " moved to " . $targetParentLocation->pathString;