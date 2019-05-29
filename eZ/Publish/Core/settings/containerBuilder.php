<?php

/**
 * File generates service container builder instance.
 *
 * Expects global $installDir to be set by caller
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

use eZ\Publish\API\Repository\Tests\Container\Compiler\SetAllServicesPublicPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use eZ\Publish\Core\Base\Container\Compiler;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Config\Resource\FileResource;

if (!isset($installDir)) {
    throw new \RuntimeException('$installDir not provided to ' . __FILE__);
}

$containerBuilder = new ContainerBuilder();

// Track current file for changes
$containerBuilder->addResource(new FileResource(__FILE__));

$settingsPath = $installDir . '/eZ/Publish/Core/settings/';
$loader = new YamlFileLoader($containerBuilder, new FileLocator($settingsPath));

$loader->load('fieldtype_external_storages.yml');
$loader->load('fieldtype_services.yml');
$loader->load('fieldtypes.yml');
$loader->load('indexable_fieldtypes.yml');
$loader->load('io.yml');
$loader->load('repository.yml');
$loader->load('repository/inner.yml');
$loader->load('repository/signalslot.yml');
$loader->load('repository/siteaccessaware.yml');
$loader->load('roles.yml');
$loader->load('storage_engines/common.yml');
$loader->load('storage_engines/cache.yml');
$loader->load('storage_engines/legacy.yml');
$loader->load('storage_engines/shortcuts.yml');
$loader->load('settings.yml');
$loader->load('utils.yml');
$loader->load('tests/common.yml');
$loader->load('policies.yml');
$loader->load('richtext.yml');

// Cache settings (takes same env variables as ezplatform does, only supports "singleredis" setup)
if (getenv('CUSTOM_CACHE_POOL') === 'singleredis') {
    /*
     * Symfony\Component\Cache\Adapter\RedisAdapter
     * @param \Redis|\RedisArray|\RedisCluster|\Predis\Client $redisClient
     * public function __construct($redisClient, $namespace = '', $defaultLifetime = 0)
     *
     * $redis = new \Redis();
     * $redis->connect('127.0.0.1', 6379, 2.5);
     */
    $containerBuilder
        ->register('ezpublish.cache_pool.driver.redis', 'Redis')
        ->addMethodCall('connect', [(getenv('CACHE_HOST') ?: '127.0.0.1'), 6379, 2.5]);

    $containerBuilder
        ->register('ezpublish.cache_pool.driver', RedisAdapter::class)
        ->setArguments([new Reference('ezpublish.cache_pool.driver.redis'), '', 120]);
}

$containerBuilder->setParameter('ezpublish.kernel.root_dir', $installDir);

$containerBuilder->addCompilerPass(new Compiler\FieldTypeCollectionPass());
$containerBuilder->addCompilerPass(new Compiler\RegisterLimitationTypePass());

$containerBuilder->addCompilerPass(new Compiler\Storage\ExternalStorageRegistryPass());
$containerBuilder->addCompilerPass(new Compiler\Storage\Legacy\FieldValueConverterRegistryPass());
$containerBuilder->addCompilerPass(new Compiler\Storage\Legacy\RoleLimitationConverterPass());

$containerBuilder->addCompilerPass(new Compiler\Search\Legacy\CriteriaConverterPass());
$containerBuilder->addCompilerPass(new Compiler\Search\Legacy\CriterionFieldValueHandlerRegistryPass());
$containerBuilder->addCompilerPass(new Compiler\Search\Legacy\SortClauseConverterPass());

//
// Symfony 4 makes services private by default. Test cases are not prepared for this.
// This is a simple workaround to override services as public.
//
$containerBuilder->addCompilerPass(new SetAllServicesPublicPass());

return $containerBuilder;
