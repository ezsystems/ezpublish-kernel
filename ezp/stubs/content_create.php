<?php
/*
 * Assume that :
 * - $parentLocation is a valid Location (formerly "parent node") => \ezp\Content\Location
 * - Current default locale is eng-GB.
 * - Locale fre-FR has also been set in the system
 */

use ezp\Content\Content;
use ezp\Content\ContentType;
use ezp\Content\Repository as ContentRepository;
use ezp\Content\Fields;

$content = new Content( ContentType::byIdentifier( "folder" ) );
/*
 * $content->fields have been set default value objects with default values set for content type
 * Setting a new value object to a field will unset the previous one (and avoid potential memory leaks)
 * Value object manipulation is totally up to the field type
 */
$content->fields['name'] = new Fields\String( "My folder name" );
$content->fields['description'] = new Fields\XMLText( "<p>This is the <strong>HTML description</strong></p>" );

// Now set an fre-FR translation
$content->addTranslation( "fre-FR" );
$content->translations["fre-FR"]->fields["name"] = new Fields\String( "Nom du dossier" );
$content->translations["fre-FR"]->fields["description"] = new Fields\XMLText( "<p>Ceci est la <strong>description HTML</strong></p>" );

// Get the content service from the repository and insert the new content
try
{
    $publishedContent = ContentRepository::get()->getContentService()->insert( $content, $parentLocation );
    echo "{$publishedContent}\n"; // Displays content "name" via __toString()
    echo "Content ID is: {$publishedContent->id}\n";
    echo "Content version number is: {$publishedContent->versionNumber}\n";
    // creation date is a DateTime object
    echo "Publication date is: {$publishedContent->creationDate->format( "Y-m-d H:i:s" )}\n";
}
catch ( ezp\Content\ValidationException $e )
{
    echo "Following validation issue occurred with your content : {$e->getMessage()}\n";
    exit;
}

?>