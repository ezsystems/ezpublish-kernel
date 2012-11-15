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

    protected function getConfigurationConverterMock( array $constructorParams )
    {
        return $this->getMock(
            'eZ\\Bundle\\EzPublishLegacyBundle\\SetupWizard\\ConfigurationConverter',
            array(
                'getParameter',
                'getGroup'
            ),
            $constructorParams
        );
    }


    /**
     * @param $package
     * @param $adminSiteaccess
     * @param $mockParameters
     * @param $expectedResult
     * @param $exception exception type, if expected
     *
     * @throws \Exception
     * @return void
     * @internal param $mockParameter
     *
     * @param $exception exception type, if expected
     *
     * @dataProvider providerForTestFromLegacy
     */
    public function testFromLegacy( $package, $adminSiteaccess, $mockParameters, $expectedResult, $exception = null )
    {
        $configurationConverter = $this->getConfigurationConverterMock(
            array(
                $this->getLegacyConfigResolverMock(),
                $this->getLegacyKernelMock(),
                array( $package )
            )
        );
        foreach ( $mockParameters as $method => $callbackMap )
        {
            $configurationConverter->expects( $this->any() )
                ->method( $method )
                ->will(
                    $this->returnCallback(
                        $this->convertMapToCallback( $callbackMap )
                    )
                )
            ;
        }

        try
        {
            $result = $configurationConverter->fromLegacy( $package, $adminSiteaccess );
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
        self::assertEquals(
            $expectedResult,
            $result
        );

        self::assertSame(
            $expectedResult,
            $result
        );
    }

    /**
     * Converts a map of arguments + return value to a callback in order to allow exceptions
     *
     * @param array $callbackMap array of callback parameter arrays [0..n-1 => arguments, n => return value]
     *
     * @return callable
     */
    protected function convertMapToCallback( $callbackMap )
    {
        return function() use ( $callbackMap )
        {
            foreach( $callbackMap as $map )
            {
                $mapArguments = array_slice( $map, 0, -1 );
                // pad the call arguments array with nulls to match the map
                $callArguments = array_pad( func_get_args(), count( $mapArguments ), null );

                if ( count( array_diff( $callArguments, $mapArguments ) ) == 0 )
                {
                    $return = $map[count( $map ) - 1];
                    if ( is_callable( $return ) )
                        return $return();
                    else
                        return $return;
                }

            }
            throw new \Exception( "No callback match found for " . var_export( func_get_args(), true ) );
        };
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
                        'ezdemo_group' =>
                        array (
                            0 => 'eng',
                            1 => 'ezdemo_site',
                            2 => 'ezdemo_site_admin',
                        ),
                    ),
                    'match' => array( 'URIElement' => 1 ),
                ),
                'system' => array(
                    'ezdemo_group' => array(
                        'database' => array(
                            'type' => 'mysql',
                            'user' => 'root',
                            'password' => null,
                            'server' => 'localhost',
                            'database_name' => 'ezdemo',
                        ),
                        'var_dir' => 'var/ezdemo_site',
                    ),
                    'eng' => array(
                        'image_variations' => array(
                            'large' => array( 'reference' => null, 'filters' => array(
                                array( 'name' => 'geometry/scaledownonly', 'params' => array( 360, 440 ) )
                            ) ),
                            'infoboximage' => array( 'reference' => null, 'filters' => array(
                                array( 'name' => 'geometry/scalewidth', 'params' => array( 75 ) ),
                                array( 'name' => 'flatten' )
                            ) ),
                        )
                    ),
                    'ezdemo_site_admin' => array( 'legacy_mode' => true )
                ),
                'imagemagick' => array(
                    'enabled' => true,
                    'path' => '/usr/bin/convert',
                ),
                'http_cache' => array( 'purge_type' => 'local' )
            ),
        );

        $exceptionType = 'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException';

