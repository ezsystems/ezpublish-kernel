<?php
/**
 * File containing the BinaryProcessorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\BinaryProcessor;

class BinaryProcessorTest extends BinaryInputProcessorTest
{
    const TEMPLATE_URL = 'http://ez.no/subdir/var/rest_test/storage/original/{path}';

    public function testPostProcessValueHash()
    {
        $path = 'application/815b3aa9.pdf';
        $processor = $this->getProcessor();

        $inputHash = array(
            'path' => $path,
        );

        $outputHash = $processor->postProcessValueHash( $inputHash );

        $this->assertEquals(
            array(
                'path' => $path,
                'url' => str_replace( '{path}', $path, self::TEMPLATE_URL )
            ),
            $outputHash
        );
    }

    /**
     * Returns the processor under test
     *
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\BinaryInputProcessor
     */
    protected function getProcessor()
    {
        return new BinaryProcessor(
            $this->getTempDir(),
            self::TEMPLATE_URL
        );
    }
}
