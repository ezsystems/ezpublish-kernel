<?php
/**
 * File containing the BinaryProcessorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Server\FieldTypeProcessor\BinaryProcessor;

class BinaryProcessorTest extends BinaryInputProcessorTest
{
    public function testPostProcessHash()
    {
        $processor = $this->getProcessor();

        $inputHash = array(
            'path' => 'var/some_site/12ace8436c64ceb907536640b58788f0.pdf',
        );

        $outputHash = $processor->postProcessHash( $inputHash );

        $this->assertEquals(
            array(
                'path' => 'var/some_site/12ace8436c64ceb907536640b58788f0.pdf',
                'url' => 'http://example.com/binaries/12ace8436c64ceb907536640b58788f0',
            ),
            $outputHash
        );
    }

    /**
     * Returns the processor under test
     *
     * @return \eZ\Publish\Core\REST\Server\FieldTypeProcessor\BinaryInputProcessor
     */
    protected function getProcessor()
    {
        return new BinaryProcessor(
            $this->getTempDir(),
            'http://example.com/binaries/{path}'
        );
    }
}