//        $parameterNotFoundException = function()
//        {
//            throw new \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException( 'Test', 'test' );
//        };

        $commonMockParameters = array(
            'getParameter' => array(
                'SiteSettingsDefaultAccess' => array( 'SiteSettings', 'DefaultAccess', null, null, 'eng' ),
                'SiteAccessSettingsAvailableSiteAccessList' => array( 'SiteAccessSettings', 'AvailableSiteAccessList', null, null, array( 'eng', 'ezdemo_site', 'ezdemo_site_admin' ) ),
                'FileSettingsVarDir' => array( 'FileSettings', 'VarDir', 'site.ini', 'eng', 'var/ezdemo_site' ),
                'FileSettingsStorageDir' => array( 'FileSettings', 'StorageDir', 'site.ini', 'eng', 'storage' ),
                'ImageMagickIsEnabled' => array( 'ImageMagick', 'IsEnabled', 'image.ini', 'eng', 'true' ),
                'ImageMagickExecutablePath' => array( 'ImageMagick', 'ExecutablePath', 'image.ini', 'eng', '/usr/bin' ),
                'ImageMagickExecutable' => array( 'ImageMagick', 'Executable', 'image.ini', 'eng', 'convert' ),
            ),
            'getGroup' => array(
                'SiteAccessSettings' => array( 'SiteAccessSettings', null, null,
                    array( 'MatchOrder' => 'uri', 'URIMatchType' => 'element', 'URIMatchElement' => 1 ) ),
                'DatabaseSettings' => array( 'DatabaseSettings', 'site.ini', 'eng',
                    array( 'DatabaseImplementation' => 'ezmysqli', 'Server' => 'localhost', 'User' => 'root', 'Password' => '', 'Database' => 'ezdemo' ) ),
                'AliasSettings' => array( 'AliasSettings', 'image.ini', 'eng',
                    array( 'AliasList' => array( 'large', 'infoboximage' ) ) ),
                'large' => array( 'large', 'image.ini', 'eng',
                    array( 'Reference' => '', 'Filters' => array( 'geometry/scaledownonly=360;440' ) ) ),
                'infoboximage' => array( 'infoboximage', 'image.ini', 'eng',
                    array( 'Reference' => '', 'Filters' => array( 'geometry/scalewidth=75', 'flatten' ) ) ),
            )
        );

        $baseData = array( 'ezdemo', 'ezdemo_site_admin', $commonMockParameters, $commonResult );

        $data = array();
        $data[] = $baseData;

        // empty site list => invalid argument exception
        $element = $baseData;
        $element[IDX_MOCK_PARAMETERS]['getParameter']['SiteSettingsSiteList'] = array( 'SiteSettings', 'SiteList', null, null, array() );
        $element[IDX_EXCEPTION] = $exceptionType;
        $data[] = $element;

        // imagemagick disabled
        $element = $baseData;
        $element[IDX_MOCK_PARAMETERS]['getParameter']['ImageMagickIsEnabled'] = array( 'ImageMagick', 'IsEnabled', 'eng', 'image.ini', 'false' );
        $element[IDX_EXPECTED_RESULT]['ezpublish']['imagemagick']['enabled'] = false;
        unset( $element[IDX_EXPECTED_RESULT]['ezpublish']['imagemagick']['path'] );
        $data[] = $element;

        // postgresql
        $element = $baseData;
        $element[IDX_MOCK_PARAMETERS]['getGroup']['DatabaseSettings'][3]['DatabaseImplementation'] = 'ezpostgresql';
        $element[IDX_EXPECTED_RESULT]['ezpublish']['system']['ezdemo_group']['database']['type'] = 'pgsql';
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

        // customized storage dir
        $element = $baseData;
        $element[IDX_MOCK_PARAMETERS]['getParameter']['FileSettingsStorageDir'] = array( 'FileSettings', 'StorageDir', 'site.ini', 'eng', 'customstorage' );
        $element[IDX_EXPECTED_RESULT]['ezpublish']['system']['ezdemo_group']['storage_dir'] = 'customstorage';
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
            ->setMethods( array_merge( $methodsToMock, array( 'getParameter', 'getGroup' ) ) )
            ->disableOriginalConstructor()
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
                        'legacy_mode' => true,
                    )
                ),
            ),
        );
    }

    /**
     * @return \Closure
     */
    protected function getLegacyKernelMock()
    {
        $legacyKernelMock = $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Legacy\\Kernel' )
            ->setMethods( array( 'runCallback' ) )
            ->disableOriginalConstructor()
            ->getMock();

        $legacyKernelMock
            ->expects( $this->any() )
            ->method( 'runCallback' )
            ->will( $this->returnValue( 'ezpKernelResult' ) );

        $closureMock = function() use ( $legacyKernelMock )
        {
            return $legacyKernelMock;
        };

        return $closureMock;
    }
}
