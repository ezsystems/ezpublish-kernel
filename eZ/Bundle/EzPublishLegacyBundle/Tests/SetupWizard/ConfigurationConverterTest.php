<?php
/**
 * File containing the ConfigurationConverterTest class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Tests\SetupWizard;

use eZ\Publish\Core\MVC\Legacy\Tests\LegacyBasedTestCase,
    eZ\Bundle\EzPublishLegacyBundle\SetupWizard\ConfigurationConverter;

class ConfigurationConverterTest extends LegacyBasedTestCase
{
    public function testFromLegacy()
    {
        $configurationConverter = new ConfigurationConverter( $this->getLegacyConfigResolverMock() );

        self::assertEquals( $this->getExpectedResultForTestFromLegacy(), $configurationConverter->fromLegacy( 'ezdemo_site', 'ezdemo_site_admin' ) );
    }

    /**
     * @param array $methodsToMock
     * @return \PHPUnit_Framework_MockObject_MockObject|eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver
     */
    protected function getLegacyConfigResolverMock( array $methodsToMock = array() )
    {
        $mock = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishLegacyBundle\\DependencyInjection\\Configuration\\LegacyConfigResolver' )
            ->disableOriginalConstructor()
            ->setMethods( $methodsToMock )
            ->getMock();

        $mock
            ->expects( $this->at( 0 ) )
            ->method( 'getParameter' )
            ->with( 'SiteSettings.DefaultAccess' )
            ->will( $this->returnValue( 'eng' ) );

        $mock
            ->expects( $this->at( 1 ) )
            ->method( 'getParameter' )
            ->with( 'SiteSettings.SiteList' )
            ->will( $this->returnValue( array( 'eng', 'ezdemo_site', 'ezdemo_site_admin' ) ) );

        $mock
            ->expects( $this->at( 2 ) )
            ->method( 'getGroup' )
            ->with( 'SiteAccessSettings' )
            ->will( $this->returnValue( array(
                'MatchOrder' => 'uri',
                'URIMatchType' => 'element',
                'URIMatchElement' => 1
            ) ) );

        $mock
            ->expects( $this->at( 3 ) )
            ->method( 'getGroup' )
            ->with( 'DatabaseSettings', 'site', 'eng' )
            ->will( $this->returnValue( array(
                'DatabaseImplementation' => 'ezmysqli',
                'Server' => 'localhost',
                'User' => 'root',
                'Password' => '',
                'Database' => 'ezdemo',
            ) ) );

        return $mock;
    }

    protected function getExpectedResultForTestFromLegacy()
    {
        return array (
            'ezpublish' =>
            array (
                'siteaccess' =>
                array (
                    'default_siteaccess' => 'eng',
                    'list' =>
                    array (
                        0 => 'eng',
                        1 => 'ezdemo_site',
                        2 => 'ezdemo_site_admin',
                    ),
                    'groups' =>
                    array (
                        'ezdemo_site_group' =>
                        array (
                            0 => 'eng',
                            1 => 'ezdemo_site',
                            2 => 'ezdemo_site_admin',
                        ),
                    ),
                    'match' =>
                    array (
                        'URIElement' => 1,
                    ),
               ),
                'system' =>
                array (
                    'ezdemo_site_group' =>
                    array (
                        'database' =>
                        array (
                            'type' => 'mysql',
                            'user' => 'root',
                            'password' => '',
                            'server' => 'localhost',
                            'database_name' => 'ezdemo',
                        ),
                    ),
                    'ezdemo_site_admin' =>
                    array(
                        'url_alias_router' => false,
                    )
                ),
            ),
        );
    }
}
