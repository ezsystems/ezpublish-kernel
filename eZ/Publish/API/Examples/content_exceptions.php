<?php
/**
 * This code example illustrates the issue with very generic exceptions.
 *
 * It tests the following scenario:
 * - create an article
 * - set the title to a string that is too long for the max length constraint set for the 'title' field
 * - catch and interpret the validation exception
 *
 * This is done in 3 ways:
 * 1. The current way, with a global exception upon contentCreate()
 * 2. Using exceptions thrown when setting the value on the struct
 * 3. Using a more specialized  exception thrown when contentCreate() is called
 */

use ezp\PublicAPI\Interfaces\Repository;

/**
 * Assumed as injected
 * @var ezp\PublicAPI\Interfaces\Repository
 */
$repository = new Repository;

$contentService = $repository->getContentService();
$locationService = $repository->getLocationService();
$contentTypeService = $repository->getContentTypeService();

$createStruct = $contentService->newContentCreateStruct(
    $contentTypeService->loadContentTypeByIdentifier( 'article' ),
    'eng-GB'
);

/**
 * Method 1
 *
 * Based on the current API, where validation is only done when using the service to create
 */
$createStruct->fields['title']['eng-GB'] = 'A title that is waaaaaaaaaaaaaaaaaaaaaaaaay too long for the title field';
$createStruct->fields['content']['eng-GB'] = '<p>What a <strong>wonderful</strong> world !</p>';
$createStruct->fields['show_comments'] = true;

try {
    $draft = $contentService->createContent(
        $createStruct,
        array(
            $locationService->newLocationCreateStruct( 123 )
        )
    );
    $publishedVersion = $contentService->publishDraft( $draft->versionInfo );
} catch( InvalidArgumentException $e ) {
    // This can mean that:
    // - no location has been set (4.x)
    // - the content's (and location's ?) remoteId already exists in the system
    // - one or more of the fields did not validate
}

/**
 * Method 2
 *
 * Possible improvement, where exceptions are actually thrown when assigning fields values
 */
try {
    $createStruct->fields['title']['eng-GB'] = 'This is my title';
} catch( FieldValidationException $e ) {
    // - We know the exception is about title, even though it will be repeated in the message
    // - The previous exception thrown earlier by the Type validator will
    //   provide us with details about what went wrong exactly
    echo "An error occured when validating $e->identifier in $e->languageCode: "  . $e->getMessage() . PHP_EOL;
}

$createStruct->fields['content']['eng-GB'] = '<p>What a <strong>wonderful</strong> world !</p>';
$createStruct->fields['show_comments'] = true;

try {
    $draft = $contentService->createContent(
        $createStruct,
        array(
            $locationService->newLocationCreateStruct( 123 )
        )
    );
    $publishedVersion = $contentService->publishDraft( $draft->versionInfo );
} catch( ContentValidationException $e ) {
    // Since fields have been validated earlier, all that's left is:
    // - no location has been set (4.x)
    // - the content's (and location's ?) remoteId already exists in the system
    // - one or more mandatory attributes haven't been given (we can't throw this one earlier, obviously)
} catch( InvalidArgumentException $e ) {
    // This one is only for border cases that don't fit in the more specified ones above
}

/**
 * Method 3
 *
 * An alternative, that doesn't use exceptions when setting values, but also provides the user with enough details.
 */
$createStruct->fields['title']['eng-GB'] = 'This is my title';
$createStruct->fields['content']['eng-GB'] = '<p>What a <strong>wonderful</strong> world !</p>';
$createStruct->fields['show_comments'] = true;

try {
    $draft = $contentService->createContent(
    $createStruct,
    array(
        $locationService->newLocationCreateStruct( 123 )
    )
    );
    $publishedVersion = $contentService->publishDraft( $draft->versionInfo );
} catch( ContentFieldValidationException $e ) {
    // Specific to field validation errors
    // Will let the user iterate over fields where errors were found, and analyze the exceptions attached to each
    foreach( $e->fieldsExceptions as $fieldException )
    {
        echo "An error occured when validating $fieldException->identifier in $fieldException->languageCode: "  . $fieldException->getMessage() . PHP_EOL;
    }
} catch( ContentValidationException $e ) {
    // Since fields have been validated earlier, all that's left is:
    // - no location has been set (4.x)
    // - the content's (and location's ?) remoteId already exists in the system
    // - one or more mandatory attributes haven't been given (we can't throw anything earlier)
} catch( InvalidArgumentException $e ) {
    // This can mean that:
    // - no location has been set (4.x)
    // - the content's (and location's ?) remoteId already exists in the system
    // - one or more of the fields did not validate
}
