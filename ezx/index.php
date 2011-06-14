<?php
/**
 * ez Publish - API Prototype using Doctrine w/ annotations as backend for simplicity
 *
 * @Requries Doctrine 2
 * @Install: Change db settings bellow to point to a ez Publish install and you should be good to go!
 */

namespace ezp\system;
use \ezx\base\Repository;

// Change these db settings to test the 'get' demo
$settings = array(
    'driver'    => 'pdo_mysql',
    'user'      => 'root',
    'password'  => 'publish44',
    'host'      => 'localhost',
    'dbname'    => 'ezpublish',
    'dev_mode'  => true,
);


if ( !isset( $_GET['fn'] ) )
{
    header( "Location: {$_SERVER['REQUEST_URI']}?fn=create&identifier=article&title=Catch22" );
    exit;
}

chdir( '../' );
require 'autoload.php';


// API 'create' demo code
if ( $_GET['fn'] === 'create' )
{
    if ( !isset( $_GET['identifier'] ) )
        die("Missing content type 'identifier' GET parameter, eg: ?fn=create&identifier=article or ?fn=create&identifier=folder&title=Catch22");

    $repository = new Repository( getDoctrineEm( $settings ) );
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

    $repository = new Repository( getDoctrineEm( $settings ) );
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
function getDoctrineEm( array $settings )
{
    require 'Doctrine/Common/ClassLoader.php';
    $classLoader = new \Doctrine\Common\ClassLoader('Doctrine');
    $classLoader->register(); // register on SPL autoload stack

    $cwd = getcwd();
    if ( !is_dir( $cwd . '/var/cache/Proxies' ) )
        mkdir( "$cwd/var/cache/Proxies/", 0777 , true );// Seeing Protocol error? Try renaming ezp-next to next..

    $config = new \Doctrine\ORM\Configuration();
    $config->setProxyDir( $cwd . '/var/cache/Proxies' );
    $config->setProxyNamespace('ezx\doctrine');
    $config->setAutoGenerateProxyClasses( $settings['dev_mode']  );

    $driverImpl = $config->newDefaultAnnotationDriver( $cwd . '/ezx/' );
    $config->setMetadataDriverImpl( $driverImpl );

    if ( $settings['dev_mode'] )
        $cache = new \Doctrine\Common\Cache\ArrayCache();
    else
        $cache = new \Doctrine\Common\Cache\ApcCache();

    $config->setMetadataCacheImpl( $cache );
    $config->setQueryCacheImpl( $cache );

    $evm = new \Doctrine\Common\EventManager();
    return  \Doctrine\ORM\EntityManager::create( $settings, $config, $evm );
}


// fake config class
class Configuration
{
    static function getInstance()
    {
        return new self();
    }

    public function get( $section, $var )
    {
        If ( $var === 'type' )
            return array(
                'ezstring' => 'ezx\content\Field_Type_String',
                'ezinteger' => 'ezx\content\Field_Type_Int',
                'ezfloat' => 'ezx\content\Field_Type_Float',
                'ezxmltext' => 'ezx\content\Field_Type_Xml',
                'ezimage' => 'ezx\content\Field_Type_Image',
                'ezkeyword' => 'ezx\content\Field_Type_Keyword',
                'ezobjectrelation' => 'ezx\content\Field_Type_Relation',
                'ezauthor' => 'ezx\content\Field_Type_Author',
                'ezboolean' => 'ezx\content\Field_Type_Boolean',
                'ezdatetime' => 'ezx\content\Field_Type_Datetime',
                'ezsrrating' => 'ezx\content\Field_Type_Rating',
                'eztext' => 'ezx\content\Field_Type_Text',
            );
        return array(
            'ezstring' => 'ezx\content\Field_String',
            'ezinteger' => 'ezx\content\Field_Int',
            'ezfloat' => 'ezx\content\Field_Float',
            'ezxmltext' => 'ezx\content\Field_Xml',
            'ezimage' => 'ezx\content\Field_Image',
            'ezkeyword' => 'ezx\content\Field_Keyword',
            'ezobjectrelation' => 'ezx\content\Field_Relation',
            'ezauthor' => 'ezx\content\Field_Author',
            'ezboolean' => 'ezx\content\Field_Boolean',
            'ezdatetime' => 'ezx\content\Field_Datetime',
            'ezsrrating' => 'ezx\content\Field_Rating',
            'eztext' => 'ezx\content\Field_Text',
        );
    }
}