<?php
/**
 * Load a Content based on its Id (60)
 * Assume that this content is a folder
 */
use ezp\Content\Repository as ContentRepository;
use ezp\Content\Fields;

$repository = ContentRepository::get();
$contentService = $repository->getContentService();
$locationService = $repository->getLocationService();
try
{
    $content = $contentService->load( 60 );
}
catch ( ezp\Content\ContentNotFoundException $e )
{
    echo "Content could not be found in the repository !\n";
    exit;
}

// Loop against fields.
// $identifier is the attribute identifier
// $value is the corresponding value object
echo "Content '{$content}' has following fields:\n";
foreach ( $content->fields as $identifier => $value )
{
    echo "Field '{$identifier}': {$value}\n"; // Using $value __toString() method
}

// Now updating content
$newParentLocation = $locationService->load( 43 ); // Fetch location with ID #43
$content->addLocationUnder( $newParentLocation );
$content->fields["name"] = new Fields\String( "New content name" );
$contentService->update( $content );

// Countable interface for content version collection
echo "There are now " . count( $content->versions ) . " versions for content";

// Free some memory
unset( $content );

?>
