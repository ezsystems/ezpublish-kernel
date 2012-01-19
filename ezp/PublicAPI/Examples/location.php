<?php
// browsing locations in the main language

use ezp\PublicAPI\Values\Content\Location;

/**
 * assumed as injected
 * @var ezp\PublicAPI\Interfaces\Repository $repository
 */
$repository = null;

$contentService = $repository->getContentService();
$locationService = $repository->getLocationService();

/**
 * Get the Home location ( $homeLocation is a int )
 */
$location = $locationService->loadLocation( $homeLocation );

// print out the computed name of the content object in the main language
echo  $location->contentInfo->name . '\n';

// get the 10 first childs in the sort settings of the home location
$childLocations = $locationService->getLocationChildren($location,0,10);

foreach($childLocations as $child) {
	// print a + if the child location has children
    if($child->childCount > 0) 
        echo "+ ";
    else 
        echo "  ";
    echo  $child->contentInfo->name . '\n';       
}

// browsing locations in a specific language
$location = $locationService->loadLocation( $homeLocation );

// load the published version
$publishedVersion = $contentService->loadVersionInfo( $location->contentInfo );

$otherLanguageCode = 'ger-GB';
echo $publishedVersion->names[$otherLanguageCode];

// get the 10 first childs in the sort settings of the home location
$childLocations = $locationService->loadLocationChildren( $location, 0, 10 );

foreach($childLocations as $child) {
	// print a + if the child location has children
    if($child->childCount > 0) 
        echo "+ ";
    else 
        echo "  ";
    $publishedChildVersion = $contentService->loadVersionInfo($child->contentInfo);
    echo $publishedChildVersion->names[$otherLanguageCode];
}

// create a new loaction for a content object

$contentInfo = $contentService->loadContent( $contentId );
$locationCreate = $locationService->newLocationCreate( $parentId );
$locationCreate->priority = 3;
$locationService->createLocation( $contentInfo, $locationCreate );



