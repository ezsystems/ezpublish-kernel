<?php
/**
 * File containing the CommonTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Common;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Yaml\Yaml;

class CommonTest extends AbstractExtensionTestCase
{
    private $minimalConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $suggestionCollector;

    /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions()
    {
        $this->suggestionCollector = $this->getMock( 'eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion\SuggestionCollectorInterface' );
        return array( new EzPublishCoreExtension( array( new Common() ) ) );
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse( file_get_contents( __DIR__ . '/../../Fixtures/ezpublish_minimal.yml' ) );
    }

    public function testIndexPage()
    {
        $indexPage1 = '/Getting-Started';
        $indexPage2 = '/Contact-Us';
        $config = array(
            'system' => array(
                'ezdemo_site' => array( 'index_page' => $indexPage1 ),
                'ezdemo_site_admin' => array( 'index_page' => $indexPage2 ),
            )
        );
        $this->load( $config );

        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.index_page' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site_admin.index_page' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.global.index_page' ) );
        $this->assertSame( $indexPage1, $this->container->getParameter( 'ezsettings.ezdemo_site.index_page' ) );
        $this->assertSame( $indexPage2, $this->container->getParameter( 'ezsettings.ezdemo_site_admin.index_page' ) );
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
                        )
                    )
                )
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
                        )
                    )
                )
            )
        );
    }

    public function testLegacyMode()
    {
        $this->load( array( 'system' => array( 'ezdemo_site' => array( 'legacy_mode' => true ) ) ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.legacy_mode' ) );
        $this->assertTrue( $this->container->getParameter( 'ezsettings.ezdemo_site.legacy_mode' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.url_alias_router' ) );
        $this->assertFalse( $this->container->getParameter( 'ezsettings.ezdemo_site.url_alias_router' ) );
    }

    public function testNotLegacyMode()
    {
        $this->load( array( 'system' => array( 'ezdemo_site' => array( 'legacy_mode' => false ) ) ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.legacy_mode' ) );
        $this->assertFalse( $this->container->getParameter( 'ezsettings.ezdemo_site.legacy_mode' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.url_alias_router' ) );
        $this->assertTrue( $this->container->getParameter( 'ezsettings.ezdemo_site.url_alias_router' ) );
    }

    public function testNonExistentSettings()
    {
        $this->load();
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.legacy_mode' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.url_alias_router' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.cache_pool_name' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.var_dir' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.storage_dir' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.binary_dir' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.session_name' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.http_cache.purge_servers' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.anonymous_user_id' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.index_page' ) );
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
            'https://purge.server3/bar'
        );
        $anonymousUserId = 10;
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => array(
                        'cache_pool_name' => $cachePoolName,
                        'var_dir' => $varDir,
                        'storage_dir' => $storageDir,
                        'binary_dir' => $binaryDir,
                        'session_name' => $sessionName,
                        'index_page' => $indexPage,
                        'http_cache' => array(
                            'purge_servers' => $cachePurgeServers
                        ),
                        'anonymous_user_id' => $anonymousUserId
                    )
                )
            )
        );

        $this->assertSame( $cachePoolName, $this->container->getParameter( 'ezsettings.ezdemo_site.cache_pool_name' ) );
        $this->assertSame( $varDir, $this->container->getParameter( 'ezsettings.ezdemo_site.var_dir' ) );
        $this->assertSame( $storageDir, $this->container->getParameter( 'ezsettings.ezdemo_site.storage_dir' ) );
        $this->assertSame( $binaryDir, $this->container->getParameter( 'ezsettings.ezdemo_site.binary_dir' ) );
        $this->assertSame( $sessionName, $this->container->getParameter( 'ezsettings.ezdemo_site.session_name' ) );
        $this->assertSame( $indexPage, $this->container->getParameter( 'ezsettings.ezdemo_site.index_page' ) );
        $this->assertSame( $cachePurgeServers, $this->container->getParameter( 'ezsettings.ezdemo_site.http_cache.purge_servers' ) );
        $this->assertSame( $anonymousUserId, $this->container->getParameter( 'ezsettings.ezdemo_site.anonymous_user_id' ) );
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
                    )
                )
            )
        );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.security.base_layout' ) );
        $this->assertSame( $layout, $this->container->getParameter( 'ezsettings.ezdemo_site.security.base_layout' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.security.login_template' ) );
        $this->assertSame( $loginTemplate, $this->container->getParameter( 'ezsettings.ezdemo_site.security.login_template' ) );
    }

    public function testNoUserSettings()
    {
        $this->load();
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.security.base_layout' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.security.login_template' ) );
    }

    /**
     * @dataProvider sessionSettingsProvider
     */
    public function testSessionSettings( array $inputParams, array $expected )
    {
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => $inputParams
                )
            )
        );

        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.session' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.session_name' ) );
        $this->assertEquals( $expected['session'], $this->container->getParameter( 'ezsettings.ezdemo_site.session' ) );
        $this->assertEquals( $expected['session_name'], $this->container->getParameter( 'ezsettings.ezdemo_site.session_name' ) );
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
                    )
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
                    'session_name' => 'foo'
                )
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
                    'session_name' => 'bar'
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
                    'session_name' => 'bar'
                )
            ),
            array(
                array(
                    'session_name' => 'some_other_session_name'
                ),
                array(
                    'session' => array(
                        'name' => 'some_other_session_name',
                    ),
                    'session_name' => 'some_other_session_name'
                )
            ),
        );
    }
}
