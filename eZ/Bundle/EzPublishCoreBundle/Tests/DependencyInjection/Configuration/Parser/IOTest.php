<?php
/**
 * File containing the IOTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\IO;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Symfony\Component\Yaml\Yaml;

class IOTest extends AbstractParserTestCase
{
    private $minimalConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $suggestionCollector;

    protected function getContainerExtensions()
    {
        return array( new EzPublishCoreExtension( array( new IO() ) ) );
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse( file_get_contents( __DIR__ . '/../../Fixtures/ezpublish_minimal.yml' ) );
    }

    public function testDefaultHandlersConfig()
    {
        $config = array(
            'system' => array(
                'ezdemo_site' => array(
                    'io' => array(
                        'binarydata_handler' => 'cluster',
                        'metadata_handler' => 'cluster',
                    )
                )
            )
        );

        $this->load( $config );

        $this->assertConfigResolverParameterValue( 'io.metadata_handler', 'cluster', 'ezdemo_site' );
        $this->assertConfigResolverParameterValue( 'io.binarydata_handler', 'cluster', 'ezdemo_site' );
    }
}
