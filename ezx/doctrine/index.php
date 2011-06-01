<?php
/**
 * ez Publish - API Prototype using Doctrine w/ annotations as backend for simplicity
 *
 * @Requries Doctrine 2
 * @Install: Change db settings bellow to point to a ez Publish install and you should be good to go!
 */

namespace ezp\system;// Needed for fake Configuration class in the bottom of this file

$settings = array(
    'driver'    => 'pdo_mysql',
    'user'      => 'root',
    'password'  => 'publish44',
    'host'      => 'localhost',
    'dbname'    => 'ezpublish',
    'dev_mode'  => true,
);


if ( !isset( $_GET['fn'] ) )
    die( "Missing fn GET parameter, valid options are '?fn=get' and '?fn=create'!" );

chdir( '../../' );


require 'autoload.php';

// First setup repository instance @todo This should be done transparently
require 'Doctrine/Common/ClassLoader.php';
$classLoader = new \Doctrine\Common\ClassLoader('Doctrine');
$classLoader->register(); // register on SPL autoload stack

$cwd = getcwd();


if ( !is_dir( $cwd . '/var/cache/Proxies' ) )
    mkdir( "$cwd/var/cache/Proxies/", 0777 , true );// Seeing Protocol error? Try renaming ezp-next to next..

$config = new \Doctrine\ORM\Configuration();
$config->setProxyDir( $cwd . '/var/cache/Proxies' );
$config->setProxyNamespace('ezx\doctrine\model');
$config->setAutoGenerateProxyClasses( $settings['dev_mode']  );

$driverImpl = $config->newDefaultAnnotationDriver( $cwd . '/ezx/doctrine/model' );
$config->setMetadataDriverImpl( $driverImpl );

if ( $settings['dev_mode'] )
    $cache = new \Doctrine\Common\Cache\ArrayCache();
else
    $cache = new \Doctrine\Common\Cache\ApcCache();
$config->setMetadataCacheImpl( $cache );
$config->setQueryCacheImpl( $cache );

$evm = new \Doctrine\Common\EventManager();
$em = \Doctrine\ORM\EntityManager::create( $settings, $config, $evm );

$repository = new \ezx\doctrine\model\ContentRepository( $em );
\ezx\doctrine\model\ContentRepository::set( $repository );

// API demos
if ( $_GET['fn'] === 'create' )
{
    if ( !isset( $_GET['identifier'] ) )
        die("Missing content type 'identifier' GET parameter, eg: ?fn=create&identifier=article or ?fn=create&identifier=folder&title=Catch22");

    // Create model object
    $content = $repository->createContent( $_GET['identifier'] );

    $content->ownerId = 10;
    $content->sectionId = 3;

    if ( isset( $content->fields['tags'] ) )
    {
        $content->fields['tags'] = "demo";
    }

    if ( isset( $_GET['title'] ) && isset( $content->fields['title'] ) )
        $content->fields['title'] = $_GET['title'];

    $state = $content->getState();
    $out = var_export( $state, true );

    $newContent = $repository->createContent( $_GET['identifier'] )->setState( $state );

    // test that reference works on new object
    if ( isset( $newContent->fields['tags'] ) )
    {
        $newContent->fields['tags'] .= " object2";
    }

    $out2 = var_export( $newContent->getState(), true );

    echo "<h3>\$repository-&gt;createContent( \$_GET['identifier'] ) > -&gt;setState() > var_export() > __set_state() test</h3><table><tr><td><pre>{$out}</pre></td><td><pre>{$out2}</pre></td></tr></table>";
}
else if ( $_GET['fn'] === 'get' )
{
    if ( !isset( $_GET['id'] ) )
        die("Missing content location 'id' GET parameter, eg: ?fn=get&id=2");

    $location = $repository->load( 'Location', (int) $_GET['id'] );
    $content = $location->content;

    $fieldStr = '';
    foreach ( $content->fields as $field )
    {
        $fieldStr .= '<br />&nbsp;' . $field  . ':<pre>' . htmlentities( $field->value ) . '</pre>';
    }
    echo 'Id: ' . $location->id
    . '<br />Children count: '. count( $location->children )
    . '<br />Parent: '.  $location->parent
    . '<br />Type: '.  $content->contentType
    . '<br />Content: ' . $content
    . ', Location count: '.   count( $content->locations )
    . '<br />Fields: ' . $fieldStr
    //. '<br />SQL: ' . $query->getSQL()
   ;
}
else
    die("GET parameter 'fn' has invalid value, should be 'create' or 'get'");

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
                'ezstring' => 'ezx\doctrine\model\Field_Type_String',
                'ezinteger' => 'ezx\doctrine\model\Field_Type_Int',
                'ezfloat' => 'ezx\doctrine\model\Field_Type_Float',
                'ezxmltext' => 'ezx\doctrine\model\Field_Type_Xml',
                'ezimage' => 'ezx\doctrine\model\Field_Type_Image',
                'ezkeyword' => 'ezx\doctrine\model\Field_Type_Keyword',
                'ezobjectrelation' => 'ezx\doctrine\model\Field_Type_Relation',
                'ezauthor' => 'ezx\doctrine\model\Field_Type_Author',
                'ezboolean' => 'ezx\doctrine\model\Field_Type_Boolean',
                'ezdatetime' => 'ezx\doctrine\model\Field_Type_Datetime',
                'ezsrrating' => 'ezx\doctrine\model\Field_Type_Rating',
                'eztext' => 'ezx\doctrine\model\Field_Type_Text',
            );
        return array(
            'ezstring' => 'ezx\doctrine\model\Field_String',
            'ezinteger' => 'ezx\doctrine\model\Field_Int',
            'ezfloat' => 'ezx\doctrine\model\Field_Float',
            'ezxmltext' => 'ezx\doctrine\model\Field_Xml',
            'ezimage' => 'ezx\doctrine\model\Field_Image',
            'ezkeyword' => 'ezx\doctrine\model\Field_Keyword',
            'ezobjectrelation' => 'ezx\doctrine\model\Field_Relation',
            'ezauthor' => 'ezx\doctrine\model\Field_Author',
            'ezboolean' => 'ezx\doctrine\model\Field_Boolean',
            'ezdatetime' => 'ezx\doctrine\model\Field_Datetime',
            'ezsrrating' => 'ezx\doctrine\model\Field_Rating',
            'eztext' => 'ezx\doctrine\model\Field_Text',
        );
    }
}