<?php
/**
 * File containing the ImageProcessorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Server\Tests\BaseTest;
use eZ\Publish\Core\REST\Server\FieldTypeProcessor\ImageProcessor;

class ImageProcessorTest extends BinaryInputProcessorTest
{
    public function testPostProcessHash()
    {
        $processor = $this->getProcessor();

        $inputHash = array(
            'path' => 'var/some_site/23-foo.jpg',
        );

        $outputHash = $processor->postProcessHash( $inputHash );

        $this->assertEquals(
            array(
                'path' => 'var/some_site/23-foo.jpg',
                'variants' => array(
                    array(
                        'variant' => 'original',
                        'contentType' => 'image/jpeg',
                        'url' => 'http://example.com/my_site/images/original/var/some_site/23-foo.jpg',
                    ),
                    array(
                        'variant' => 'thumbnail',
                        'contentType' => 'image/png',
                        'url' => 'http://example.com/my_site/images/thumbnail/var/some_site/23-foo.jpg',
                    ),
                ),
            ),
            $outputHash
        );
    }

    /**
     * Returns the processor under test
     *
     * @return eZ\Publish\Core\REST\Server\FieldTypeProcessor\BinaryInputProcessor
     */
    protected function getProcessor()
    {
        return new ImageProcessor(
            $this->getTempDir(),
            'http://example.com/my_site/images/{variant}/{path}',
            array(
                'original' => 'image/jpeg',
                'thumbnail' => 'image/png',
            )
        );
    }
}
