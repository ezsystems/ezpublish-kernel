<?php
/**
 * ez Publish - API Prototype using Doctrine w/ annotations as backend for simplicity
 *
 * @Requries Doctrine 2
 * @Install: Change db settings bellow to point to a ez Publish install and you should be good to go!
 */

use \ezx\base\Repository, \ezp\base\Configuration;


if ( !isset( $_GET['fn'] ) )
{
    header( "Location: {$_SERVER['REQUEST_URI']}?fn=create&identifier=article&title=Catch22" );
    exit;
}

chdir( '../' );
require 'config.php';
require 'autoload.php';

$paths = array();
foreach ( glob( '{ezp,ezx}/*', GLOB_BRACE | GLOB_ONLYDIR ) as $path )
{
    $paths[] = "{$path}/settings/";
}
Configuration::setGlobalConfigurationData( $settings );
Configuration::setGlobalDirs( $paths, 'modules' );


// API 'create' demo code
if ( $_GET['fn'] === 'create' )
{
    if ( !isset( $_GET['identifier'] ) )
        die("Missing content type 'identifier' GET parameter, eg: ?fn=create&identifier=article or ?fn=create&identifier=folder&title=Catch22");

    $repository = new Repository( getDoctrineEm() );
    $contentService = $repository->ContentService();

    // Create Content object
    $content = $contentService->create( $_GET['identifier'] );
    /** Alternative example:
    $contentTypeService = $repository->ContentTypeService();
    $contentType = $contentTypeService->loadByIdentifier( $_GET['identifier'] );
    $content = new Content( $contentType );
    */

    $content->ownerId = 10;
    $content->sectionId = 3;

    if ( isset( $content->fields['tags'] ) )
    {
        $content->fields['tags']->type->value = "instance1";
    }

    if ( isset( $_GET['title'] ) && isset( $content->fields['title'] ) )
        $content->fields['title']->type->value = $_GET['title'];

    $content->notify();

    $state = $content->toHash();
    $out = var_export( $state, true );

    $newContent = $contentService->create( $_GET['identifier'] )->fromHash( $state );

    // test that reference works on new object
    if ( isset( $newContent->fields['tags'] ) )
    {
        $newContent->fields['tags']->type->value .= " instance2";
    }

    $out2 = var_export( $newContent->toHash( true ), true );

    echo "<h3>\$hash1 = new Content( \$contentType )-&gt;toHash();<br />\$hash2 = new Content( \$contentType )-&gt;fromHash( \$hash1 )-&gt;toHash( \$internal = true );</h3><table><tr><td><pre>{$out}</pre></td><td><pre>{$out2}</pre></td></tr></table>";
}
// API 'get' demo code
else if ( $_GET['fn'] === 'get' )
{
    if ( !isset( $_GET['id'] ) )
        die("Missing content location 'id' GET parameter, eg: ?fn=get&id=2");

    $repository = new Repository( getDoctrineEm() );
    $contentService = $repository->ContentService();

    $content = $contentService->load( (int) $_GET['id'] );
    $locations = $content->locations;

    $content->notify();
    $fieldStr = '';
    foreach ( $content->fields as $field )
    {
        $fieldStr .= '<br />&nbsp;' . $field  . ':<pre>' . htmlentities( $field->type->value ) . '</pre>';
    }
    echo 'Main Node ID: ' . $locations[0]->id
    . '<br />Children count: '. count( $locations[0]->children )
    . '<br />Parent: '.  $locations[0]->parent
    . '<br />Type: '.  $content->contentType
    . '<br />Content: ' . $content
    . ', Location count: '.   count( $locations )
    . '<br />Fields: ' . $fieldStr
    //. '<br />SQL: ' . $query->getSQL()
   ;
}
else
{
    die("GET parameter 'fn' has invalid value, should be 'create' or 'get'");
}






/**
 * Setup Doctrine and return EntityManager
 *
 * @return \Doctrine\ORM\EntityManager
 */
function getDoctrineEm()
{
    require 'Doctrine/Common/ClassLoader.php';
    $classLoader = new \Doctrine\Common\ClassLoader('Doctrine');
    $classLoader->register(); // register on SPL autoload stack

    $cwd = getcwd();
    if ( !is_dir( $cwd . '/var/cache/Proxies' ) )
        mkdir( "$cwd/var/cache/Proxies/", 0777 , true );// Seeing Protocol error? Try renaming ezp-next to next..

    $devMode = \ezp\base\Configuration::developmentMode();
    $config = new \Doctrine\ORM\Configuration();
    $config->setProxyDir( $cwd . '/var/cache/Proxies' );
    $config->setProxyNamespace('ezx\doctrine');
    $config->setAutoGenerateProxyClasses( $devMode );

    $driverImpl = $config->newDefaultAnnotationDriver( $cwd . '/ezx/' );
    $config->setMetadataDriverImpl( $driverImpl );

    if ( $devMode )
        $cache = new \Doctrine\Common\Cache\ArrayCache();
    else
        $cache = new \Doctrine\Common\Cache\ApcCache();

    $config->setMetadataCacheImpl( $cache );
    $config->setQueryCacheImpl( $cache );

    $evm = new \Doctrine\Common\EventManager();
    $settings = \ezp\base\Configuration::getInstance()->getSection( 'doctrine' );
    return  \Doctrine\ORM\EntityManager::create( $settings, $config, $evm );
}