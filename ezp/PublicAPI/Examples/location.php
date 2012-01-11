<?php
// browsing locations in the main language

use ezp\PublicAPI\Values\Content\Location;
$location = $locationService->loadLocation($HOME_LOCATION);
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
$location = $locationService->load($HOME_LOCATION);
// load the published version
$publishedVersion = $contentService->loadVersionInfo($location->contentInfo);
echo $publishedVersion->names[$otherLanguageCode];

// get the 10 first childs in the sort settings of the home location
$childLocations = $locationService->getLocationChildren($location,0,10);

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

$contentInfo = $contentService->loadContent($CONTENT_ID);
$locationCreate = $loactionService->newLocationCreate($PARENT_ID);
$locationCreate->priority=3;
$locationService->create($contentInfo,$locationCreate);



