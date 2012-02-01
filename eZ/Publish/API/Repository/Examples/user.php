<?php
/**
 * assumed as injected
 * @var eZ\Publish\API\Repository\Repository $repository
 */
$repository = null;

$userService = $repository->getUserService();
$contentService = $repository->getContentService();

/**
 * 1. Create a new user
 */

// load Members Group in default installation
$userGroup = $userService->loadUserGroup( 12 );

// in 4.x the content type is take from configuration - will be changed in the future
// this is
$userCreate = $userService->newUserCreateStruct( "jdoe", "john.doe@example.org", "newpassword", "eng-US", /*content type*/null );
$userCreate->fields['first_name'] = 'John';
$userCreate->fields['last_name'] = 'Doe';

$user = $userService->createUser( $userCreate, array( $userGroup ) );


/**
 * 2. Create a new user group
 */

// get struct
$userGroupCreate = $userService->newUserGroupCreateStruct( "eng-US" );

// set fields
$userGroupCreate->fields['name'] = 'NomNom Users';
$userGroupCreate->fields['description'] = 'User group for users to like to NomNom';

// create (and publish)
$newUserGroup = $userService->createUserGroup( $userGroupCreate, $userGroup );
