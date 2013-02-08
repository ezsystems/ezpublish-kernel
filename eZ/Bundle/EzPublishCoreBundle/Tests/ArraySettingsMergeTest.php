<?php
/**
 * File containing the ArraySettingsMergeTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;

class ArraySettingsMergeTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->containerMock = $this->getMock(
            'Symfony\\Component\\DependencyInjection\\ContainerBuilder'
        );
        $this->parserMock = $this->getMock(
            'eZ\\Bundle\\EzPublishCoreBundle\\DependencyInjection\\Configuration\\AbstractParser',
            array(
                // leave setBaseKey alone!
                'registerInternalConfigArray',
                'getContainerParameter',
                'groupsArraySetting',
                'addSemanticConfig',
                'registerInternalConfig'
            )
        );
    }

    /**
     * Test that settings array a properly merged when defined in several
     * scopes.
     *
     * @dataProvider parameterProvider
     */
    public function testArrayMerge(
        $testId, $siteaccess, array $groups, array $defaultValue,
        array $globalValue, array $config, $options, array $expected,
        $customBaseKey = null
    )
    {
        $hasParameterMap = array(
            array(
                'ezsettings.' . ConfigResolver::SCOPE_DEFAULT . '.' . $testId,
                true
            ),
            array(
                'ezsettings.' . ConfigResolver::SCOPE_GLOBAL . '.' . $testId,
                true
            ),
            array(
                'ezpublish.siteaccess.groups_by_siteaccess',
                true
            )
        );

        $getParameterMap = array(
            array(
                'ezsettings.' . ConfigResolver::SCOPE_DEFAULT . '.' . $testId,
                $defaultValue
            ),
            array(
                'ezsettings.' . ConfigResolver::SCOPE_GLOBAL . '.' . $testId,
                $globalValue
            ),
            array(
                'ezpublish.siteaccess.groups_by_siteaccess',
                array( $siteaccess => $groups )
            )
        );

        $this->containerMock
            ->expects( $this->any() )
            ->method( 'hasParameter' )
            ->will( $this->returnValueMap( $hasParameterMap ) );

        $this->containerMock
            ->expects( $this->any() )
            ->method( 'getParameter' )
            ->will( $this->returnValueMap( $getParameterMap ) );

        $this->containerMock
            ->expects( $this->any() )
            ->method( 'setParameter' )
            ->with(
                $this->equalTo( "ezsettings.$siteaccess.$testId" ),
                $this->equalTo( $expected )
            );

        if ( $customBaseKey !== null )
        {
            $this->parserMock->setBaseKey( $customBaseKey );
        }
        $method = new \ReflectionMethod(
            $this->parserMock,
            'registerInternalConfigArray'
        );
        $method->setAccessible( true );
        $method->invoke( $this->parserMock, $testId, $config, $this->containerMock, $options );
    }

    public function parameterProvider()
    {
        $testId = 'wizards';
        $siteaccess = 'krondor';
        $group1 = 'midkemia';
        $group2 = 'triagia';
        $all = array( 'Kulgan', 'Macros the Black', 'Pug', 'Rogen', 'William' );
        $siteaccessConfig = array(
            'list' => array( $siteaccess ),
            'groups' => array(
                $group1 => array( $siteaccess ),
                $group2 => array( $siteaccess ),
            )
        );
        $testIdHash = 'location_view';
        $locationView1 = array(
            'full' => array(
                'Wizard' => array(
                    'template' => 'wizard.html.twig'
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer.html.twig'
                )
            )
        );

        $locationView2 = array(
            'full' => array(
                'Dwarve' => array(
                    'template' => 'dwarve.html.twig'
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer2.html.twig'
                )
            )
        );

        $locationView3 = array(
            'full' => array(
                'Moredhel' => array(
                    'template' => 'moredhel.html.twig'
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer3.html.twig'
                ),
            )
        );
        $locationView4 = array(
            'full' => array(
                'Moredhel' => array(
                    'template' => 'moredhel2.html.twig'
                ),
                'Warrior' => array(
                    'template' => 'warrior.html.twig'
                ),
            )
        );

        $locationView12 = array(
            'full' => array(
                'Wizard' => array(
                    'template' => 'wizard.html.twig'
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer2.html.twig'
                ),
                'Dwarve' => array(
                    'template' => 'dwarve.html.twig'
                ),
            )
        );

        $locationView123 = array(
            'full' => array(
                'Wizard' => array(
                    'template' => 'wizard.html.twig'
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer3.html.twig'
                ),
                'Dwarve' => array(
                    'template' => 'dwarve.html.twig'
                ),
                'Moredhel' => array(
                    'template' => 'moredhel.html.twig'
                )
            )
        );

        $locationView1234 = array(
            'full' => array(
                'Wizard' => array(
                    'template' => 'wizard.html.twig'
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer3.html.twig'
                ),
                'Dwarve' => array(
                    'template' => 'dwarve.html.twig'
                ),
                'Moredhel' => array(
                    'template' => 'moredhel2.html.twig'
                ),
                'Warrior' => array(
                    'template' => 'warrior.html.twig'
                ),
            )
        );

        $locationView21 = array(
            'full' => array(
                'Dwarve' => array(
                    'template' => 'dwarve.html.twig'
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer.html.twig'
                ),
                'Wizard' => array(
                    'template' => 'wizard.html.twig'
                ),
            )
        );

        $cases = array(
            //
            // MERGING TESTS ON NORMAL ARRAY
            //
            array(
                // everything in default scope
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                $all,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array()
                ),
                0,
                $all,
            ),
            array(
                // everything in global scope
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $all,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array()
                ),
                0,
                $all,
            ),
            array(
                // everything in a group
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group2 => array( $testId => $all )
                    )
                ),
                0,
                $all,
            ),
            array(
                // everything in a siteaccess
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $siteaccess => array( $testId => $all )
                    )
                ),
                0,
                $all,
            ),
            array(
                // default scope + one group
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array( 'Kulgan', 'Macros the Black' ),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testId => array( 'Pug', 'Rogen', 'William' ) ),
                    )
                ),
                0,
                $all,
            ),
            array(
                // one group + global scope
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array( 'Pug', 'Rogen', 'William' ),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testId => array( 'Kulgan', 'Macros the Black' ) ),
                    )
                ),
                0,
                $all,
            ),
            array(
                // default scope + two groups
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array( 'Kulgan', 'Macros the Black' ),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testId => array( 'Pug', 'Rogen' ) ),
                        $group2 => array( $testId => array( 'William' ) ),
                    )
                ),
                0,
                $all,
            ),
            array(
                // two groups + global scope
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array( 'Kulgan', 'Macros the Black' ),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testId => array( 'Pug', 'Rogen' ) ),
                        $group2 => array( $testId => array( 'William' ) ),
                    )
                ),
                0,
                array( 'Pug', 'Rogen', 'William', 'Kulgan', 'Macros the Black' ),
            ),
            array(
                // default scope + two groups + siteaccess
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array( 'Kulgan', 'Macros the Black' ),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testId => array( 'Pug' ) ),
                        $group2 => array( $testId => array( 'Rogen' ) ),
                        $siteaccess => array( $testId => array( 'William' ) ),
                    )
                ),
                0,
                $all,
            ),
            array(
                // global scope + two groups + siteaccess
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array( 'Kulgan', 'Macros the Black' ),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testId => array( 'Pug' ) ),
                        $group2 => array( $testId => array( 'Rogen' ) ),
                        $siteaccess => array( $testId => array( 'William' ) ),
                    )
                ),
                0,
                array( 'Pug', 'Rogen', 'William', 'Kulgan', 'Macros the Black' ),
            ),
            array(
                // default scope + two groups +  global
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array( 'Kulgan', 'Macros the Black' ),
                array( 'William' ),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testId => array( 'Pug' ) ),
                        $group2 => array( $testId => array( 'Rogen' ) ),
                    )
                ),
                0,
                $all,
            ),
            array(
                // default scope + two groups + siteaccess + global
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array( 'Kulgan' ),
                array( 'William' ),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testId => array( 'Macros the Black' ) ),
                        $group2 => array( $testId => array( 'Pug' ) ),
                        $siteaccess => array( $testId => array( 'Rogen' ) ),
                    )
                ),
                0,
                $all,
            ),
            //
            // UNIQUE OPTION TESTS (only suitable for normal array)
            //
            array(
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array( 'Kulgan', 'Kulgan' ),
                array( 'William' ),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testId => array( 'Macros the Black' ) ),
                        $group2 => array( $testId => array( 'Pug' ) ),
                        $siteaccess => array( $testId => array( 'Rogen', 'Pug' ) ),
                    )
                ),
                AbstractParser::UNIQUE,
                array( 'Kulgan', 'Macros the Black', 'Pug', 'Rogen', 'William' )
            ),
            array(
                $testId,
                $siteaccess,
                array( $group1, $group2 ),
                array( 'Kulgan', 'Kulgan' ),
                array( 'William', 'Kulgan', 'Pug' ),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testId => array( 'Macros the Black' ) ),
                        $group2 => array( $testId => array( 'Pug', 'William', 'Kulgan' ) ),
                        $siteaccess => array( $testId => array( 'Rogen', 'Pug', 'Rogen', 'Macros the Black' ) ),
                    )
                ),
                AbstractParser::UNIQUE,
                array( 'Kulgan', 'Macros the Black', 'Pug', 'William', 'Rogen' )
            ),
            //
            // MERGING HASH TESTS with MERGE_FROM_SECOND_LEVEL
            //
            array(
                // everything in default scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array()
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ),
            array(
                // everything in global scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $locationView1,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array()
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ),
            array(
                // everything in a group
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView1 )
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ),
            array(
                // everything in a siteaccess
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $siteaccess => array( $testIdHash => $locationView1 )
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ),

            array(
                // default scope + one group
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView12,
            ),
            array(
                // one group + global scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $locationView1,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView21,
            ),
            array(
                // default scope + two groups
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                        $group2 => array( $testIdHash => $locationView3 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ),
            array(
                // default scope + two groups + siteaccess
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                        $group2 => array( $testIdHash => $locationView3 ),
                        $siteaccess => array( $testIdHash => $locationView4 )
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView1234,
            ),
            array(
                // two groups
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView1 ),
                        $group2 => array( $testIdHash => $locationView2 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView12,
            ),
            array(
                // two groups + global scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView1 ),
                        $group2 => array( $testIdHash => $locationView2 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView123
            ),
            array(
                // two groups + siteaccess + global scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $locationView4,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView1 ),
                        $group2 => array( $testIdHash => $locationView2 ),
                        $siteaccess => array( $testIdHash => $locationView3 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView1234
            ),

            array(
                // two groups + siteaccess
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $siteaccess => array( $testIdHash => $locationView3 ),
                        $group1 => array( $testIdHash => $locationView1 ),
                        $group2 => array( $testIdHash => $locationView2 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView123
            ),
            array(
                // default scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                        $siteaccess => array( $testIdHash => $locationView3 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ),
            array(
                // default scope + group + siteaccess + global scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                $locationView4,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                        $siteaccess => array( $testIdHash => $locationView3 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView1234,
            ),
            array(
                // global scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group2 => array( $testIdHash => $locationView1 ),
                        $siteaccess => array( $testIdHash => $locationView2 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView123
            ),
            array(
                // default scope + group +  global
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group2 => array( $testIdHash => $locationView2 ),
                    )
                ),
                AbstractParser::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ),
            //
            // MERGING HASH TESTS without MERGE_FROM_SECOND_LEVEL, the result
            // is always the "last" defined one
            //
            array(
                // everything in default scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array()
                ),
                0,
                $locationView1,
            ),
            array(
                // everything in global scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $locationView1,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array()
                ),
                0,
                $locationView1,
            ),
            array(
                // everything in a group
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView1 )
                    )
                ),
                0,
                $locationView1,
            ),
            array(
                // everything in a siteaccess
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $siteaccess => array( $testIdHash => $locationView1 )
                    )
                ),
                0,
                $locationView1,
            ),
            array(
                // default scope + one group
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                    )
                ),
                0,
                $locationView2,
            ),
            array(
                // one group + global scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $locationView1,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                    )
                ),
                0,
                $locationView1,
            ),
            array(
                // default scope + two groups
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                        $group2 => array( $testIdHash => $locationView3 ),
                    )
                ),
                0,
                $locationView3,
            ),
            array(
                // default scope + two groups + siteaccess
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                        $group2 => array( $testIdHash => $locationView3 ),
                        $siteaccess => array( $testIdHash => $locationView4 ),
                    )
                ),
                0,
                $locationView4,
            ),
            array(
                // default scope + two groups + global scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                $locationView4,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                        $group2 => array( $testIdHash => $locationView3 ),
                    )
                ),
                0,
                $locationView4,
            ),
            array(
                // two groups
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView1 ),
                        $group2 => array( $testIdHash => $locationView2 ),
                    )
                ),
                0,
                $locationView2,
            ),
            array(
                // two groups + global scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView1 ),
                        $group2 => array( $testIdHash => $locationView2 ),
                    )
                ),
                0,
                $locationView3
            ),
            array(
                // two groups + siteaccess
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $siteaccess => array( $testIdHash => $locationView3 ),
                        $group1 => array( $testIdHash => $locationView1 ),
                        $group2 => array( $testIdHash => $locationView2 ),
                    )
                ),
                0,
                $locationView3
            ),
            array(
                // two groups + siteaccess + global scope
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $locationView4,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $siteaccess => array( $testIdHash => $locationView3 ),
                        $group1 => array( $testIdHash => $locationView1 ),
                        $group2 => array( $testIdHash => $locationView2 ),
                    )
                ),
                0,
                $locationView4
            ),
            array(
                // default scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group1 => array( $testIdHash => $locationView2 ),
                        $siteaccess => array( $testIdHash => $locationView3 ),
                    )
                ),
                0,
                $locationView3,
            ),
            array(
                // global scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                array(),
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group2 => array( $testIdHash => $locationView1 ),
                        $siteaccess => array( $testIdHash => $locationView2 ),
                    )
                ),
                0,
                $locationView3
            ),
            array(
                // default scope + group +  global
                $testIdHash,
                $siteaccess,
                array( $group1, $group2 ),
                $locationView1,
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    'system' => array(
                        $group2 => array( $testIdHash => $locationView2 ),
                    )
                ),
                0,
                $locationView3,
            )
        );

        foreach ( $cases as $k => $newcase )
        {
            // run the same tests with another baseKey than the default one
            if ( isset( $newcase[5]['system'] ) )
            {
                $newcase[5]['customBaseKey'] = $newcase[5]['system'];
                unset( $newcase[5]['system'] );
                $newcase[] = 'customBaseKey';
                $cases[] = $newcase;
            }
        }
        return $cases;
    }

}
