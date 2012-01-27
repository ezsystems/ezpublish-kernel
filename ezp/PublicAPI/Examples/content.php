<?php
use ezp\PublicAPI\Values\Content\VersionInfo,
    ezp\PublicAPI\Interfaces\Repository;

/**
 * assumed as injected
 * @var ezp\PublicAPI\Interfaces\Repository $repository
 */
$repository = null;

$contentService = $repository->getContentService();
$locationService = $repository->getLocationService();
$contentTypeService = $repository->getContentTypeService();

/**
 * create a new instance of a content object of type article
 */

$contentType = $contentTypeService->loadContentTypeByIdentifier( 'article' );
// $contentType is read only output value

// construct the creation structure with the content type and the main language of the content
// the main language is also the default language for the fields
$contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-US' );

// set title field in the main language
$contentCreateStruct->setField( 'title', 'Title' );

// set summary field in php array style
$contentCreateStruct->fields['summary'] = "<p>this is a summary</p>";

// set authors field of the article
$contentCreateStruct->setField(
    'author',
    new \ezp\Content\FieldType\Author\Value(
        array(
            new \ezp\Content\FieldType\Author\Author( 'John Doe', 'john.doe@example.net' ),
            new \ezp\Content\FieldType\Author\Author( 'Bud Spencer', 'bud.spencer@example.net' ),
        )
    )
);

// set image for the article
$contentCreateStruct->setField( 'image', new \ezp\Content\FieldType\Image\Value( "/tmp/myimage.jpg","my alternative text" ) );

// set a remote id for the content
$contentCreateStruct->remoteId = "12345";

// create the content instance with a default location create structure
$parentLocationId = 123;


// demonstrating transactions 

$repository->beginTransaction();
try {
    $content = $contentService->createContent( $contentCreateStruct, array( $locationService->newLocationCreateStruct( $parentLocationId ) ) );
    // print the new created info data
    $contentId = $content->contentId;
    echo $contentId;

    // 4.x. the location array is empty because the location are created on publish for the first time
    // this method will throw an exception
    try
    {
        $locations = $locationService->getLocations( $content->contentInfo );
    }
    catch ( BadStateException $e )
    {
        echo "yes this content object has no location by now";
    }
    // publish the content
    $contentService->publishContent( $content->versionInfo );
    
    // now there is one location with parentId 123 in the returned array
    $locations = $locationService->getLocations( $content->contentInfo );
    
    // print the fields
    foreach ( $content->getFields() as $field )
    {
        echo "Field '{$field->identifier}','{$field->language}': {$field->value}\n";
    }
    $repository->commit();
    
}
catch(UnauthorizedException $e) {
    echo "permission denied\n" . $e.getMessage();
    $repository->rollback();
}
catch(IllegalArgumentException $e) {
    echo "you did something wrong in the create struct: " . $e.getMessage() ."\n";
    $repository->rollback();
}
catch(RuntimeException $e) {
    echo "something serious went wrong" . $e->getMessage();
    $repository->rollback();
}



/**
 * translate the article
 */

// translating the content object (4.x)
// load the content info object (note this info object differes from the one in the draft after publishing)
$contentInfo = $contentService->loadContentInfo( $content->contentId );

// create a draft from the before published content
$content = $contentService->createDraftFromContent( $contentInfo );

// instantiate a version update value object
$contentUpdate = $contentService->newVersionUpdateStruct();
$contentUpdate->initialLanguageId = 'ger-DE';
$contentUpdate->fields['title'] = 'Titel';
// .... as with creation see above

// update the draft
$content = $contentService->updateContent( $versionInfo, $contentUpdate );

// read the fields of the version

foreach ( $content->getFields() as $field )
{
    echo "Field '{$field->identifier}','{$field->language}': {$field->value}\n";
}

/**
 * Update the content meta data
 */

// Create the content update struct
$contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();

// Change the main language and alwaysAvailableFlag
$contentMetadataUpdateStruct->mainLanguageCode = 'ger-DE';
$contentMetadataUpdateStruct->alwaysAvailable = false;

// Update the content
$contentService->updateContentMetadata( $contentInfo, $contentMetadataUpdateStruct );


/**
 * deleting the version and content
 */

// delete the version (draft)
$contentService->deleteVersion( $content->versionInfo );

