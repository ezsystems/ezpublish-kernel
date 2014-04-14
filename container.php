<?php
/**
 * File generates service container instance
 *
 * Expects global $settings to be set by caller
 *
 * @deprecated Since 5.0, this is only used for unit tests.
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

use eZ\Publish\Core\Base\WrappedServiceContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Publish\Core\Base\Container\Compiler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

if ( !isset( $settings ) )
{
    throw new \RuntimeException( '$settings not provided to container.php' );
}

// [temp] Inject legacy kernel, as it does not yet have a factory
$dependencies = array();
if ( isset( $_ENV['legacyKernel'] ) )
{
    $dependencies['@legacyKernel'] = $_ENV['legacyKernel'];
}

$installDir = $settings["service"]["parameters"]["install_dir"];

$containerBuilder = new ContainerBuilder();

$settingsPath = $installDir . "/eZ/Publish/Core/settings/";
$loader = new YamlFileLoader( $containerBuilder, new FileLocator( $settingsPath ) );

$loader->load( 'io.yml' );
$loader->load( 'roles.yml' );
$loader->load( 'fieldtype_external_storages.yml' );
$loader->load( 'fieldtype_services.yml' );
$loader->load( 'indexable_fieldtypes.yml' );
$loader->load( 'fieldtypes.yml' );
$loader->load( 'papi.yml' );
$loader->load( 'storage_engines/common.yml' );
$loader->load( 'storage_engines/cache.yml' );
$loader->load( 'storage_engines/legacy.yml' );
$loader->load( 'storage_engines/cached_legacy.yml' );
$loader->load( 'storage_engines/legacy_solr.yml' );
$loader->load( 'storage_engines/cached_legacy_solr.yml' );
$loader->load( 'settings.yml' );

$containerBuilder->setParameter( "ezpublish.kernel.root_dir", $installDir );

$containerBuilder->addCompilerPass( new Compiler\FieldTypeRepositoryPass() );
$containerBuilder->addCompilerPass( new Compiler\RegisterLimitationTypePass() );

$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\FieldTypeRegistryPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\CriteriaConverterPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\CriterionFieldValueHandlerRegistryPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\ExternalStorageRegistryPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\FieldValueConverterRegistryPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\RoleLimitationConverterPass() );
$containerBuilder->addCompilerPass( new Compiler\Storage\Legacy\SortClauseConverterPass() );

$containerBuilder->setParameter( "install_dir", $installDir );

$serviceContainer = new WrappedServiceContainer( $containerBuilder );

return $serviceContainer;
