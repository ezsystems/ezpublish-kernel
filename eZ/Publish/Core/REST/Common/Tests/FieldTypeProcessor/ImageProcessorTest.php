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
use eZ\Publish\Core\REST\Common\UrlHandler;

class ImageProcessorTest extends BinaryInputProcessorTest
{
    /** @var UrlHandler */
    protected $urlHandler;

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

        $outputHash = $processor->postProcessValueHash( $inputHash );

        $expectedVariations = array();
        foreach ( $this->getVariations() as $variation )
        {
            $expectedVariations[$variation] = array( 'href' => null );
        }
        $this->assertEquals(
            array(
                'path' => '/var/some_site/223-1-eng-US/Cool-File.jpg',
                'imageId' => '223-12345',
                'variants' => $expectedVariations,
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
            $this->getUrlHandlerMock(),
            $this->getVariations()
        );
    }

    protected function getUrlHandlerMock()
    {
        if ( !isset( $this->urlHandler ) )
        {
            $this->urlHandler = $this->getMock( 'eZ\\Publish\\Core\\REST\\Common\\UrlHandler' );
        }
        return $this->urlHandler;
    }

    protected function getVariations()
    {
        return array( 'small', 'medium', 'large' );
    }
}
