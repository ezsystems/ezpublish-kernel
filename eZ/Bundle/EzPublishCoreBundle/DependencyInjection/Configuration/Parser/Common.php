<?php

/**
 * File containing the Common class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollectorAwareInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollectorInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Configuration parser handling all basic configuration (aka "common").
 */
class Common extends AbstractParser implements SuggestionCollectorAwareInterface
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollectorInterface */
    private $suggestionCollector;

    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->scalarNode('repository')->info('The repository to use. Choose among ezpublish.repositories.')->end()
            // @deprecated
            // Use ezpublish.repositories / repository settings instead.
            ->arrayNode('database')
                ->info('DEPRECATED. Use ezpublish.repositories / repository settings instead.')
                ->children()
                    ->enumNode('type')->values(['mysql', 'pgsql', 'sqlite'])->info('The database driver. Can be mysql, pgsql or sqlite.')->end()
                    ->scalarNode('server')->end()
                    ->scalarNode('port')->end()
                    ->scalarNode('user')->cannotBeEmpty()->end()
                    ->scalarNode('password')->end()
                    ->scalarNode('database_name')->cannotBeEmpty()->end()
                    ->scalarNode('charset')->defaultValue('utf8')->end()
                    ->scalarNode('socket')->end()
                    ->arrayNode('options')
                        ->info('Arbitrary options, supported by your DB driver ("driver-opts" in PDO)')
                        ->example(['foo' => 'bar', 'someOptionName' => ['one', 'two', 'three']])
                        ->useAttributeAsKey('key')
                        ->prototype('variable')->end()
                    ->end()
                    ->scalarNode('dsn')->info('Full database DSN. Will replace settings above.')->example('mysql://root:root@localhost:3306/ezdemo')->end()
                ->end()
            ->end()
            ->scalarNode('cache_service_name')
                ->example('cache.app')
                ->info('The cache pool service name to use for a siteaccess / siteaccess-group, *must* be present.')
            ->end()
            ->scalarNode('var_dir')
                ->cannotBeEmpty()
                ->example('var/ezdemo_site')
                ->info('The directory relative to web/ where files are stored. Default value is "var"')
            ->end()
            ->arrayNode('api_keys')
                ->info('Collection of API keys')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('google_maps')
                        ->setDeprecated('The child node "%node%" at path "%path%" is no longer used and deprecated.')
                        ->info('Google Maps API Key, required as of Google Maps v3 to make sure maps show up correctly.')
                    ->end()
                ->end()
            ->end()
            ->scalarNode('storage_dir')
                ->cannotBeEmpty()
                ->info("Directory where to place new files for storage, it's relative to var directory. Default value is 'storage'")
            ->end()
            ->scalarNode('binary_dir')
                ->cannotBeEmpty()
                ->info('Directory where binary files (from ezbinaryfile field type) are stored. Default value is "original"')
            ->end()
            // @deprecated since 5.3. Will be removed in 6.x.
            ->scalarNode('session_name')
                ->info('DEPRECATED. Use session.name instead.')
            ->end()
            ->arrayNode('session')
                ->info('Session options. Will override options defined in Symfony framework.session.*')
                ->children()
                    ->scalarNode('name')
                        ->info('The session name. If you want a session name per siteaccess, use "{siteaccess_hash}" token. Will override default session name from framework.session.name')
                        ->example(['session' => ['name' => 'eZSESSID{siteaccess_hash}']])
                    ->end()
                    ->scalarNode('cookie_lifetime')->end()
                    ->scalarNode('cookie_path')->end()
                    ->scalarNode('cookie_domain')->end()
                    ->booleanNode('cookie_secure')->end()
                    ->booleanNode('cookie_httponly')->end()
                ->end()
            ->end()
            ->scalarNode('pagelayout')
                ->info('The default layout to use')
                ->example('AppBundle::pagelayout.html.twig')
            ->end()
            ->scalarNode('index_page')
                ->info('The page that the index page will show. Default value is null.')
                ->example('/Getting-Started')
            ->end()
            ->scalarNode('default_page')
                ->info('The default page to show, e.g. after user login this will be used for default redirection. If provided, will override "default_target_path" from security.yml.')
                ->example('/Getting-Started')
            ->end()
            ->arrayNode('http_cache')
                ->info('Settings related to Http cache')
                ->children()
                    ->arrayNode('purge_servers')
                        ->info('Servers to use for Http PURGE (will NOT be used if ezpublish.http_cache.purge_type is "local").')
                        ->example(['http://localhost/', 'http://another.server/'])
                        ->requiresAtLeastOneElement()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->scalarNode('anonymous_user_id')
                ->cannotBeEmpty()
                ->example('10')
                ->info('The ID of the user used for everyone who is not logged in.')
            ->end()
            ->arrayNode('user')
                ->children()
                    ->scalarNode('layout')
                        ->info('Layout template to use for user related actions. This is most likely the base pagelayout template of your site.')
                        ->example(['layout' => 'eZDemoBundle::pagelayout.html.twig'])
                    ->end()
                    ->scalarNode('login_template')
                        ->info('Template to use for login form. Defaults to EzPublishCoreBundle:security:login.html.twig')
                        ->example(['login_template' => 'AcmeTestBundle:User:login.html.twig'])
                    ->end()
                ->end()
            ->end();
    }

    public function preMap(array $config, ContextualizerInterface $contextualizer)
    {
        $contextualizer->mapConfigArray('session', $config);
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        if (isset($scopeSettings['database'])) {
            $this->addDatabaseConfigSuggestion($currentScope, $scopeSettings['database']);
        }
        if (isset($scopeSettings['repository'])) {
            $contextualizer->setContextualParameter('repository', $currentScope, $scopeSettings['repository']);
        }
        if (isset($scopeSettings['cache_service_name'])) {
            $contextualizer->setContextualParameter('cache_service_name', $currentScope, $scopeSettings['cache_service_name']);
        }
        if (isset($scopeSettings['var_dir'])) {
            $contextualizer->setContextualParameter('var_dir', $currentScope, $scopeSettings['var_dir']);
        }
        if (isset($scopeSettings['storage_dir'])) {
            $contextualizer->setContextualParameter('storage_dir', $currentScope, $scopeSettings['storage_dir']);
        }
        if (isset($scopeSettings['binary_dir'])) {
            $contextualizer->setContextualParameter('binary_dir', $currentScope, $scopeSettings['binary_dir']);
        }

        $contextualizer->setContextualParameter('api_keys', $currentScope, $scopeSettings['api_keys']);
        foreach ($scopeSettings['api_keys'] as $key => $value) {
            $contextualizer->setContextualParameter('api_keys.' . $key, $currentScope, $value);
        }

        // session_name setting is deprecated in favor of session.name
        $container = $contextualizer->getContainer();
        $sessionOptions = $container->hasParameter("ezsettings.$currentScope.session") ? $container->getParameter("ezsettings.$currentScope.session") : [];
        if (isset($sessionOptions['name'])) {
            $contextualizer->setContextualParameter('session_name', $currentScope, $sessionOptions['name']);
        }
        // @deprecated session_name is deprecated, but if present, in addition to session.name, consider it instead (BC).
        if (isset($scopeSettings['session_name'])) {
            $sessionOptions['name'] = $scopeSettings['session_name'];
            $contextualizer->setContextualParameter('session_name', $currentScope, $scopeSettings['session_name']);
            $contextualizer->setContextualParameter('session', $currentScope, $sessionOptions);
        }

        if (isset($scopeSettings['http_cache']['purge_servers'])) {
            $contextualizer->setContextualParameter('http_cache.purge_servers', $currentScope, $scopeSettings['http_cache']['purge_servers']);
        }
        if (isset($scopeSettings['anonymous_user_id'])) {
            $contextualizer->setContextualParameter('anonymous_user_id', $currentScope, $scopeSettings['anonymous_user_id']);
        }
        if (isset($scopeSettings['user']['layout'])) {
            $contextualizer->setContextualParameter('security.base_layout', $currentScope, $scopeSettings['user']['layout']);
        }
        if (isset($scopeSettings['user']['login_template'])) {
            $contextualizer->setContextualParameter('security.login_template', $currentScope, $scopeSettings['user']['login_template']);
        }
        if (isset($scopeSettings['index_page'])) {
            $contextualizer->setContextualParameter('index_page', $currentScope, $scopeSettings['index_page']);
        }
        if (isset($scopeSettings['default_page'])) {
            $contextualizer->setContextualParameter('default_page', $currentScope, '/' . ltrim($scopeSettings['default_page'], '/'));
        }
        if (isset($scopeSettings['pagelayout'])) {
            $contextualizer->setContextualParameter('pagelayout', $currentScope, $scopeSettings['pagelayout']);
        }
    }

    /**
     * Injects SuggestionCollector.
     *
     * @param SuggestionCollectorInterface $suggestionCollector
     */
    public function setSuggestionCollector(SuggestionCollectorInterface $suggestionCollector)
    {
        $this->suggestionCollector = $suggestionCollector;
    }

    private function addDatabaseConfigSuggestion($sa, array $databaseConfig)
    {
        $suggestion = new ConfigSuggestion(
<<<EOT
Database configuration has changed for eZ Content repository.
Please define:
 - An entry in ezpublish.repositories
 - A Doctrine connection (You may check configuration reference for Doctrine "config:dump-reference doctrine" console command.)
 - A reference to configured repository in ezpublish.system.$sa.repository
EOT
        );
        $suggestion->setMandatory(true);
        $suggestionArray = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'dbname' => 'my_database',
            'user' => 'my_user',
            'password' => 'some_password',
            'charset' => 'UTF8',
        ];

        if (!empty($databaseConfig)) {
            $suggestionArray['dbname'] = $databaseConfig['database_name'];
            $suggestionArray['host'] = $databaseConfig['server'];
            $driverMap = [
                'mysql' => 'pdo_mysql',
                'pgsql' => 'pdo_pgsql',
                'sqlite' => 'pdo_sqlite',
            ];
            if (isset($driverMap[$databaseConfig['type']])) {
                $suggestionArray['driver'] = $driverMap[$databaseConfig['type']];
            } else {
                $suggestionArray['driver'] = $databaseConfig['type'];
            }
            if (isset($databaseConfig['socket'])) {
                $suggestionArray['unix_socket'] = $databaseConfig['socket'];
            }
            $suggestionArray['options'] = $databaseConfig['options'];
            $suggestionArray['user'] = $databaseConfig['user'];
            $suggestionArray['password'] = $databaseConfig['password'];
        }
        $suggestion->setSuggestion(
            [
                'doctrine' => [
                    'dbal' => [
                        'connections' => [
                            'default' => $suggestionArray,
                        ],
                    ],
                ],
                'ezpublish' => [
                    'repositories' => [
                        'my_repository' => [
                            'storage' => [
                                'engine' => 'legacy',
                                'connection' => 'default',
                            ],
                            'search' => [
                                'engine' => 'legacy',
                                'connection' => 'default',
                            ],
                        ],
                    ],
                    'system' => [
                        $sa => [
                            'repository' => 'my_repository',
                        ],
                    ],
                ],
            ]
        );

        $this->suggestionCollector->addSuggestion($suggestion);
    }
}
