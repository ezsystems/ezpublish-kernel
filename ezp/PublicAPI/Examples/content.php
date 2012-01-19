<?php
use ezp\PublicAPI\Values\Content\LocationCreate;
use ezp\PublicAPI\Values\Content\ContentCreate;
use ezp\PublicAPI\Interfaces\ContentTypeService;
use ezp\PublicAPI\Interfaces\ContentService;
use ezp\PublicAPI\Interfaces\Repository;

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


// construct the creation structure with the content type and the main language of the content
// the main language is also the default language for the fields
$contentCreate = $contentService->newContentCreate($contentType,'eng-US');

// set title field in the main language
$contentCreate->setField('title','Title');
// set summary field in php array style
$contentCreate->fields['summary'] = "<p>this is a summary</p>";

// set authors field of the article
$authors = array();
$authors[] = new ezp\Content\FieldType\Author\Author('John Doe','john.doe@example.net');
$authors[] = new ezp\Content\FieldType\Author\Author('Bud Spencer','bud.spencer@example.net');
$contentCreate->setField('author',new ezp\Content\FieldType\Author\Value($authors));
// set image for the article
$contentCreate->setField('image',new ezp\Content\FieldType\Image\Value("/tmp/myimage.jpg","my alternative text"));
// set a remote id for the content
$contentCreate->remoteId = "12345";

// create the content instance with a default location create structure
$parentLocationId = 123;
$version = $contentService->createContentDraft($contentCreate, array($locationService->newLocationCreate($parentLocationId)));

// print the new created info data
$contentId = $version->contentId;
echo $contentId;
// 4.x. the location array is empty because the location are created on publish for the first time
$locations = $version->contentInfo->locations;

// publish the content
$contentService->publishDraft($version->versionInfo);

// now there is one location with parentId 123 in the returned array
$locations = $version->contentInfo->locations;

/**
 * translate the article
 */

// translating the content object (4.x)
// load the content info object (note this info object differes from the one in the draft after publishing)
$contentInfo = $contentService->loadContent($version->contentId);
// create a draft from the before published content
$versionInfo = $contentService->createDraftFromContent( $contentInfo );

// instantiate a version update value object
$versionUpdate = $contentService->newVersionUpdate();
$versionUpdate->initialLanguageId = 'ger-DE';
$versionUpdate->fields['title'] = 'Titel';
// .... as with creation see above

// update the draft
$version = $contentService->updateVersion($versionInfo,$versionUpdate);

// read the fields of the version

foreach ( $version->getFields() as $field) {
	echo "Field '{$field->identifier}','{$field->language}': {$field->value}\n";
}

/**
 * Update the content info object
 */

// Create the content update struct
$contentUpdate = $contentService->newContentUpdate();

// Change the main language and alwaysAvailableFlag
$contentUpdate->mainLanguageCode = 'ger-DE';
$contentUpdate->alwaysAvailbale = false;

// Update the content
$contentService->updateContent( $contentInfo, $contentUpdate );


/**
 * deleting the version and content
 */ 

// delete the version (draft)

$contentService->deleteVersion($version->versionInfo);

// delete the content object

$contentService->deleteContent($version->contentInfo);

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
$contentInfo = $contentService->loadContentByRemoteId( 'remote-id' );

// Load the version info instance
$versionInfo = $contentService->loadVersionInfo( $contentInfo );


/**
 * Load a specific version info for a content info object.
 */

// Load a content info instance
$contentInfo = $contentService->loadContent( 42 );

// Load a specific version
$versionInfo = $contentService->loadVersionInfo( $contentInfo, 3 );


/**
 * Load the latest version with all languages.
 */

// Load a content info instance
$contentInfo = $contentService->loadContent( 42 );

// Load the latest version for this content object
$version = $contentService->loadVersionByContentInfo( $contentInfo );

/**
 * Load the latest version with all languages (short form)
 */

// Load the latest version for this content object
$version = $contentService->loadVersion( 42 );


/**
 * Load the latest version in german and english
 */

$languages = array( 'eng-US', 'ger-DE' );

// Load the latest version for this content object
$version = $contentService->loadVersion( 42, $languages );

var_dump( $version->contentInfo->mainLanguage );


/**
 * Load a specific version in german
 */

$language = array( 'de' );

// Load this version
$version = $contentService->loadVersion( 42, $language, 17 );


/**
 * Remove all archived versions from a content object.
 */

// Load a content info for a specific content id
$contentInfo = $contentService->loadContent( 23 );

// List of content versions
$versionInfos = $contentService->loadVersions( $contentInfo );

foreach( $versionInfos as $versionInfo) {
	if($versionInfo->status == VersionInfo::STATUS_ARCHIVED) {
		$contentService->deleteVersion($versionInfo);
	}
}

/**
 * Copy content object only with latest version
 */

// load the latesst version
$versionInfo = $contentService->loadVersionInfoById(23);

// Instantiate a location create struct
$locationCreate = $locationService->newLocationCreate(123);

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
foreach ( $searchResult->items as $version )
{
    echo $version->getVersionInfo()->versionNo, PHP_EOL;
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
$contentInfo = $contentService->loadContentByRemoteId( 'remote' );

// Load the latest version info for the content object
$versionInfo = $contentService->loadVersionInfo( $contentInfo );

// Now load all outgoing relations
foreach ( $contentService->loadOutgoingRelations( $versionInfo ) as $relation )
{
    var_dump( $relation->type );
}


/**
 * Load all incoming relations
 */

// Load the used content info object
$contentInfo = $contentService->loadContent( 17 );

// Load the incoming relations for the content info object
foreach ( $contentService->loadIncomingRelations( $contentInfo ) as $relation )
{
    echo $relation->sourceContentId, ' --> ', $relation->destinationContentId, PHP_EOL;
}


/**
 * Remove a relation
 */

// Load the content info instance
$contentInfo = $contentService->loadContent( 42 );

// Load specific version info
$versionInfo = $contentService->loadVersionInfo( $contentInfo);

// Remove a relation
$contentService->deleteRelation( $version, 23 );

