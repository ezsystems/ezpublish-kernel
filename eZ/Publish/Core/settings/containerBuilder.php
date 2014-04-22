<?php
/**
 * File generates service container builder instance
 *
 * Expects global $installDir to be set by caller
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use eZ\Publish\Core\Base\Container\Compiler;
use Symfony\Component\Config\Resource\FileResource;

if ( !isset( $installDir ) )
{
    throw new \RuntimeException( '$installDir not provided to ' . __FILE__ );
}

$containerBuilder = new ContainerBuilder();

$containerBuilder->addResource( new FileResource( __FILE__ ) );

$settingsPath = $installDir . "/eZ/Publish/Core/settings/";
$loader = new YamlFileLoader( $containerBuilder, new FileLocator( $settingsPath ) );

$loader->load( 'io.yml' );
$loader->load( 'roles.yml' );
$loader->load( 'fieldtype_external_storages.yml' );
$loader->load( 'fieldtype_services.yml' );
$loader->load( 'indexable_fieldtypes.yml' );
$loader->load( 'fieldtypes.yml' );
$loader->load( 'repository.yml' );
$loader->load( 'storage_engines/common.yml' );
$loader->load( 'storage_engines/cache.yml' );
$loader->load( 'storage_engines/legacy.yml' );
$loader->load( 'storage_engines/cached_legacy.yml' );
$loader->load( 'storage_engines/legacy_solr.yml' );
$loader->load( 'storage_engines/cached_legacy_solr.yml' );
$loader->load( 'settings.yml' );

$containerBuilder->setParameter( "ezpublish.kernel.root_dir", $installDir );

$containerBuilder->addCompilerPass( new Compiler\FieldTypeCollectionPass() );
$containerBuilder->addCompilerPass( new Compiler\RegisterLimitationTypePass() );

$containerBuilder->addCompilerPass( new Compiler\Storage\ExternalStorageRegistryPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\CriteriaConverterPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\CriterionFieldValueHandlerRegistryPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\FieldValueConverterRegistryPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\RoleLimitationConverterPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\SortClauseConverterPass() );

return $containerBuilder;
