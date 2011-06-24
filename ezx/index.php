<?php
/**
 * ez Publish - API Prototype using Doctrine w/ annotations as backend for simplicity
 *
 * @Requries Doctrine 2
 * @Install: Change db settings bellow to point to a ez Publish install and you should be good to go!
 */

use \ezp\base\ServiceContainer, \ezp\base\Configuration;


if ( !isset( $_GET['fn'] ) )
{
    header( "Location: {$_SERVER['REQUEST_URI']}?fn=create&identifier=article&title=Catch22" );
    exit;
}

chdir( '../' );
require 'config.php';
require 'ezp/base/autoloader.php';
spl_autoload_register( array( new ezp\base\Autoloader( $settings['base']['autoload'] ), 'load' ) );

$paths = array();
foreach ( glob( '{ezp,ezx}/*', GLOB_BRACE | GLOB_ONLYDIR ) as $path )//@todo Take from configuration
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

    $sc = new ServiceContainer();
    $repository = $sc->getRepository();
    $contentService = $repository->getContentService();

    // Create Content object
    $content = $contentService->create( $_GET['identifier'] );
    /** Alternative example:
    $contentTypeService = $repository->ContentTypeService();
    $contentType = $contentTypeService->loadByIdentifier( $_GET['identifier'] );
    $content = new Content( $contentType );
    */

    $content->ownerId = 10;
    $content->sectionId = 3;

    if ( isset( $content->fieldMap['tags'] ) )
    {
        $content->fieldMap['tags']->type->value = "instance1";
        // should be:
        // $content->fieldMap['tags'] = "instance1";
        // shortcut for:
        //$content->fieldMap['tags']->value = "instance1";
    }

    if ( isset( $_GET['title'] ) && isset( $content->fieldMap['title'] ) )
        $content->fieldMap['title']->type->value = $_GET['title'];

    $content->notify( 'store' );// Needed to make sure changes in fieldtypes tricle down to field

    $state = $content->toHash();
    $out = var_export( $state, true );

    $newContent = $contentService->create( $_GET['identifier'] )->fromHash( $state );

    // test that reference works on new object
    if ( isset( $newContent->fieldMap['tags'] ) )
    {
        $newContent->fieldMap['tags']->type->value .= " instance2";
    }

    $newContent->notify( 'store' );// Needed to make sure changes in fieldtypes tricle down to field

    $out2 = var_export( $newContent->toHash( true ), true );

    echo "<h3>\$hash1 = new Content( \$contentType )-&gt;toHash();<br />\$hash2 = new Content( \$contentType )-&gt;fromHash( \$hash1 )-&gt;toHash( \$internal = true );</h3><table><tr><td><pre>{$out}</pre></td><td><pre>{$out2}</pre></td></tr></table>";
}
// API 'get' demo code
else if ( $_GET['fn'] === 'get' )
{
    if ( !isset( $_GET['id'] ) )
        die("Missing content location 'id' GET parameter, eg: ?fn=get&id=2");

    $sc = new ServiceContainer();
    $repository = $sc->getRepository();
    $contentService = $repository->getContentService();

    $content = $contentService->load( (int) $_GET['id'] );
    $locations = $content->locations;

    $fieldStr = '';
    foreach ( $content->fieldMap as $field )
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