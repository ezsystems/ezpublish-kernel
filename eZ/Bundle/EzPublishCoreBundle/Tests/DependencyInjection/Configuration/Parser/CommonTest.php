<?php

/**
 * File containing the CommonTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Common;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Symfony\Component\Yaml\Yaml;

class CommonTest extends AbstractParserTestCase
{
    private $minimalConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $suggestionCollector;

    protected function getContainerExtensions()
    {
        $this->suggestionCollector = $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion\SuggestionCollectorInterface');

        return array(new EzPublishCoreExtension(array(new Common())));
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse(file_get_contents(__DIR__ . '/../../Fixtures/ezpublish_minimal.yml'));
    }

    public function testIndexPage()
    {
        $indexPage1 = '/Getting-Started';
        $indexPage2 = '/Contact-Us';
        $config = array(
            'system' => array(
                'ezdemo_site' => array('index_page' => $indexPage1),
                'ezdemo_site_admin' => array('index_page' => $indexPage2),
            ),
        );
        $this->load($config);

        $this->assertConfigResolverParameterValue('index_page', $indexPage1, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('index_page', $indexPage2, 'ezdemo_site_admin');
    }

    public function testDefaultPage()
    {
        $defaultPage1 = '/Getting-Started';
        $defaultPage2 = '/Foo/bar';
        $config = array(
            'system' => array(
                'ezdemo_site' => array('default_page' => $defaultPage1),
                'ezdemo_site_admin' => array('default_page' => $defaultPage2),
            ),
        );
        $this->load($config);

        $this->assertConfigResolverParameterValue('default_page', $defaultPage1, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('default_page', $defaultPage2, 'ezdemo_site_admin');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDatabaseSingleSiteaccess()
    {
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => array(
                        'database' => array(
                            'type' => 'sqlite',
                            'server' => 'localhost',
                            'user' => 'root',
                            'password' => 'root',
                            'database_name' => 'ezdemo',
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDatabaseSiteaccessGroup()
    {
        $this->load(
            array(
                'system' => array(
                    'ezdemo_group' => array(
                        'database' => array(
                            'type' => 'sqlite',
                            'server' => 'localhost',
                            'user' => 'root',
                            'password' => 'root',
                            'database_name' => 'ezdemo',
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Test defaults.
     */
    public function testNonExistentSettings()
    {
        $this->load();
        $this->assertConfigResolverParameterValue('url_alias_router', true, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('cache_service_name', 'cache.app', 'ezdemo_site');
        $this->assertConfigResolverParameterValue('var_dir', 'var', 'ezdemo_site');
        $this->assertConfigResolverParameterValue('storage_dir', 'storage', 'ezdemo_site');
        $this->assertConfigResolverParameterValue('binary_dir', 'original', 'ezdemo_site');
        $this->assertConfigResolverParameterValue('session_name', '%ezpublish.session_name.default%', 'ezdemo_site');
        $this->assertConfigResolverParameterValue('http_cache.purge_servers', array(), 'ezdemo_site');
        $this->assertConfigResolverParameterValue('anonymous_user_id', 10, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('index_page', null, 'ezdemo_site');
    }

    public function testMiscSettings()
    {
        $cachePoolName = 'cache_foo';
        $varDir = 'var/foo/bar';
        $storageDir = 'alternative_storage_folder';
        $binaryDir = 'alternative_binary_folder';
        $sessionName = 'alternative_session_name';
        $indexPage = '/alternative_index_page';
        $cachePurgeServers = array(
            'http://purge.server1/',
            'http://purge.server2:1234/foo',
            'https://purge.server3/bar',
        );
        $anonymousUserId = 10;
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => array(
                        'cache_service_name' => $cachePoolName,
                        'var_dir' => $varDir,
                        'storage_dir' => $storageDir,
                        'binary_dir' => $binaryDir,
                        'session_name' => $sessionName,
                        'index_page' => $indexPage,
                        'http_cache' => array(
                            'purge_servers' => $cachePurgeServers,
                        ),
                        'anonymous_user_id' => $anonymousUserId,
                    ),
                ),
            )
        );

        $this->assertConfigResolverParameterValue('cache_service_name', $cachePoolName, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('var_dir', $varDir, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('storage_dir', $storageDir, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('binary_dir', $binaryDir, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('session_name', $sessionName, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('index_page', $indexPage, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('http_cache.purge_servers', $cachePurgeServers, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('anonymous_user_id', $anonymousUserId, 'ezdemo_site');
    }

    public function testUserSettings()
    {
        $layout = 'somelayout.html.twig';
        $loginTemplate = 'login_template.html.twig';
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => array(
                        'user' => array(
                            'layout' => $layout,
                            'login_template' => $loginTemplate,
                        ),
                    ),
                ),
            )
        );

        $this->assertConfigResolverParameterValue('security.base_layout', $layout, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('security.login_template', $loginTemplate, 'ezdemo_site');
    }

    public function testNoUserSettings()
    {
        $this->load();
        $this->assertConfigResolverParameterValue(
            'security.base_layout',
            '%ezsettings.default.pagelayout%',
            'ezdemo_site'
        );
        $this->assertConfigResolverParameterValue(
            'security.login_template',
            'EzPublishCoreBundle:Security:login.html.twig',
            'ezdemo_site'
        );
    }

    /**
     * @dataProvider sessionSettingsProvider
     */
    public function testSessionSettings(array $inputParams, array $expected)
    {
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => $inputParams,
                ),
            )
        );

        $this->assertConfigResolverParameterValue('session', $expected['session'], 'ezdemo_site');
        $this->assertConfigResolverParameterValue('session_name', $expected['session_name'], 'ezdemo_site');
    }

    public function sessionSettingsProvider()
    {
        return array(
            array(
                array(
                    'session' => array(
                        'name' => 'foo',
                        'cookie_path' => '/foo',
                        'cookie_domain' => 'foo.com',
                        'cookie_lifetime' => 86400,
                        'cookie_secure' => false,
                        'cookie_httponly' => true,
                    ),
                ),
                array(
                    'session' => array(
                        'name' => 'foo',
                        'cookie_path' => '/foo',
                        'cookie_domain' => 'foo.com',
                        'cookie_lifetime' => 86400,
                        'cookie_secure' => false,
                        'cookie_httponly' => true,
                    ),
                    'session_name' => 'foo',
                ),
            ),
            array(
                array(
                    'session' => array(
                        'name' => 'foo',
                        'cookie_path' => '/foo',
                        'cookie_domain' => 'foo.com',
                        'cookie_lifetime' => 86400,
                        'cookie_secure' => false,
                        'cookie_httponly' => true,
                    ),
                    'session_name' => 'bar',
                ),
                array(
                    'session' => array(
                        'name' => 'bar',
                        'cookie_path' => '/foo',
                        'cookie_domain' => 'foo.com',
                        'cookie_lifetime' => 86400,
                        'cookie_secure' => false,
                        'cookie_httponly' => true,
                    ),
                    'session_name' => 'bar',
                ),
            ),
            array(
                array(
                    'session_name' => 'some_other_session_name',
                ),
                array(
                    'session' => array(
                        'name' => 'some_other_session_name',
                    ),
                    'session_name' => 'some_other_session_name',
                ),
            ),
        );
    }
}
