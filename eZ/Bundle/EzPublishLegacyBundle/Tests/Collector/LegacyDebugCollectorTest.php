<?php
/**
 * File containing the LegacyDebugCollectorTest class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Tests\Collector;

use eZ\Publish\Core\MVC\Legacy\Tests\LegacyBasedTestCase;
use eZ\Bundle\EzPublishLegacyBundle\SetupWizard\ConfigurationConverter;
use eZ\Bundle\EzPublishLegacyBundle\Collector\LegacyDebugCollector;

class LegacyDebugCollectorTest extends \PHPUnit_Framework_TestCase
{

    protected $container;
    protected $collector;
    protected $name;

    public function setUp()
    {
        parent::setUp();

        $this->name = 'legacy_data';
        $this->container = $this->getContainerMock();

        $this->collector = new LegacyDebugCollector( $this->container );
    }

    public function testGetName()
    {
        $this->assertEquals( $this->name, $this->collector->getName() );
    }

    public function testCollect()
    {
        $this->markTestSkipped(
            'Skipped due to legacy closure'
        );
    }
    /**
     * @dataProvider someContentId
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Collector\LegacyDebugCollector::getLegacyDebug
     */
    public function testGetLegacyDebug( $contentId )
    {
        $serviceLegacy = $this->getLegacyKernelMock();

        $debugOutput = $serviceLegacy()->runCallback(

            function () use ( $contentId )
            {
                $ezDebugInstance = eZDebug::instance();
                //($as_html, $returnReport, $allowedDebugLevels, $useAccumulators, $useTiming, $useIncludedFiles)
                $report = $ezDebugInstance->printReportInternal( true, true, false, false, false, false );

                return $report;
            }
        );

        $this->assertInternalType( 'string', $debugOutput );
    }

    /*
     * Provide data for testing getLegacyDebug()
     */
    public function someContentId()
    {
        return array(
            array( 57 )
        );
    }

    public function testGetInfo()
    {
        $this->markTestSkipped(
            'Skipped due to legacy closure'
        );
    }
    /**
     * @dataProvider infoArray 
     */
    public function testGetInfoCount( $contentId, $type )
    {
        $this->markTestSkipped(
            'Skipped due to legacy closure'
        );
    }

    /*
     *  Data provide for testing getInfoCount()
     */
    public function infoArray()
    {
        return array(
            array( 57, "Debug" ),
            array( 57, "Warning" ),
            array( 57, "Error" )
        );
    }

    /**
     * @param array $methodsToMock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Request
     */
    private function getRequestMock( array $methodsToMock = array() )
    {
        return $this
            ->getMockBuilder( 'Symfony\\Component\\HttpFoundation\\Request' )
            ->setMethods( array_merge( array( 'getPathInfo' ), $methodsToMock ) )
            ->getMock();
    }

    /**
     * @param array $methodsToMock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    private function getContainerMock( array $methodsToMock = array() )
    {
        return $this
            ->getMockBuilder( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' )
            ->setMethods( $methodsToMock )
            ->getMock();
    }

    /**
     * @param array $methodsToMock
     * 
     * @return \Closure
     */
    protected function getLegacyKernelMock()
    {
        $legacyKernelMock = $this->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Legacy\\Kernel' )
                ->setMethods( array( 'runCallback' ) )
                ->disableOriginalConstructor()
                ->getMock();

        $legacyKernelMock->expects( $this->any() )
                ->method( 'runCallback' )
                ->will( $this->returnValue( 'Debug: test - Warning: test - Error: test' ) );

        $closureMock = function() use ( $legacyKernelMock )
        {
            return $legacyKernelMock;
        };

        return $closureMock;
    }

}
