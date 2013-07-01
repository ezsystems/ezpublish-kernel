<?php
/**
 * File containing the ImageProcessorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\ImageProcessor;
use eZ\Publish\Core\REST\Common\RequestParser;

class ImageProcessorTest extends BinaryInputProcessorTest
{
    /** @var RequestParser */
    protected $requestParser;

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\ImageProcessor::postProcessValueHash
     */
    public function testPostProcessValueHash()
    {
        $processor = $this->getProcessor();

        $inputHash = array(
            'path' => 'var/some_site/223-1-eng-US/Cool-File.jpg',
            'imageId' => '223-12345'
        );

        $routerMock = $this->getRouterMock();
        foreach ( $this->getVariations() as $iteration => $variationIdentifier )
        {
            $expectedVariations[$variationIdentifier]['href'] = "/content/binary/images/{$inputHash['imageId']}/variations/{$variationIdentifier}";
            $routerMock
                ->expects( $this->at( $iteration ) )
                ->method( 'generate' )
                ->with(
                    'ezpublish_rest_binaryContent_getImageVariation',
                    array( 'imageId' => $inputHash['imageId'], 'variationIdentifier' => $variationIdentifier )
                )
                ->will(
                    $this->returnValue( $expectedVariations[$variationIdentifier]['href'] )
                );
        }

        $outputHash = $processor->postProcessValueHash( $inputHash );

        $this->assertEquals(
            array(
                'path' => '/var/some_site/223-1-eng-US/Cool-File.jpg',
                'imageId' => '223-12345',
                'variations' => $expectedVariations,
            ),
            $outputHash
        );
    }

    /**
     * Returns the processor under test
     *
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\ImageProcessor
     */
    protected function getProcessor()
    {
        return new ImageProcessor(
            $this->getTempDir(),
            $this->getRouterMock(),
            $this->getVariations()
        );
    }

    /**
     * @returns \Symfony\Component\Routing\RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRouterMock()
    {
        if ( !isset( $this->requestParser ) )
        {
            $this->requestParser = $this->getMock( 'Symfony\\Component\\Routing\\RouterInterface' );
        }
        return $this->requestParser;
    }

    protected function getVariations()
    {
        return array( 'small', 'medium', 'large' );
    }
}
