<?php
/**
 * assumed as injected
 * @var ezp\PublicAPI\Interfaces\Repository $repository
 */
$repository = null;

// create a new user

$userService = $repository->getUserService();
$contentService = $repository->getContentService();

// load Members Group in default installation
$userGroup = $userService->loadUserGroup( 12 );

// in 4.x the content type is take from configuration - will be changed in the future

$userCreate = $userService->newUserCreateStruct( "jdoe", "john.doe@example.org", "newpassword", "eng-US", /*content type*/null );
$userCreate->fields['first_name'] = 'John';
$userCreate->fields['last_name'] = 'Doe';

$user = $userService->createUser( $userCreate, array( $userGroup ) );
