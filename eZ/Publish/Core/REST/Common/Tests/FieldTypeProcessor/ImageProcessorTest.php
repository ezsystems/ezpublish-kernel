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

class ImageProcessorTest extends BinaryInputProcessorTest
{
    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\ImageProcessor::postProcessValueHash
     */
    public function testPostProcessValueHash()
    {
        $processor = $this->getProcessor();

        $inputHash = array(
            'path' => 'var/some_site/223-1-eng-US/Cool-File.jpg',
        );

        $outputHash = $processor->postProcessValueHash( $inputHash );

        $this->assertEquals(
            array(
                'path' => 'var/some_site/223-1-eng-US/Cool-File.jpg',
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
            'http://example.com/images/{fieldId}-{versionNo}/{variant}',
            array(
                'original' => 'image/jpeg',
                'thumbnail' => 'image/png',
            )
        );
    }
}
