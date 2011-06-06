<?php
/**
 * Load a Content based on its Id (60)
 * Assume that this content is a folder
 */
use ezp\Content\Repository as ContentRepository;

$repository = ContentRepository::get();
try
{
    $content = $repository->loadContent( 60 );
}
catch ( ezp\Content\ContentNotFoundException $e )
{
    echo "Content could not be found in the repository !";
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
$content->fields["name"] = new ezp\Content\Fields\String( "New content name" );
$repository->getContentService()->update( $content );

?>