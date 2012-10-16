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

    /**
     * @param $mockParameter
     * @param $expectedResult
     * @param $exception exception type, if expected
     * @dataProvider providerForTestFromLegacy
     */
    public function testFromLegacy( $package, $adminSiteaccess, $mockParameters, $expectedResult, $exception = null )
    {
        $legacyResolver = $this->getLegacyConfigResolverMock();
        foreach( $mockParameters as $method => $map )
        {
            $legacyResolver->expects( $this->any() )->method( $method )->will( $this->returnValueMap( $map ) );
        }

        $configurationConverter = new ConfigurationConverter( $legacyResolver );

        try
        {
            $result = $configurationConverter->fromLegacy( 'ezdemo_site', 'ezdemo_site_admin' );
        }
        catch( \Exception $e )
        {
            if ( $exception !== null && $e instanceof $exception )
            {
                return;
            }
            else
            {
                throw $e;
            }
        }
        self::assertSame(
            $expectedResult,
            $result
        );
    }

    public function providerForTestFromLegacy()
    {
        define( 'IDX_PACKAGE', 0 );
        define( 'IDX_ADMIN_SITEACCESS', 1 );
        define( 'IDX_MOCK_PARAMETERS', 2 );
        define( 'IDX_EXPECTED_RESULT', 3 );
        define( 'IDX_EXCEPTION', 4 );

        $commonResult = array (
            'ezpublish' => array (
                'siteaccess' => array(
                    'default_siteaccess' => 'eng',
                    'list' => array(
                        0 => 'eng',
                        1 => 'ezdemo_site',
                        2 => 'ezdemo_site_admin',
                    ),
                    'groups' => array(
                        'ezdemo_site_group' =>
                        array (
                            0 => 'eng',
                            1 => 'ezdemo_site',
                            2 => 'ezdemo_site_admin',
                        ),
                    ),
                    'match' => array( 'URIElement' => 1 ),
                ),
                'system' => array(
                    'ezdemo_site_group' => array(
                        'database' => array(
                            'type' => 'mysql',
                            'user' => 'root',
                            'password' => null,
                            'server' => 'localhost',
                            'database_name' => 'ezdemo',
                        ),
                    ),
                    'ezdemo_site_admin' => array( 'url_alias_router' => false )
                ),
            ),
        );

        $exceptionType = 'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException';

        $commonMockParameters = array(
            'getParameter' => array(
                'SiteSettings.DefaultAccess' => array( 'SiteSettings.DefaultAccess', null, null, 'eng' ),
                'SiteSettings.SiteList' => array( 'SiteSettings.SiteList', null, null, array( 'eng', 'ezdemo_site', 'ezdemo_site_admin' ) ),
            ),
            'getGroup' => array(
                'SiteAccessSettings' => array( 'SiteAccessSettings', null, null, array( 'MatchOrder' => 'uri', 'URIMatchType' => 'element', 'URIMatchElement' => 1 ) ),
                'DatabaseSettings' => array( 'DatabaseSettings', 'site', 'eng', array( 'DatabaseImplementation' => 'ezmysqli', 'Server' => 'localhost', 'User' => 'root', 'Password' => '', 'Database' => 'ezdemo' ) ),
            )
        );

        $baseData = array( 'ezdemo', 'ezdemo_site_admin', $commonMockParameters, $commonResult );

        $data = array();
        $data[] = $baseData;

        // empty site list => invalid argument exception
        $element = $baseData;
        $element[IDX_MOCK_PARAMETERS]['getParameter']['SiteSettings.SiteList'] = array( 'SiteSettings.SiteList', null, null, array() );
        $element[IDX_EXCEPTION] = $exceptionType;
        $data[] = $element;

        // host match, with map
        $element = $baseData;
        $element[IDX_MOCK_PARAMETERS]['getGroup']['SiteAccessSettings'] = array( 'SiteAccessSettings', null, null, array(
            'MatchOrder' => 'host',
            'HostMatchType' => 'map',
            'HostMatchMapItems' => array( 'site.com;eng', 'admin.site.com;ezdemo_site_admin' )
        ) );
        $element[IDX_EXPECTED_RESULT]['ezpublish']['siteaccess']['match'] = array(
            "Map\\Host" => array( 'site.com' => 'eng', 'admin.site.com' => 'ezdemo_site_admin' )
        );
        $data[] = $element;

        // host match, with map
        $element = $baseData;
        $element[IDX_MOCK_PARAMETERS]['getGroup']['SiteAccessSettings'] = array( 'SiteAccessSettings', null, null, array(
            'MatchOrder' => 'host',
            'HostMatchType' => 'map',
            'HostMatchMapItems' => array( 'site.com;eng', 'admin.site.com;ezdemo_site_admin' )
        ) );
        $element[IDX_EXPECTED_RESULT]['ezpublish']['siteaccess']['match'] = array(
            "Map\\Host" => array( 'site.com' => 'eng', 'admin.site.com' => 'ezdemo_site_admin' )
        );
        $data[] = $element;

        // host match, with map
        $element = $baseData;
        $element[IDX_ADMIN_SITEACCESS] = 'winter';
        $element[IDX_EXCEPTION] = $exceptionType;
        $data[] = $element;

        return $data;
    }

    /**
     * @param array $methodsToMock
     * @return \PHPUnit_Framework_MockObject_MockObject|eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver
     */
    protected function getLegacyConfigResolverMock( array $methodsToMock = array() )
    {

        $mock = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishLegacyBundle\\DependencyInjection\\Configuration\\LegacyConfigResolver' )
            ->setMethods( array( 'getParameter', 'getGroup' ) )
            ->disableOriginalConstructor()
            ->setMethods( $methodsToMock )
            ->getMock();

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
                            'password' => null,
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