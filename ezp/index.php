<?php
/**
 * ez Publish - Public API Prototype
 */
namespace ezp\Content;
use ezp\Base\Configuration,
    ezp\Base\ServiceContainer,
    ezp\Content\Concrete as Content,
    ezp\User\Concrete as User;

// Use bootstrap.php to setup autoload and Configuration
chdir( '../' );
require 'bootstrap.php';


// Setup ServiceContainer for dependency injection using in-memory storage
$sc = new ServiceContainer(
    Configuration::getInstance('service')->getAll(),
    array(
        '@persistence_handler' => new \eZ\Publish\SPI\Persistence\Storage\InMemory\Handler(),
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
$version = $content->getCurrentVersion();
$version->fields['name'] = 'New Folder';
$version->fields['description'] = 'This is an empty folder';

//$content = $repository->getContentService()->create( $content );


echo "Content id: {$content->id}<br />";

echo "Fields:<br />";
foreach ( $version->fields as $identifier => $field )
{
    echo " > $identifier: {$field}<br />";// Using $value __toString()
}