// delete the content object
$contentService->deleteContent( $content->contentInfo );

/**
 * Load all drafts of the current user
 */

foreach ( $contentService->loadContentDrafts() as $versionInfo )
{
    foreach ( $versionInfo->names as $language => $name )
    {
        echo 'In language "', $language, '" is name "', $name, '"', PHP_EOL;
    }
}

/**
 * Load the latest version info for a given remote id
 */

// Load the content info by it's remote id
$contentInfo = $contentService->loadContentInfoByRemoteId( 'remote-id' );

// Load the version info instance
$versionInfo = $contentService->loadVersionInfo( $contentInfo );


/**
 * Load a specific version info for a content info object.
 */

// Load a content instance
$content = $contentService->loadContent( 42 );

// Load a specific version
$versionInfo = $contentService->loadVersionInfo( $content, 3 );


/**
 * Load the latest version with all languages.
 */

// Load a content instance
$content = $contentService->loadContent( 42 );



/**
 * Load the latest version in german and english
 */

$languages = array( 'eng-US', 'ger-DE' );

// Load the latest version for this content object
$content = $contentService->loadContent( 42, $languages );

var_dump( $content->contentInfo->mainLanguageCode );


/**
 * Load a specific version in german
 */

$language = array( 'ger-DE' );

// Load this version
$content = $contentService->loadContent( 42, $language, 17 );


/**
 * Remove all archived versions from a content object.
 */

// Load a content info for a specific content id
$contentInfo = $contentService->loadContentInfo( 23 );

// List of content versions
$versionInfos = $contentService->loadVersions( $contentInfo );
foreach ( $versionInfos as $versionInfo )
{
    if ( $versionInfo->status == VersionInfo::STATUS_ARCHIVED )
    {
       $contentService->deleteVersion( $versionInfo );
    }
}

/**
 * Copy content object only with latest version
 */

// load the latest version
$versionInfo = $contentService->loadVersionInfoById( 23 );

// Instantiate a location create struct
$locationCreate = $locationService->newLocationCreateStruct( 123 );

// Copy content in latest version
$version = $contentService->copyContent( $versionInfo->contentInfo, $locationCreate, $versionInfo );


/**
 * Find a set of matching versions
 */

// Create a simple search query
$query = new \ezp\PublicAPI\Values\Content\Query();
$query->criterion = new \ezp\PublicAPI\Values\Content\Query\Criterion\LogicalAnd(
    array(
        new \ezp\PublicAPI\Values\Content\Query\Criterion\Field( 'title', \ezp\PublicAPI\Values\Content\Query\Criterion\Operator::LIKE, '*foo*' ),
        new \ezp\PublicAPI\Values\Content\Query\Criterion\Field( 'abstract', \ezp\PublicAPI\Values\Content\Query\Criterion\Operator::LIKE, '*foo*' )
    )
);

// Search for matching versions
$searchResult = $contentService->findContent( $query, array() );

// Dump version number for all found versions
foreach ( $searchResult->items as $content )
{
    echo $content->getVersionInfo()->versionNo, PHP_EOL;
}

/**
 * Add a relation
 */

// Load specific version info
$versionInfo = $contentService->loadVersionInfoById( 42 );

// Add a relation
$contentService->addRelation( $versionInfo, 23 );


/**
 * Load all outgoing relations
 */

// Load the content info instance
$contentInfo = $contentService->loadContentInfoByRemoteId( 'remote' );

// Load the latest version info for the content object
$versionInfo = $contentService->loadVersionInfo( $contentInfo );

// Now load all outgoing relations
foreach ( $contentService->loadRelations( $versionInfo ) as $relation )
{
    var_dump( $relation->type );
}


/**
 * Load all incoming relations
 */

// Load the used content info object
$contentInfo = $contentService->loadContentInfo( 17 );

// Load the incoming relations for the content info object
foreach ( $contentService->loadReverseRelations( $content ) as $relation )
{
    echo $relation->sourceContentId, ' --> ', $relation->destinationContentId, PHP_EOL;
}


/**
 * Remove a relation
 */

// Load the content info instance
$contentInfo = $contentService->loadContentInfo( 42 );

// Load specific version info
$versionInfo = $contentService->loadVersionInfo( $contentInfo );

// Remove a relation
$contentService->deleteRelation( $versionInfo, 23 );
