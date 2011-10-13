<?php
/**
 * ez Publish - Public API Prototype
 */
namespace ezp\Content;
use ezp\Base\Configuration,
    ezp\Base\ServiceContainer,
    ezp\Content\Concrete as Content,
    ezp\User\Concrete as User;

// Use testsBootstrap.php to setup autolaod and Configuration
chdir( '../' );
require 'testsBootstrap.php';


// Setup ServiceContainer for dependency injection using in-memory storage
$sc = new ServiceContainer(
    Configuration::getInstance('service')->getAll(),
    array(
        '@persistence_handler' => new \ezp\Persistence\Storage\InMemory\Handler(),
        '@io_handler' => new \ezp\Io\Storage\InMemory(),
    )
);

// Get repository and set 'Admin' as current user ('Anonymous' is default user)
$repository = $sc->getRepository();
$admin = $repository->getUserService()->load( 14 );
$anonymous = $repository->setUser( $admin );


// Get 'Folder' Content Type (Class)
$contentType = $repository->getContentTypeService()->load( 1 );

// Get 'Standard' Section
$section = $repository->getSectionService()->load( 1 );


// Create Content object
$content = new Content( $contentType, $anonymous );
$content->setSection( $section );
$content->fields['name'] = 'New Folder';
$content->fields['description'] = 'This is an empty folder';

//$content = $repository->getContentService()->create( $content );


echo "Content id: {$content->id}<br />";

echo "Fields:<br />";
foreach ( $content->fields as $identifier => $field )
{
    echo " > $identifier: {$field}<br />";// Using $value __toString()
}

