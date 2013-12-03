<?php
/**
 * File containing the CommonTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions()
    {
        return array( new EzPublishCoreExtension( array( new Common() ) ) );
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse( __DIR__ . '/../../Fixtures/ezpublish_minimal.yml' );
    }

    public function testLanguagesSingleSiteaccess()
    {
        $langDemoSite = array( 'eng-GB' );
        $langFre = array( 'fre-FR', 'eng-GB' );
        $config = array(
            'system' => array(
                'ezdemo_site' => array( 'languages' => $langDemoSite ),
                'fre' => array( 'languages' => $langFre ),
            )
        );
        $this->load( $config );

        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.languages' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.fre.languages' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.global.languages' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site_admin.languages' ) );
        $this->assertSame( $langDemoSite, $this->container->getParameter( 'ezsettings.ezdemo_site.languages' ) );
        $this->assertSame( $langFre, $this->container->getParameter( 'ezsettings.fre.languages' ) );
        // languages for ezdemo_site_admin will take default value (empty array)
        $this->assertEmpty( $this->container->getParameter( 'ezsettings.ezdemo_site_admin.languages' ) );
    }

    public function testLanguagesSiteaccessGroup()
    {
        $langDemoSite = array( 'eng-US', 'eng-GB' );
        $config = array(
            'system' => array(
                'ezdemo_frontend_group' => array( 'languages' => $langDemoSite ),
            )
        );
        $this->load( $config );

        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.languages' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.fre.languages' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.global.languages' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site_admin.languages' ) );
        $this->assertSame( $langDemoSite, $this->container->getParameter( 'ezsettings.ezdemo_site.languages' ) );
        $this->assertSame( $langDemoSite, $this->container->getParameter( 'ezsettings.fre.languages' ) );
        // languages for ezdemo_site_admin will take default value (empty array)
        $this->assertEmpty( $this->container->getParameter( 'ezsettings.ezdemo_site_admin.languages' ) );
    }

    /**
     * @dataProvider databaseParamsProvider
     */
    public function testDatabaseSingleSiteaccess( array $inputParams, $expected )
    {
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => $inputParams
                )
            )
        );

        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.database' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.database.params' ) );
        $this->assertEquals( $expected['database'], $this->container->getParameter( 'ezsettings.ezdemo_site.database' ) );
        $this->assertEquals( $expected['database.params'], $this->container->getParameter( 'ezsettings.ezdemo_site.database.params' ) );
    }

    /**
     * @dataProvider databaseParamsProvider
     */
    public function testDatabaseSiteaccessGroup( array $inputParams, $expected )
    {
        $this->load(
            array(
                'system' => array(
                    'ezdemo_group' => $inputParams
                )
            )
        );

        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.database' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.fre.database' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site_admin.database' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.database.params' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.fre.database.params' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site_admin.database.params' ) );
        $this->assertEquals( $expected['database'], $this->container->getParameter( 'ezsettings.ezdemo_site.database' ) );
        $this->assertEquals( $expected['database'], $this->container->getParameter( 'ezsettings.fre.database' ) );
        $this->assertEquals( $expected['database'], $this->container->getParameter( 'ezsettings.ezdemo_site_admin.database' ) );
        $this->assertEquals( $expected['database.params'], $this->container->getParameter( 'ezsettings.ezdemo_site.database.params' ) );
        $this->assertEquals( $expected['database.params'], $this->container->getParameter( 'ezsettings.fre.database.params' ) );
        $this->assertEquals( $expected['database.params'], $this->container->getParameter( 'ezsettings.ezdemo_site_admin.database.params' ) );
    }

    public function databaseParamsProvider()
    {
        return array(
            array(
                array(
                    'database' => array(
                        'type' => 'sqlite',
                        'server' => 'localhost',
                        'user' => 'root',
                        'password' => 'root',
                        'database_name' => 'ezdemo',
                    )
                ),
                array(
                    'database' => array(
                        'type' => 'sqlite',
                        'server' => 'localhost',
                        'user' => 'root',
                        'password' => 'root',
                        'database_name' => 'ezdemo',
                        'charset' => 'utf8',
                        'options' => array()
                    ),
                    'database.params' => array(
                        'host' => 'localhost',
                        'database' => 'ezdemo',
                        'type' => 'sqlite',
                        'user' => 'root',
                        'password' => 'root',
                        'charset' => 'utf8',
                        'driver-opts' => array()
                    )
                )
            ),
            array(
                array(
                    'database' => array(
                        'type' => 'mysql',
                        'server' => 'some.server',
                        'user' => 'foo_bar_baz',
                        'password' => '123abc456@woot%$&#',
                        'database_name' => 'my_database',
                        'options' => array( 'foo' => 'bar', 'some_param' => '123bzz;!' )
                    )
                ),
                array(
                    'database' => array(
                        'type' => 'mysql',
                        'server' => 'some.server',
                        'user' => 'foo_bar_baz',
                        'password' => '123abc456@woot%$&#',
                        'database_name' => 'my_database',
                        'options' => array( 'foo' => 'bar', 'some_param' => '123bzz;!' ),
                        'charset' => 'utf8',
                    ),
                    'database.params' => array(
                        'host' => 'some.server',
                        'database' => 'my_database',
                        'type' => 'mysql',
                        'user' => 'foo_bar_baz',
                        'password' => '123abc456@woot%$&#',
                        'charset' => 'utf8',
                        'driver-opts' => array( 'foo' => 'bar', 'some_param' => '123bzz;!' )
                    )
                )
            ),
            array(
                array(
                    'database' => array(
                        'type' => 'pgsql',
                        'server' => 'localhost',
                        'user' => 'root',
                        'password' => '$!root123',
                        'database_name' => 'ezdemo',
                        'charset' => 'utf16'
                    )
                ),
                array(
                    'database' => array(
                        'type' => 'pgsql',
                        'server' => 'localhost',
                        'user' => 'root',
                        'password' => '$!root123',
                        'database_name' => 'ezdemo',
                        'charset' => 'utf16',
                        'options' => array()
                    ),
                    'database.params' => array(
                        'host' => 'localhost',
                        'database' => 'ezdemo',
                        'type' => 'pgsql',
                        'user' => 'root',
                        'password' => '$!root123',
                        'charset' => 'utf16',
                        'driver-opts' => array()
                    )
                )
            ),
            array(
                array(
                    'database' => array(
                        'dsn' => 'mysql://root:root@localhost/ezdemo',
                    )
                ),
                array(
                    'database' => array(
                        'charset' => 'utf8',
                        'dsn' => 'mysql://root:root@localhost/ezdemo',
                        'options' => array()
                    ),
                    'database.params' => 'mysql://root:root@localhost/ezdemo'
                )
            ),
            array(
                array(
                    'database' => array(
                        'type' => 'sqlite',
                        'server' => 'localhost',
                        'user' => 'root',
                        'password' => 'root',
                        'database_name' => 'ezdemo',
                        'dsn' => 'mysql://root:root@localhost/ezdemo',
                    )
                ),
                array(
                    'database' => array(
                        'type' => 'sqlite',
                        'server' => 'localhost',
                        'user' => 'root',
                        'password' => 'root',
                        'database_name' => 'ezdemo',
                        'dsn' => 'mysql://root:root@localhost/ezdemo',
                        'charset' => 'utf8',
                        'options' => array()
                    ),
                    'database.params' => 'mysql://root:root@localhost/ezdemo'
                )
            ),
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
    }

    public function testMiscSettings()
    {
        $cachePoolName = 'cache_foo';
        $varDir = 'var/foo/bar';
        $storageDir = 'alternative_storage_folder';
        $binaryDir = 'alternative_binary_folder';
        $sessionName = 'alternative_session_name';
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
        $this->assertSame( $cachePurgeServers, $this->container->getParameter( 'ezsettings.ezdemo_site.http_cache.purge_servers' ) );
        $this->assertSame( $anonymousUserId, $this->container->getParameter( 'ezsettings.ezdemo_site.anonymous_user_id' ) );
    }
}
